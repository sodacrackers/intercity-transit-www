<?php

namespace Drupal\ict_gtfs;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Url;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

/**
 * Fetch, cache, and parse data from a GTFS API endpoint.
 */
class Gtfs {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The time service.
   *
   * @var \Drupal\example\ExampleInterface
   */
  protected $time;

  /**
   * Allowed types.
   *
   * @var string[]
   */
  protected $allowedTypes = [
    'Alert',
    'TripUpdate',
    'VehiclePosition',
  ];

  /**
   * The maximum age before refreshing the data, in seconds.
   *
   * @var int
   */
  protected $maxAge = 60;

  /**
   * The GTFS API server.
   *
   * @var string
   */
  protected $baseUrl = 'https://its.rideralerts.com/InfoPoint/GTFS-realtime.ashx';

  /**
   * Constructs a Gtfs object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(ClientInterface $http_client, LoggerInterface $logger, CacheBackendInterface $cache, TimeInterface $time) {
    $this->httpClient = $http_client;
    $this->logger = $logger;
    $this->cache = $cache;
    $this->time = $time;
  }

  /**
   * Get GTFS data.
   *
   * @param string $type
   *   The type of data to get: one of
   *   - Alert
   *   - TripUpdate
   *   - VehiclePosition.
   *
   * @return array
   *   A nested array with the keys
   *   - Header
   *     - GtfsRealtimeVersion
   *     - incrementality
   *     - Timestamp
   *   - Entities
   *   The Entities array is keyed by item.
   */
  public function get(string $type): array {
    $gtfs_data = [
      'Header' => [
        'GtfsRealtimeVersion' => 0,
        'incrementality' => 0,
        'Timestamp' => 0,
      ],
      'Entities' => [],
    ];
    $args = ['%type' => $type];

    // Get the data from the cache or the external API.
    $json = $this->getJson($type);

    // Validate the JSON string. Get a nested array, not an object.
    $gtfs_array = json_decode($json, TRUE);
    if (!is_array($gtfs_array)) {
      $this->logger->warning('JSON for type %type does not represent an array.', $args);
      $this->logger->debug('json = "%json"', ['%json' => substr($json, 0, 100)]);
      return $gtfs_data;
    }

    // Index the entities by ID. Fall back to numeric key.
    $gtfs_data['Header'] = $gtfs_array['Header'] ?? [];
    foreach ($gtfs_array['Entities'] ?? [] as $key => $item) {
      $item_key = $item['Id'] ?? $key;
      $gtfs_data['Entities'][$item_key] = $item;
    }

    return $gtfs_data;
  }

  /**
   * Get GTFS data as JSON from cache or the external server.
   *
   * @param string $type
   *   The type of data to get: one of
   *   - Alert
   *   - TripUpdate
   *   - VehiclePosition.
   *
   * @return string
   *   A JSON string representing the requested data. If anything goes wrong,
   *   then return an empty string.
   */
  protected function getJson(string $type): string {
    $args = ['%type' => $type];

    // Validate the $type parameter.
    if (!in_array($type, $this->allowedTypes)) {
      $this->logger->warning('Unsupported GTFS type %type.', $args);
      return '';
    }

    $cid = "ict_gtfs:$type";
    $cache = $this->cache->get($cid);
    if ($cache !== FALSE) {
      return $cache->data;
    }

    // Fetch the data from the API endpoint.
    $query = ['Type' => $type, 'debug' => 'true'];
    $url = Url::fromUri($this->baseUrl, ['query' => $query]);
    try {
      $response = $this->httpClient->get($url->toString())->getBody();
    }
    catch (RequestException $e) {
      $this->logger->warning('Unable to fetch GTFS type %type from the server.', $args);
      return '';
    }

    if (!$response->isReadable()) {
      $this->logger->warning('Cannot read response from the server for GTFS type %type.', $args);
      return '';
    }

    $json = (string) $response;
    $expire = $this->time->getRequestTime() + $this->maxAge;
    $this->cache->set($cid, $json, $expire);

    return $json;
  }

}
