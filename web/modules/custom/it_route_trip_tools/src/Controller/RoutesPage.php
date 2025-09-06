<?php
namespace Drupal\it_route_trip_tools\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\media\MediaInterface;

/**
 * Provides route responses for the Example module.
 */
class RoutesPage extends ControllerBase {

  protected function getEditableConfigNames() {
    return [
      'it_route_trip_tools.settings',
    ];
  }

  public function BuildTitle($routeId) {
    $title = '';
    if ($routeId != 'all') {
      $request = \Drupal::request();
      if ($route = $request->attributes->get(\Drupal\Core\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
        $date = $request->query->get('date');
        $date = $date ?? date('Y-m-d');
        $title = it_route_trip_tools_build_route_title($routeId, $date);
      } else {
        $config = $this->config('it_route_trip_tools.settings');
        $title = $config->get('route_page_title');
      }
    }
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

  public static function loadAlertsByRoute($route_id) {
    // Load the node storage service.
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    // Load all published nodes of type "alert".
    $query = $node_storage->getQuery()
      ->accessCheck()
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
      ->accessCheck()
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

  public static function loadAllAlerts() {
      // Load the node storage service.
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      // Load all published nodes of type "alert".
      $query = $node_storage->getQuery()
        ->condition('type', 'rider_alerts')
        ->accessCheck()
        ->condition('field_start_date', date('Y-m-d'), '<')
        ->condition('field_end_date', date('Y-m-d'), '>')
        ->condition('status', 1);
      $nids = $query->execute();
      // Load the node entities.
      $alerts_with_end = $node_storage->loadMultiple($nids);
      // Load all published nodes of type "alert".
      $query = $node_storage->getQuery()
        ->condition('type', 'rider_alerts')
        ->accessCheck()
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

  public function getAllRoutesData($date = NULL) {
    return it_route_trip_tools_pics_get_all_routes_data($date);
  }

  public static function getRouteColor($route_id) {
    $mapping = [
      '12' => '#65A30D',
      '13' => '#2563EB',
      '14' => '#CA8A04',
      '21' => '#0C4A6E',
      '41' => '#4C1D95',
      '42' => '#BE123C',
      '45' => '#059669',
      '47' => '#1E40AF',
      '48' => '#525252',
      '60' => '#9333EA',
      '64' => '#0D9488',
      '65' => '#334155',
      '66' => '#DC2626',
      '67' => '#D97706',
      '68' => '#F43F5E',
      '94' => '#EA580C',
      'ONE' => '#0C4A6E',
      '600' => '#7E22CE',
      '610' => '#0EA5E9',
      '62A' => '#E11D48',
      '62B' => '#0284C7',
    ];
    return $mapping[$route_id] ?? '#000000';
  }

  public function BuildPage($routeId = NULL) {
    /*Need to grab the Routes form*/
    $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
    $routes_path = $config->get('route_page_path');
    $routes_options = it_route_trip_tools_pics_get_routes();
    $routes_options = array_map(function ($route) {
      return [
        'id' => $route['route_short_name'],
        'name' => $route['route_long_name'],
        'alerts_content' => RoutesPage::loadAlertsByRoute($route['route_short_name']),
      ];
    }, $routes_options);

    if (empty($routeId) || $routeId === 'all') {
      $all_routes_map_data_array = $this->getAllRoutesData();
      $all_routes_map_data_array = array_map(function($route) {
        $val = isset($route['Route']['MapInfo']['Shapes'][0]) ? reset($route['Route']['MapInfo']['Shapes'][0]) : [];
        return [
          'Shapes' => $val,
          'Color' => RoutesPage::getRouteColor($route['Route']['RouteInfo']['route_short_name']),
          'RouteName' => isset($route['Route']['RouteInfo']['route_long_name']) ? $route['Route']['RouteInfo']['route_long_name'] : '',
        ];
      }, $all_routes_map_data_array);
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
      $date = \Drupal::request()->query->get('date');
      $date = empty($date) ? date('Y-m-d') : $date;
      /*Grab the route data by route ID using it_route_trip_tools_get_route_data, which is in the module file*/
      $route_data_weekdays = it_route_trip_tools_get_route_table_map_data($routeId, $date);
      $request = \Drupal::request();
      if (empty($route_data_weekdays)) {
        $current_path = \Drupal::service('path.current')->getPath();
        $url = \Drupal\Core\Url::fromUserInput($current_path)->toString();
        return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
      }
      if ($route = $request->attributes->get(\Drupal\Core\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
        $new_title = $route_data_weekdays['short_name'] . ' - ' . $route_data_weekdays['long_name'];
        $route->setDefault('_title', $new_title);
      }

      [$year, $month, $day] = explode('-', $date);
      $routes_map_weekdays = [
        '#theme' => 'routes_map',
        '#route_data' => $route_data_weekdays,
        '#day' => $month . '/' . $day . '/' . $year,
      ];
      $routes_table_weekdays = [
        '#theme' => 'routes_table',
        '#route_data' => $route_data_weekdays
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
        '#routes_table_weekdays' => $routes_table_weekdays,
        '#route_data_weekdays' => $route_data_weekdays,
        '#all_routes_map_data' => 1,
        '#download_url' => $download_url,
        '#attached' => [
          'drupalSettings' => [
            'it_route_trip_tools' => [
              'routes_path' => $routes_path,
              'available_days' => it_route_trip_tools_pics_get_dates(),
            ]
          ]
        ]
      ];
    }


  }
}