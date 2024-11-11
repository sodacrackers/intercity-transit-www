<?php

namespace Drupal\Tests\cacheexclude\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\system\Functional\Cache\AssertPageCacheContextsAndTagsTrait;

/**
 * Enables the page cache and tests it with various page requests.
 *
 * @group cacheexclude
 */
class CacheExcludeHeaderTest extends BrowserTestBase {

  use AssertPageCacheContextsAndTagsTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['cacheexclude', 'page_cache', 'node'];

  /**
   * Setup cacheexclude setting.
   */
  protected function setUp(): void {
    parent::setUp();
    // Enable page cache.
    $config = $this->config('system.performance');
    $config->set('cache.page.max_age', 300);
    $config->save();
    drupal_flush_all_caches();
  }

  /**
   * Tests that cacheexclude works as expected.
   */
  public function testCacheExcludeByPath(): void {
    $paths = [
      'cached_path' => '/node',
      'excluded_path' => Url::fromRoute('<front>'),
    ];

    // Both paths should cache before we configure the module.
    foreach ($paths as $path) {
      // First request should MISS.
      $this->drupalGet($path);
      $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'MISS');
      // Second request should HIT.
      $this->drupalGet($path);
      $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'HIT');
    }

    // Update the config and re-test.
    $config = $this->config('cacheexclude.settings');
    $config->set('cacheexclude_list', '<front>');
    $config->save();
    drupal_flush_all_caches();

    // No cache for <front>.
    $this->drupalGet($paths['excluded_path']);
    $this->assertSession()->responseHeaderDoesNotExist('X-Drupal-Cache');

    // Cache for /node, but first request should MISS.
    $this->drupalGet($paths['cached_path']);
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'MISS');
    // Second request should HIT.
    $this->drupalGet($paths['cached_path']);
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'HIT');
  }

}
