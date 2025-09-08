<?php

namespace Drupal\it_route_trip_tools;

use Drupal\Core\Database\Connection;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Service for managing persistent transit data cache.
 */
class TransitDataCache {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a TransitDataCache object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(Connection $database, TimeInterface $time, LoggerChannelFactoryInterface $logger_factory) {
    $this->database = $database;
    $this->time = $time;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Gets data from the persistent cache.
   *
   * @param string $cid
   *   The cache ID.
   *
   * @return mixed|false
   *   The cached data or FALSE if not found or expired.
   */
  public function get($cid) {
    try {
      $cache = $this->database->select('it_route_trip_tools_cache', 'c')
        ->fields('c', ['data', 'expire'])
        ->condition('cid', $cid)
        ->execute()
        ->fetchAssoc();

      if ($cache) {
        // Check if the cache has expired.
        if ($cache['expire'] > 0 && $cache['expire'] < $this->time->getRequestTime()) {
          // Cache has expired, delete it.
          $this->delete($cid);
          return FALSE;
        }

        // Return unserialized data.
        return unserialize($cache['data']);
      }
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('it_route_trip_tools')->error('Error getting cache: @message', ['@message' => $e->getMessage()]);
    }

    return FALSE;
  }

  /**
   * Sets data in the persistent cache.
   *
   * @param string $cid
   *   The cache ID.
   * @param mixed $data
   *   The data to cache.
   * @param int $expire
   *   The cache expiration time. Use 0 for permanent cache.
   * @param array $tags
   *   An array of cache tags.
   */
  public function set($cid, $data, $expire = 0, array $tags = []) {
    try {
      // Delete existing entry if it exists.
      $this->delete($cid);

      // Insert new cache entry.
      $this->database->insert('it_route_trip_tools_cache')
        ->fields([
          'cid' => $cid,
          'data' => serialize($data),
          'expire' => $expire,
          'created' => $this->time->getRequestTime(),
          'tags' => implode(' ', $tags),
        ])
        ->execute();
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('it_route_trip_tools')->error('Error setting cache: @message', ['@message' => $e->getMessage()]);
    }
  }

  /**
   * Deletes a cache entry.
   *
   * @param string $cid
   *   The cache ID to delete.
   */
  public function delete($cid) {
    try {
      $this->database->delete('it_route_trip_tools_cache')
        ->condition('cid', $cid)
        ->execute();
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('it_route_trip_tools')->error('Error deleting cache: @message', ['@message' => $e->getMessage()]);
    }
  }

  /**
   * Deletes all cache entries.
   */
  public function deleteAll() {
    try {
      $this->database->truncate('it_route_trip_tools_cache')->execute();
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('it_route_trip_tools')->error('Error deleting all cache: @message', ['@message' => $e->getMessage()]);
    }
  }

  /**
   * Deletes expired cache entries.
   */
  public function deleteExpired() {
    try {
      $this->database->delete('it_route_trip_tools_cache')
        ->condition('expire', 0, '>')
        ->condition('expire', $this->time->getRequestTime(), '<')
        ->execute();
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('it_route_trip_tools')->error('Error deleting expired cache: @message', ['@message' => $e->getMessage()]);
    }
  }

  /**
   * Invalidates cache entries by tags.
   *
   * @param array $tags
   *   The cache tags to invalidate.
   */
  public function invalidateTags(array $tags) {
    if (empty($tags)) {
      return;
    }

    try {
      $query = $this->database->select('it_route_trip_tools_cache', 'c')
        ->fields('c', ['cid']);

      $or = $query->orConditionGroup();
      foreach ($tags as $tag) {
        $or->condition('tags', '%' . $this->database->escapeLike($tag) . '%', 'LIKE');
      }
      $query->condition($or);

      $cids = $query->execute()->fetchCol();
      
      foreach ($cids as $cid) {
        $this->delete($cid);
      }
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('it_route_trip_tools')->error('Error invalidating tags: @message', ['@message' => $e->getMessage()]);
    }
  }
}
