<?php

namespace Drupal\ict_gtfs\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\ict_gtfs\Gtfs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides route responses for the Example module.
 */
class BusData extends ControllerBase {

  /**
   * The service to pull remote data from.
   *
   * @var Gtfs
   */
  private $gtfs;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private RendererInterface $renderer;

  /**
   * BusData constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $gtfs
   *   The gtfs service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(Gtfs $gtfs, RendererInterface $renderer, ModuleExtensionList $module_handler) {
    $this->gtfs = $gtfs;
    $this->renderer = $renderer;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ict.gtfs'),
      $container->get('renderer'),
      $container->get('extension.list.module'),
    );
  }

  public function json(Request $request) {
    $routeId = $request->query->get('route_id');
    $service_type = $request->query->get('service_type') ?: 1;
    if ($routeId) {
      $route_data = it_route_trip_tools_get_route_table_map_data($routeId, $service_type);
      $trip_updates = $this->gtfs->getArray('TripUpdate');
      $vehicle_position = $this->gtfs->getArray('VehiclePosition');
      $route_data['alerts'] = $this->getRouteAlerts($routeId);
      foreach ($route_data['stop_markers'] as $direction_name => &$direction) {
        $direction_id = $direction_name == 'inbound' ? '0' : '1';
        $trips = $this->getTripsByRouteAndDirection($routeId, $direction_id);
        $trip_ids = array_map(function ($item) {
          return $item[2];
        }, $trips);
        $stop_updates = $this->getStopTimeUpdates($trip_updates, $trip_ids);
        $route_data['vehicle_position'][$direction_name] = $this->getVehiclePositions($vehicle_position, $trip_ids);
        foreach ($direction as $stop_id => &$stop_data) {
          $stop_data['real_time'] = $this->getRealTimeByStopId($stop_id, $stop_updates);
        }
      }
      $context = new RenderContext();
      /** @var \Drupal\Core\Cache\CacheableJsonResponse $response */
      $response = $this->renderer->executeInRenderContext($context, function () use ($route_data) {
        $response = CacheableJsonResponse::create($route_data);
        $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
          '#cache' => [
            'max-age' => 30,
          ],
        ]));
        return $response;
      });
      return $response;
    }

  }

  private function getStaticData(string $data_type) {
    $filepath = $this->moduleHandler->getPath('ict_gtfs') . '/data/' . $data_type . '.txt';
    $file_to_read = file_get_contents($filepath);
    $rows_to_parse = explode("\r\n", $file_to_read);
    return array_map('str_getcsv', $rows_to_parse);
  }

  private function getTripsByRouteAndDirection(string $route_id, string $direction) {
    $trips = $this->getStaticData('trips');
    return array_filter($trips, function ($item) use ($route_id, $direction) {
      return $item[0] === $route_id && $item[4] === $direction;
    });
  }

  private function getStopTimeUpdates($json_data, $trip_list) {
    $stop_time_updates = array();
    foreach ($json_data['entity'] as $entity) {
      if (in_array($entity['tripUpdate']['trip']['tripId'], $trip_list)) {
        if (!empty($entity['tripUpdate']['vehicle'])) {
          $vehicle_id = $entity['tripUpdate']['vehicle']['id'];
          $vehicle_label = $entity['tripUpdate']['vehicle']['label'];
        }
        foreach ($entity['tripUpdate']['stopTimeUpdate'] ?? [] as $stop_time_update) {
          $stop_id = intval($stop_time_update['stopId']);
          if ($stop_time_update['arrival'] != NULL) {
            $arrival_delay = $stop_time_update['arrival']['delay'] ?? NULL;
            $arrival_time = $stop_time_update['arrival']['time'];
          }
          if ($stop_time_update['departure'] != NULL) {
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
    return $stop_time_updates;
  }

  private function getVehiclePositions($json_data, $trip_list) {
    $vehicle_positions = array();

    foreach ($json_data['entity'] as $entity) {
      if ($entity['vehicle'] != NULL) {
        if (!empty($entity['vehicle']['trip'])) {
          if ($entity['vehicle']['trip']['tripId'] != NULL && in_array($entity['vehicle']['trip']['tripId'], $trip_list)) {
            $vehicle_id = $entity['vehicle']['vehicle']['id'];
            $latitude = $entity['vehicle']['position']['latitude'];
            $longitude = $entity['vehicle']['position']['lngitude'];
            $bearing = $entity['vehicle']['position']['bearing'];
            $vehicle_positions[] = ['vehicle_id' => $vehicle_id, 'latitude' => $latitude, 'longitude' => $longitude, 'bearing' => $bearing];
          }
        }
      }
    }
    return $vehicle_positions;
  }

  private function getRealTimeByStopId($stop_id, $stop_updates) {
    return array_filter($stop_updates, function ($item) use ($stop_id) {
      return $item['stop_id'] === $stop_id;
    });
  }

  private function loadAlertsByRoute($route_id) {
    // Load the node storage service.
    $node_storage = $this->entityTypeManager()->getStorage('node');
    // Load all published nodes of type "alert".
    $query = $node_storage->getQuery()
      ->condition('type', 'rider_alerts')
      ->condition('field_affected_routes_new_.entity.name', $route_id)
      ->condition('field_start_date', date('Y-m-d'), '<')
      ->condition('field_end_date', date('Y-m-d'), '>')
      ->condition('status', 1);
    $nids = $query->execute();
    // Load the node entities.
    $alerts_with_end = $node_storage->loadMultiple($nids);
    // Load all published nodes of type "alert".
    $query = $node_storage->getQuery()
      ->condition('type', 'rider_alerts')
      ->condition('field_affected_routes_new_.entity.name', $route_id)
      ->condition('field_start_date', date('Y-m-d'), '<')
      ->notExists('field_end_date')
      ->condition('field_end_date_until_further_not', TRUE)
      ->condition('status', 1);
    $nids = $query->execute();
    // Load the node entities.
    $alerts_with_no_end = $node_storage->loadMultiple($nids);
    return array_merge($alerts_with_end, $alerts_with_no_end);

  }

  private function getRouteAlerts($route_id) {
    $alerts = $this->loadAlertsByRoute($route_id);
    return array_map(function ($item) {
      return [
        'id' => $item->id(),
        'title' => $item->label(),
        'url' => $item->toUrl()->toString(TRUE),
        'description' => $item->get('body')->getValue()[0],
        'affected_routes' => array_map(function ($routes) {
          return $routes->label();
        }, $item->get('field_affected_routes_new_')->referencedEntities()),
        'start_date' => $item->get('field_start_date')->value,
        'end_date' => $item->get('field_end_date')->value,
        'end_until_further_notice' => $item->get('field_end_date_until_further_not')->value,
        'detour_map' => empty($item->get('field_image')->entity) ? NULL : $item->get('field_image')->entity->createFileUrl(),
        'live_nid' => $item->get('field_live_nid')->value,
      ];
    }, $alerts);
  }

}