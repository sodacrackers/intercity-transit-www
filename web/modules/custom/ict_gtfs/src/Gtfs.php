<?php

namespace Drupal\ict_gtfs;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\example\ExampleInterface;
use GuzzleHttp\ClientInterface;

/**
 * Gtfs service.
 */
class Gtfs {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The logger.channel.ict_gtfs service.
   *
   * @var \Drupal\example\ExampleInterface
   */
  protected $loggerChannelIctGtfs;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constructs a Gtfs object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\example\ExampleInterface $logger_channel_ict_gtfs
   *   The logger.channel.ict_gtfs service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   */
  public function __construct(ClientInterface $http_client, ExampleInterface $logger_channel_ict_gtfs, CacheBackendInterface $cache) {
    $this->httpClient = $http_client;
    $this->loggerChannelIctGtfs = $logger_channel_ict_gtfs;
    $this->cache = $cache;
  }

  /**
   * Method description.
   */
  public function doSomething() {
    // @DCG place your code here.
  }

}
