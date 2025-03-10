<?php

namespace Drupal\cacheexclude\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Processes all the paths in cacheexclude config before migrating to D9.
 *
 * @MigrateProcessPlugin(
 *   id = "cacheexclude_paths"
 * )
 */
class CacheexcludePaths extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $all_paths = '';
    foreach (explode("\r\n", $value) as $path) {
      // Adds a leading slash to all the paths.
      $all_paths = $all_paths . '/' . $path . "\n";
    }
    // Removes trailing "\n".
    return rtrim($all_paths);
  }

}
