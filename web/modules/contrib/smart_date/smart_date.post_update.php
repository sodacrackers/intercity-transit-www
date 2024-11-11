<?php

/**
 * @file
 * Post-update functions for Smart Date module.
 */

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Exception\FieldStorageDefinitionUpdateForbiddenException;
use Drupal\Core\Entity\Schema\DynamicallyFieldableEntityStorageSchemaInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\smart_date\Plugin\Field\FieldType\SmartDateItem;

/**
 * Clear caches to ensure schema changes are read.
 */
function smart_date_post_update_translatable_separator() {
  // Empty post-update hook to cause a cache rebuild.
}

/**
 * Migrate smartdate_default field formatter settings to smartdate_custom.
 */
function smart_date_post_update_translatable_config() {

  // Loop through all configured entity view displays, and compile information
  // about the smartdate_default field settings.
  $displays = EntityViewDisplay::loadMultiple();
  foreach ($displays as $display) {
    if ($display instanceof EntityViewDisplay) {
      $components = $display->getComponents();
      foreach ($components as $fieldName => $component) {
        if (isset($component['type'])
          && $component['type'] === 'smartdate_default'
          && isset($component['settings'])
        ) {
          // Keep the settings the same but change it to the custom display.
          $component['type'] = 'smartdate_custom';
          $display->setComponent($fieldName, $component);
          $display->save();
        }
      }
    }
  }
  // Now ensure defaults are imported.
  // If there are already smart date format entities then nothing is needed.
  $storage = \Drupal::entityTypeManager()->getStorage('smart_date_format');
  $existing = $storage->loadMultiple();
  if ($existing) {
    return;
  }

  // Obtain configuration from yaml files.
  $config_path = \Drupal::service('extension.list.module')->getPath('smart_date') . '/config/install/';
  $source      = new FileStorage($config_path);

  // Load the provided default entities.
  $storage->create($source->read('smart_date.smart_date_format.compact'))
    ->save();
  $storage->create($source->read('smart_date.smart_date_format.date_only'))
    ->save();
  $storage->create($source->read('smart_date.smart_date_format.default'))
    ->save();
  $storage->create($source->read('smart_date.smart_date_format.time_only'))
    ->save();
}

/**
 * Increase the storage size to resolve the 2038 problem.
 */
function smart_date_post_update_increase_column_storage(&$sandbox): void {
  if (!isset($sandbox['items'])) {
    $items = _smart_date_update_get_smart_date_fields();
    $sandbox['items'] = $items;
    $sandbox['current'] = 0;
    $sandbox['num_processed'] = 0;
    $sandbox['max'] = count($items);
  }

  [$entity_type_id, $field_name] = $sandbox['items'][$sandbox['current']];
  if ($entity_type_id && $field_name) {
    _smart_date_update_process_smart_date_field($entity_type_id, $field_name);
  }
  $sandbox['current']++;

  $sandbox['#finished'] = empty($sandbox['max']) ? 1 : ($sandbox['current'] / $sandbox['max']);
}

/**
 * Gets a list fields that use the SmartDateItem class.
 *
 * @return string[]
 *   An array with two elements, an entity type ID and a field name.
 */
function _smart_date_update_get_smart_date_fields(): array {
  $items = [];

  // Get all the field definitions.
  $field_definitions = \Drupal::service('plugin.manager.field.field_type')->getDefinitions();

  // Get all the field types that use the SmartDateItem class.
  $field_types = array_keys(array_filter($field_definitions, function ($definition) {
    return is_a($definition['class'], SmartDateItem::class, TRUE);
  }));

  /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager */
  $entity_field_manager = \Drupal::service('entity_field.manager');

  // Build a list of all the Smart Date fields.
  foreach ($field_types as $field_type) {
    $entity_field_map = $entity_field_manager->getFieldMapByFieldType($field_type);
    foreach ($entity_field_map as $entity_type_id => $fields) {
      $storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
      if ($storage instanceof SqlContentEntityStorage) {
        foreach (array_keys($fields) as $field_name) {
          $items[] = [$entity_type_id, $field_name];
        }
        $storage->resetCache();
      }
    }
  }

  return $items;
}

/**
 * Update a Smart Date field to remove Y2038 limitation.
 *
 * @param string $entity_type_id
 *   The entity type ID.
 * @param string $field_name
 *   The name of the field that needs to be updated.
 *
 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
 * @throws \Drupal\Core\Entity\Sql\SqlContentEntityStorageException
 */
function _smart_date_update_process_smart_date_field(string $entity_type_id, string $field_name): void {
  /** @var \Drupal\Core\Logger\LoggerChannel $logger */
  $logger = \Drupal::logger('update');

  $storage_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($entity_type_id);

  $entity_type_manager = \Drupal::entityTypeManager();
  $entity_type_manager->useCaches(FALSE);
  $entity_storage = $entity_type_manager->getStorage($entity_type_id);

  // Get the table mappings for this field.
  /** @var \Drupal\Core\Entity\Sql\DefaultTableMapping $table_mapping */
  $table_mapping = $entity_storage->getTableMapping($storage_definitions);

  // Field type column names map to real table column names.
  $columns = $table_mapping->getColumnNames($field_name);
  $column_names = [];
  if ($columns['value']) {
    $column_names['value'] = $columns['value'];
  }
  if ($columns['end_value']) {
    $column_names['end_value'] = $columns['end_value'];
  }

  // We are allowed to change 'value' and 'end_value' columns, so if those do
  // not exist due contrib or custom alters leave everything unchanged.
  if (!$column_names) {
    $logger->notice("Smart Date timestamps for entity '$entity_type_id' field '$field_name' not updated because database columns were not found.");
    return;
  }

  // Get the original storage definition for this field.
  $last_installed_schema_repository = \Drupal::service('entity.last_installed_schema.repository');
  $original_storage_definitions = $last_installed_schema_repository->getLastInstalledFieldStorageDefinitions($entity_type_id);
  $original_storage_definition = $original_storage_definitions[$field_name];

  // Get the current storage definition for this field.
  $storage_definition = $storage_definitions[$field_name];
  $storage = $entity_type_manager->getStorage($storage_definition->getTargetEntityTypeId());

  if (!($storage instanceof DynamicallyFieldableEntityStorageSchemaInterface
    && $storage->requiresFieldStorageSchemaChanges($storage_definition, $original_storage_definition))) {
    $logger->notice("Timestamp for entity '$entity_type_id' field '$field_name' not updated because field size is already 'big'.");
    return;
  }

  $schema = \Drupal::database()->schema();
  $field_schema = $original_storage_definitions[$field_name]->getSchema() ?? $storage_definition->getSchema();
  $specification = $field_schema['columns']['value'];
  $specification['size'] = 'big';
  foreach ($column_names as $column_name) {
    // Update the table specification for the timestamp field, setting the size
    // to 'big'.
    foreach ($table_mapping->getAllFieldTableNames($field_name) as $table) {
      $schema->changeField($table, $column_name, $column_name, $specification);
    }
  }

  // Update the tracked entity table schema, setting the size to 'big'.
  /** @var \Drupal\Core\Entity\EntityDefinitionUpdateManager $mgr */
  try {
    \Drupal::service('entity.definition_update_manager')->updateFieldStorageDefinition($storage_definition);
  }
  catch (FieldStorageDefinitionUpdateForbiddenException $e) {
  }

  $logger->notice("Successfully updated entity '$entity_type_id' field '$field_name' to remove year 2038 limitation.");
}
