<?php

namespace Drupal\cacheexclude\Plugin\migrate\source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\Variable;

/**
 * Cacheexclude migrate source plugin.
 *
 * @MigrateSource(
 *   id = "cacheexclude",
 *   source_module = "cacheexclude"
 * )
 */
class Cacheexclude extends Variable {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    $configuration['variables'] = [
      'cacheexclude_list',
      'cacheexclude_node_types',
    ];
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
  }

}
