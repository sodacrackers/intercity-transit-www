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
class CacheExcludeNodeTypeTest extends BrowserTestBase {

  use AssertPageCacheContextsAndTagsTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'cacheexclude', 'page_cache'];

  /**
   * Setup cacheexclude setting.
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable page cache.
    $config = $this->config('system.performance');
    $config->set('cache.page.max_age', 300);
    $config->save();

    // Create content types.
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    drupal_flush_all_caches();
  }

  /**
   * Tests that cacheexclude works for a node type as expected.
   */
  public function testCacheExcludeByContentType(): void {
    $article_node = $this->drupalCreateNode([
      'title' => 'Excluded article',
      'type' => 'article',
    ]);

    $page_node = $this->drupalCreateNode([
      'title' => 'Cached page',
      'type' => 'page',
    ]);

    $paths = [
      'article_path' => '/node/' . $article_node->id(),
      'page_path' => '/node/' . $page_node->id(),
    ];

    // Both nodes should cache before we configure the module.
    foreach ($paths as $path) {
      // First request should MISS.
      $this->drupalGet($path);
      $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'MISS');
      // Second request should HIT.
      $this->drupalGet($path);
      $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'HIT');
    }

    $settings = [
      'article' => 'article',
      'page' => '0',
    ];
    $config = $this->config('cacheexclude.settings');
    $config->set('cacheexclude_node_types', $settings);
    $config->save();
    drupal_flush_all_caches();

    // No cache for the article.
    $this->drupalGet($paths['article_path']);
    $this->assertSession()->responseHeaderDoesNotExist('X-Drupal-Cache');

    // Cache for the page, but first request should MISS.
    $this->drupalGet($paths['page_path']);
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'MISS');
    // Second request should HIT.
    $this->drupalGet($paths['page_path']);
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'HIT');
  }

}
