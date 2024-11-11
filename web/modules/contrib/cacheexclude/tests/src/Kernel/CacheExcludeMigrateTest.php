<?php

namespace Drupal\Tests\cacheexclude\Kernel;

use Drupal\Tests\migrate_drupal\Kernel\d7\MigrateDrupal7TestBase;

/**
 * Tests cache exclude config migration.
 *
 * @group cacheexclude
 */
class CacheExcludeMigrateTest extends MigrateDrupal7TestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    // Text module is enabled because we need "text_with_summary" plugin.
    'text',
    'cacheexclude',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installConfig(static::$modules);
    $this->executeMigrations([
      'd7_node_type',
      'cacheexclude_settings',
    ]);
  }

  /**
   * Gets the path to test fixtures.
   *
   * @return string
   *   A filepath string.
   */
  protected function getFixtureFilePath() {
    return implode(DIRECTORY_SEPARATOR, [
      \Drupal::service('extension.list.module')->getPath('cacheexclude'),
      'tests',
      'fixtures',
      'drupal7.php',
    ]);
  }

  /**
   * Asserts that cache exclude configuration is migrated.
   */
  public function testCacheExcludeMigration(): void {
    $expected_config = [
      'cacheexclude_list' => '/node/3
/blog/*',
      'cacheexclude_node_types' => [
        'range' => 'range',
        'test_display' => 'test_display',
        'video' => 'video',
        'article' => '0',
        'page' => '0',
      ],
    ];
    $config = $this->config('cacheexclude.settings')->getRawData();
    $this->assertSame($expected_config, $config);
  }

}
