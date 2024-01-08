<?php
namespace Drupal\it_route_trip_tools\Controller;

use Drupal\ict_gtfs\Controller\BusData;
use Drupal\ict_gtfs\Gtfs;
use Drupal\Core\Controller\ControllerBase;
use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for the Example module.
 */
class RoutesPage extends ControllerBase {

  private Gtfs $gtfs;

  /**
   * ModalFormContactController constructor.
   *
   * @param \Drupal\ict_gtfs\Gtfs $form_builder
   *   The form builder.
   */
  public function __construct(Gtfs $form_builder) {
      $this->gtfs = $form_builder;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
      return new static(
          $container->get('ict.gtfs')
      );
  }
  
  protected function getEditableConfigNames() {
    return [
      'it_route_trip_tools.settings',
    ];
  }

  public function BuildTitle($routeId) {
    if ($routeId != 'all'):
      $request = \Drupal::request();
      if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)):
        $title = it_route_trip_tools_build_route_title($routeId);
      endif;
    else:
      $config = $this->config('it_route_trip_tools.settings');
      $title = $config->get('route_page_title');
    endif;
    return $title;
  }

  protected function getActiveAlerts($alerts) {
    $now = time();
    return array_filter($alerts, function ($alert) use ($now) {
      $active = FALSE;
      foreach ($alert['alert']['activePeriod'] as $active_period) {
        $active = $active || ($active_period['start'] <= $now && $active_period['end'] >= $now);
      }
      return $active;
    });
  }

  private function customizeOptions($options) {
    $new_options = [];
    foreach ($options as $route_id => $route_name) {
      $alerts = BusData::loadAlertsByRoute($route_id);
      $new_options[$route_id] = [
        'name' => $route_name,
        'alerts' => count($alerts),
        'alerts_content' => $alerts,
      ];
    }
    return $new_options;
  }

  public static function loadAllAlerts() {
      // Load the node storage service.
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      // Load all published nodes of type "alert".
      $query = $node_storage->getQuery()
        ->condition('type', 'rider_alerts')
        ->condition('field_start_date', date('Y-m-d'), '<')
        ->condition('field_end_date', date('Y-m-d'), '>')
        ->condition('status', 1);
      $nids = $query->execute();
      // Load the node entities.
      $alerts_with_end = $node_storage->loadMultiple($nids);
      // Load all published nodes of type "alert".
      $query = $node_storage->getQuery()
        ->condition('type', 'rider_alerts')
        ->condition('field_start_date', date('Y-m-d'), '<')
        ->notExists('field_end_date')
        ->condition('field_end_date_until_further_not', TRUE)
        ->condition('status', 1);
      $query->sort('created', 'DESC');
      $query->range(0, 8);
      $nids = $query->execute();
      // Load the node entities.
      $alerts_with_no_end = $node_storage->loadMultiple($nids);
      return array_merge($alerts_with_end, $alerts_with_no_end);
  }


  public static function getAllAlerts() {
    $alerts = self::loadAllAlerts();
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

  public function getAllRoutesData() {
    $routes = $this->gtfs->getStaticData('routes');
    $routes = array_filter($routes);
    $headers = array_shift($routes);
    $routes_id_index = array_search('route_id', $headers);
    $routes_color_index = array_search('route_color', $headers);
    $shapes = $this->gtfs->getStaticData('shapes');
    $headers = array_shift($shapes);
    $shape_index = array_search('shape_id', $headers);
    $shape_pt_lat_index = array_search('shape_pt_lat', $headers);
    $shape_pt_lon_index = array_search('shape_pt_lon', $headers);
    $trips = $this->gtfs->getStaticData('trips');
    $headers = array_shift($trips);
    $route_id_index = array_search('route_id', $headers);
    $shape_id_index = array_search('shape_id', $headers);
    $res = [];
    foreach ($routes as $route) {
      $route_id = $route[$routes_id_index];
      if (empty($route_id)) {
        continue;
      }
      $res[$route_id] = [
        "RouteName" => $route_id,
        "RouteDescription" => "RouteDescription",
        "Color" => '#' . ($route[$routes_color_index] ?? 'FFF'),
        "Shapes" => []
      ];
      $trips_in_route = array_filter($trips, function ($item) use ($route_id, $route_id_index) {
        return $item[$route_id_index] == $route_id;
      });
      foreach ($trips_in_route as $trip_in_route) {
        $shape_id = $trip_in_route[$shape_id_index] ?? NULL;
        $shape_items_in_trip = array_filter($shapes, function($item) use ($shape_index, $shape_id) {
          return $item[$shape_index] == $shape_id;
        });
        $res[$route_id]['Shapes'][] = [
          "shapeId" => $shape_id,
          "shapeData" => array_values(array_map(function ($item) use ($shape_pt_lat_index, $shape_pt_lon_index) {
            return isset($item[$shape_pt_lat_index]) && isset($item[$shape_pt_lon_index]) ? [
              'lat' => (float) $item[$shape_pt_lat_index],
              'lon' => (float) $item[$shape_pt_lon_index],
            ] : [];
          }, $shape_items_in_trip)),
        ];
      }
    }
    return array_values($res);
  }

  public function BuildPage($routeId = NULL) {

    $routes_options = it_route_trip_tools_build_routes_options(TRUE);
    $routes_options = $this->customizeOptions($routes_options);

    $all_routes_map_data_array = $this->getAllRoutesData();

    /*Need to grab the Routes form*/
    $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
    $routes_path = $config->get('route_page_path');

    if (empty($routeId) || $routeId === 'all') {
      $alerts = $this->getAllAlerts();
      return [
        '#theme' => 'routes_new_page',
        '#routes_options' => $routes_options,
        '#alert_options' => $alerts,
        '#alert_view_all_link' => '/rider-alerts',
        '#attached' => [
          'drupalSettings' => [
            'it_route_trip_tools' => [
              'all_routes_map_data_array' => [
                $all_routes_map_data_array
              ],
              'routes_path' => $routes_path
            ]
          ]
        ]
      ];
    }

    if ($routeId != 'all') {
      /*Grab the route data by route ID using it_route_trip_tools_get_route_data, which is in the module file*/
      $route_data_weekdays = it_route_trip_tools_get_route_table_map_data($routeId, 2);
      $route_data_weekend = it_route_trip_tools_get_route_table_map_data($routeId, 3);
      $request = \Drupal::request();
      if (empty($route_data_weekdays)) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
      }
      if ($route_data_weekend['bounding']['min']['lat'] == 999 && $route_data_weekend['bounding']['max']['lat'] == -999) {
        $route_data_weekend['bounding'] = $route_data_weekdays['bounding'];
      }
      if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
        $new_title = $route_data_weekdays['short_name'] . ' - ' . $route_data_weekdays['long_name'];
        $route->setDefault('_title', $new_title);
      }

      $routes_map_weekdays = [
        '#theme' => 'routes_map',
        '#route_data' => $route_data_weekdays,
        '#days' => 'weekdays',
      ];
      $routes_map_weekend = [
        '#theme' => 'routes_map',
        '#route_data' => $route_data_weekend,
        '#days' => 'weekends',
      ];
      $routes_table_weekdays = [
        '#theme' => 'routes_table',
        '#route_data' => $route_data_weekdays,
        '#valid_for' => 'weekdays',
      ];
      $routes_table_weekend = [
        '#theme' => 'routes_table',
        '#route_data' => $route_data_weekend,
        '#valid_for' => 'weekends',
      ];
      $medias = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties([
        'bundle' => 'route_pdfs',
        'name' => $routeId,
      ]);
      $media = reset($medias);
      $download_url = $media instanceof MediaInterface ? $media->get('field_document')->entity->createFileUrl() : '';
      return [
        '#theme' => 'routes_page',
        '#route_id' => $routeId,
        '#route_short_name' => $route_data_weekdays['short_name'],
        '#routes_options' => $routes_options,
        '#routes_map_weekdays' => $routes_map_weekdays,
        '#routes_map_weekend' => $routes_map_weekend,
        '#routes_table_weekdays' => $routes_table_weekdays,
        '#routes_table_weekend' => $routes_table_weekend,
        '#route_data_weekdays' => $route_data_weekdays,
        '#route_data_weekend' => $route_data_weekend,
        '#all_routes_map_data' => 1,
        '#download_url' => $download_url,
        '#attached' => [
          'drupalSettings' => [
            'it_route_trip_tools' => [
              'all_routes_map_data_array' => [
                $all_routes_map_data_array
              ],
              'routes_path' => $routes_path
            ]
          ]
        ]
      ];
    }


  }
}