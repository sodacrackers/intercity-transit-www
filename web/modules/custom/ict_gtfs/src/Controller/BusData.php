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
use Symfony\Component\HttpFoundation\JsonResponse;
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
    $service_type = $request->query->get('service_type') ?: '2';
    if ($routeId) {
      $route_data = it_route_trip_tools_get_route_table_map_data($routeId, $service_type);
      $trip_updates = $this->gtfs->getArray('TripUpdate');
      $vehicle_position = $this->gtfs->getArray('VehiclePosition');
      $route_data['alerts'] = $this->getRouteAlerts($routeId);
      foreach ($route_data['stop_markers'] as $direction_name => &$direction) {
        $direction_id = $direction_name == 'inbound' ? '1' : '0';
        $stop_times = \Drupal::service('ict.gtfs')->getStopTimes($routeId, $direction_id, $service_type);
        $trips = it_route_trip_get_trips($stop_times, FALSE);
        $vehicle_list = [];
        $stop_updates = $this->gtfs->getStopTimeUpdates($trip_updates, $trips, $vehicle_list);
        $route_data['vehicle_position'][$direction_name] = $this->gtfs->getVehiclePositions($vehicle_position, $vehicle_list, $routeId);
        foreach ($direction as $stop_id => &$stop_data) {
          $stop_data['real_time'] = $this->gtfs->getRealTimeByStopId($stop_data['stop_data']['stopId'], $stop_data['stop_data']['stopSequence'], $stop_updates);
        }
      }
      $context = new RenderContext();
      /** @var \Drupal\Core\Cache\CacheableJsonResponse $response */
      $response = $this->renderer->executeInRenderContext($context, function () use ($route_data) {
        return new JsonResponse($route_data);
      });
      return $response;
    }

  }

  public static function getRouteAlerts($route_id) {
    $alerts = self::loadAlertsByRoute($route_id);
    return array_map(function ($item) {
      return [
        'id' => $item->id(),
        'title' => $item->label(),
        'url' => $item->toUrl()->toString(),
        'severity' => $item->get('field_severity')->value,
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
