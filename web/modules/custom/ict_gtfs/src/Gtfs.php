<?php

namespace Drupal\ict_gtfs;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Url;
use Google\Transit\Realtime\FeedMessage;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Stream;
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
   * Get JSON from the FeedMessage object.
   *
   * @var bool
   *   If TRUE, get a FeedMessage object and serialize to JSON.
   *   If FALSE, use the debug parameter to get JSON from the API.
   */
  protected $jsonFromFeedMessage = TRUE;

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
   * List the available types of data.
   *
   * @return string[]
   *   The available types.
   */
  public function getAllowedTypes(): array {
    return $this->allowedTypes;
  }

  /**
   * Get the jsonFromFeedMessage setting.
   *
   * @return bool
   *   If TRUE, get a FeedMessage object and serialize to JSON.
   */
  public function getJsonFromFeedMessage(): bool {
    return $this->jsonFromFeedMessage;
  }

  /**
   * Get the cache maximum age setting.
   *
   * @return int
   *   The maximum age before refreshing the data, in seconds.
   */
  public function getMaxAge(): int {
    return $this->maxAge;
  }

  /**
   * Get GTFS data as an object.
   *
   * @param string $type
   *   The type of data to get: see ::getAllowedTypes().
   *
   * @return Google\Transit\Realtime\FeedMessage|null
   *   A FeedMessage object representing the requested data.
   *   Return NULL if there is an error or the data is empty.
   */
  public function getObject(string $type): ?FeedMessage {
    $args = ['%type' => $type];

    // Validate the $type parameter.
    if (!in_array($type, $this->getAllowedTypes())) {
      $this->logger->warning('Unsupported GTFS type %type.', $args);
      return NULL;
    }

    // Get the data from the cache or the external API.
    $cid = "ict_gtfs:$type:protobuf";
    $cache = $this->cache->get($cid);
    if ($cache !== FALSE) {
      $data = $cache->data;
    }
    else {
      $data = $this->fetchData($type, 'protobuf');
      $expire = $this->time->getRequestTime() + $this->maxAge;
      $this->cache->set($cid, $data, $expire);
    }

    // Load the data into an object.
    try {
      $message = new FeedMessage();
      $message->mergeFromString($data);
    }
    catch (\Exception $e) {
      return NULL;
    }

    return $message;
  }

  /**
   * Get GTFS data as a PHP array.
   *
   * @param string $type
   *   The type of data to get: see ::getAllowedTypes().
   *
   * @return array
   *   A nested array with the keys
   *   - header
   *     - gtfsRealtimeVersion
   *     - timestamp
   *   - entity
   *   The entity array is keyed by item ID.
   */
  public function getArray(string $type): array {
    $gtfs_data = [
      'header' => [
        'gtfsRealtimeVersion' => 0,
        'timestamp' => 0,
      ],
      'entity' => [],
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

    // Use the right keys for ordinary data or debug data.
    [$header_key, $entity_key, $id_key] = $this->jsonFromFeedMessage
      ? ['header', 'entity', 'id']
      : ['Header', 'Entities', 'Id'];

    // Index the entities by ID. Fall back to numeric key.
    $gtfs_data['header'] = $gtfs_array[$header_key] ?? [];
    foreach ($gtfs_array[$entity_key] ?? [] as $key => $item) {
      $item_key = $item[$id_key] ?? $key;
      $gtfs_data['entity'][$item_key] = $item;
    }

    return $gtfs_data;
  }

  /**
   * Get GTFS data from the external server.
   *
   * @param string $type
   *   The type of data to get: see ::getAllowedTypes().
   * @param string $format
   *   The data format: either 'json' or 'protobuf'.
   *
   * @return string
   *   A string representing the requested data. If anything goes wrong, then
   *   return an empty string.
   */
  protected function fetchData(string $type, string $format): string {
    $args = ['%type' => $type, '%format' => $format];

    // Validate the $type parameter.
    if (!in_array($type, $this->getAllowedTypes())) {
      $this->logger->warning('Unsupported GTFS type %type.', $args);
      return '';
    }

    // Validate the $format parameter.
    if (!in_array($format, ['json', 'protobuf'])) {
      $this->logger->warning('Unsupported format %format.', $args);
      return '';
    }

    // Fetch the data from the API endpoint.
    $query = ['Type' => $type];
    if ($format === 'json') {
      $query['debug'] = 'true';
    }
    $url = Url::fromUri($this->baseUrl, ['query' => $query]);
    try {
      $response = $this->httpClient->get($url->toString())->getBody();
      assert($response instanceof Stream);
    }
    catch (RequestException $e) {
      $this->logger->warning('Unable to fetch GTFS type %type from the server in format %format.', $args);
      return '';
    }

    if (!$response->isReadable()) {
      $this->logger->warning('Cannot read response from the server for GTFS type %type in format %format.', $args);
      return '';
    }

    return (string) $response;
  }

  /**
   * Get GTFS data as JSON from cache or the external server.
   *
   * @param string $type
   *   The type of data to get: see ::getAllowedTypes().
   *
   * @return string
   *   A JSON string representing the requested data. If anything goes wrong,
   *   then return an empty string.
   */
  protected function getJson(string $type): string {
    $args = ['%type' => $type];

    // Validate the $type parameter.
    if (!in_array($type, $this->getAllowedTypes())) {
      $this->logger->warning('Unsupported GTFS type %type.', $args);
      return '';
    }

    if ($this->jsonFromFeedMessage) {
      // Cache the protobuf string, not the JSON.
      $message = $this->getObject($type);
      return $message === NULL ? '' : $message->serializeToJsonString();
    }

    $cid = "ict_gtfs:$type:json";
    $cache = $this->cache->get($cid);
    if ($cache !== FALSE) {
      return $cache->data;
    }

    // Fetch the data from the API endpoint.
    $json = $this->fetchData($type, 'json');
    $expire = $this->time->getRequestTime() + $this->maxAge;
    $this->cache->set($cid, $json, $expire);

    return $json;
  }

}
