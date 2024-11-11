<?php

namespace Drupal\cacheexclude\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Looks up existing node types to migrate cacheexclude config only for those.
 *
 * @MigrateProcessPlugin(
 *   id = "cacheexclude_nodes"
 * )
 */
class CacheexcludeNodes extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The migrate lookup service.
   *
   * @var \Drupal\migrate\MigrateLookupInterface
   */
  protected $migrateLookup;

  /**
   * Constructs a CacheexcludeNodes object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\migrate\MigrateLookupInterface $migrate_lookup
   *   The migrate lookup service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrateLookupInterface $migrate_lookup) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migrateLookup = $migrate_lookup;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('migrate.lookup'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = $row->getSource();
    $node_types = [];
    foreach ($value['cacheexclude_node_types'] as $key => $data) {
      $lookup_result = $this->migrateLookup->lookup('d7_node_type', [$key]);
      if ($lookup_result === []) {
        continue;
      }
      $destination_node_type = $lookup_result[0]['type'];
      $node_types[$destination_node_type] = $data ? $destination_node_type : 0;
    }
    return $node_types;
  }

}
