<?php

namespace Drupal\ict_gtfs;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   * The GTFS API server.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * Allowed types.
   *
   * @var string[]
   */
  protected $allowedTypes;

  /**
   * The maximum age before refreshing the data, in seconds.
   *
   * @var int
   */
  protected $maxAge;

  /**
   * Get JSON from the FeedMessage object.
   *
   * @var bool
   *   If TRUE, get a FeedMessage object and serialize to JSON.
   *   If FALSE, use the debug parameter to get JSON from the API.
   */
  protected $jsonFromFeedMessage;

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
   * The zip files and configs.
   *
   * @var array;
   */
  private $items;

  /**
   * Constructs a Gtfs object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client, LoggerInterface $logger, CacheBackendInterface $cache, TimeInterface $time) {
    $settings = $config_factory->get('ict_gtfs.settings');
    $this->baseUrl = $settings->get('base_url');
    $this->maxAge = $settings->get('max_age');
    $this->allowedTypes = $settings->get('allowed_types');
    $this->jsonFromFeedMessage = $settings->get('json_from_object');
    $this->items = $settings->get('items');

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
   * Get the cache maximum age setting.
   *
   * @return int
   *   The maximum age before refreshing the data, in seconds.
   */
  public function getMaxAge(): int {
    return $this->maxAge;
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
//      $query['debug'] = 'true';
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

  public function getActiveConfiguration() {
    $now = time();
    return array_reduce($this->items ?: [], function ($carry, $item) use ($now) {
      $from = strtotime($item['detail']['date_from']);
      $to = strtotime($item['detail']['date_to']);
      return $from <= $now && $to >= $now ? $item : $carry;
    });
  }

  public function getActiveConfigurationPath() {
    $active_zip = $this->getActiveConfiguration();
    if ($active_zip) {
      $folder_name = $active_zip['detail']['date_from'] . '-' . $active_zip['detail']['date_to'] . '/';
      return \Drupal::service('file_system')->realpath('private://avail/' . $folder_name);
    }
  }

  public function getStaticData(string $data_type) {
    $path = $this->getActiveConfigurationPath();
    if ($path) {
      $filepath = $path . '/'. $data_type . '.txt';
      $file_to_read = file_get_contents($filepath);
      $file_to_read = str_replace("\r\n", "\n", $file_to_read);
      $rows_to_parse = explode("\n", $file_to_read);
      return array_map('str_getcsv', $rows_to_parse);
    }
    return [];
  }

  public function getTripsByRouteAndDirection(string $route_id, string $direction, string $service_type) {
    $trips = $this->getStaticData('trips');
    return array_filter($trips, function ($item) use ($route_id, $direction, $service_type) {
      return $item[0] === $route_id && $item[4] === $direction && $item[1] == $service_type;
    });
  }

  public function getStopTimes(string $route_id, string $direction, string $service_type) {
    $trips = array_values($this->getTripsByRouteAndDirection($route_id, $direction, $service_type));
    $trip_ids = array_map(function ($item) {
      return $item[2];
    }, $trips);
    $keyed_trips = [];
    foreach ($trips as $trip) {
      $keyed_trips[$trip[2]] = [
        'route_id' => $trip[0],
        'service_id' => $trip[1],
        'trip_id' => $trip[2],
        'trip_headsign' => $trip[3],
        'direction_id' => $trip[4],
        'block_id' => $trip[5],
        'shape_id' => $trip[6],
        'wheelchair_accessible' => $trip[7],
        'bikes_allowed' => $trip[8],
      ];
    }
    $stop_times = $this->getStaticData('stop_times');
    $stop_times_headers = array_shift($stop_times);
    $trip_id_index = array_search('trip_id', $stop_times_headers);
    $arrival_time_index = array_search('arrival_time', $stop_times_headers);
    $departure_time_index = array_search('departure_time', $stop_times_headers);
    $stop_id_index = array_search('stop_id', $stop_times_headers);
    $stop_sequence_index = array_search('stop_sequence', $stop_times_headers);
    $timepoint_index = array_search('timepoint', $stop_times_headers);
    $stop_times = array_filter($stop_times, function ($item) use ($trip_ids) {
      return in_array($item[0], $trip_ids);
    });
    usort($stop_times, function ($item, $comp) {
      return (int) $item[4] - (int) $comp[4];
    });
    $stops = $this->getStaticData('stops');
    $built_stops = [];
    foreach ($stops as $stop) {
      if ($stop[0]) {
        $built_stops[$stop[0]] = [
          'name' => $stop[2],
          'lat' => $stop[4],
          'lon' => $stop[5],
        ];
      }
    }

    $built_stop_times = [];
    foreach ($stop_times as $stop_time) {
      $built_stop_times[] = [
        'trip_id' => $stop_time[$trip_id_index],
        'arrival_time' => $stop_time[$arrival_time_index],
        'departure_time' => $stop_time[$departure_time_index],
        'stop_id' => $stop_time[$stop_id_index],
        'stop_sequence' => $stop_time[$stop_sequence_index],
        'timepoint' => $stop_time[$timepoint_index],
        'name' => $built_stops[$stop_time[$stop_id_index]]['name'],
        'lat' => $built_stops[$stop_time[$stop_id_index]]['lat'],
        'lon' => $built_stops[$stop_time[$stop_id_index]]['lon'],
        'shape_id' => $keyed_trips[$stop_time[$trip_id_index]]['shape_id']
      ];
    }

    return $built_stop_times;

  }

  public function getShapes($route_id, $direction, $service_type) {
    $shapes = $this->getStaticData('shapes');
    $lat_index = array_search('shape_pt_lat', $shapes[0]);
    $lon_index = array_search('shape_pt_lon', $shapes[0]);
    $trips = array_values($this->getTripsByRouteAndDirection($route_id, $direction, $service_type));
    $trip_shapes = array_unique(array_map(function ($item) {
      return $item[6];
    }, $trips));
    $ret_shapes = [];
    foreach ($trip_shapes as $trip_shape) {
      $filtered_shapes = array_filter($shapes, function ($item) use ($trip_shape, $lat_index, $lon_index) {
        return $item[0] == $trip_shape;
      });
      $ret_shapes[] = array_map(function ($item) use ($lat_index, $lon_index) {
        return [
          'lat' => $item[$lat_index],
          'lng' => $item[$lon_index],
        ];
      }, $filtered_shapes);
    }
    return $ret_shapes;

  }

  public function getStopTimeUpdates($json_data, $trip_list, &$vehicle_list) {
    $stop_time_updates = array();
    foreach ($json_data['entity'] as $entity) {
      if (in_array($entity['tripUpdate']['trip']['tripId'], $trip_list)) {
        $vehicle_id =
        $vehicle_label =
        $arrival_time =
        $arrival_delay =
        $departure_delay =
        $departure_time = NULL;
        if (!empty($entity['tripUpdate']['vehicle'])) {
          $vehicle_id = $entity['tripUpdate']['vehicle']['id'];
          $vehicle_list[$vehicle_id] = $vehicle_id;
          $vehicle_label = $entity['tripUpdate']['vehicle']['label'];
        }
        foreach ($entity['tripUpdate']['stopTimeUpdate'] ?? [] as $stop_time_update) {
          $stop_id = intval($stop_time_update['stopId']);
          if (isset($stop_time_update['arrival']) && $stop_time_update['arrival'] != NULL) {
            $arrival_delay = $stop_time_update['arrival']['delay'] ?? NULL;
            $arrival_time = $stop_time_update['arrival']['time'];
          }
          if (isset($stop_time_update['departure']) && $stop_time_update['departure'] != NULL) {
            $departure_delay = $stop_time_update['departure']['delay'] ?? NULL;
            $departure_time = $stop_time_update['departure']['time'];
          }
          $stop_time_updates[] = [
            'stop_id' => $stop_id,
            'vehicle_id' => $vehicle_id,
            'vehicle_label' => $vehicle_label,
            'arrival_time' => $arrival_time,
            'arrival_delay' => $arrival_delay,
            'departure_delay' => $departure_delay,
            'departure_time' => $departure_time,
          ];
        }
      }
    }
    $vehicle_list = array_values($vehicle_list);
    return $stop_time_updates;
  }

  public function getVehiclePositions($json_data, $vehicle_list) {
    $vehicle_positions = array();

    foreach ($json_data['entity'] as $entity) {
      if (isset($entity['vehicle']['vehicle']['id']) && in_array($entity['vehicle']['vehicle']['id'], $vehicle_list)) {
        $vehicle_id = $entity['vehicle']['vehicle']['id'];
        $latitude = $entity['vehicle']['position']['latitude'];
        $longitude = $entity['vehicle']['position']['longitude'];
        $bearing = $entity['vehicle']['position']['bearing'] ?? '';
        $vehicle_positions[] = ['vehicle_id' => $vehicle_id, 'latitude' => $latitude, 'longitude' => $longitude, 'bearing' => $bearing];
      }
    }
    return $vehicle_positions;
  }

  public function getRealTimeByStopId($stop_id, $stop_updates) {
    return array_filter($stop_updates, function ($item) use ($stop_id) {
      return $item['stop_id'] === $stop_id;
    });
  }

  public function getCurrentServiceType() {
    $calendar_dates = $this->getStaticData('calendar_dates');
    $calendar_structured = [];
    foreach ($calendar_dates as $calendar_date) {
      if (isset($calendar_date[1])) {
        $calendar_structured[$calendar_date[1]] = $calendar_date[0];
      }
    }
    $today = (int) date('Ymd');
    if (isset($calendar_structured[$today])) {
      return $calendar_structured[$today];
    }
    return (int) date('N', strtotime('now')) >= 6 ? '3' : '2';
  }

}
