<?php
namespace Drupal\it_route_trip_tools\Controller;

use Drupal\ict_gtfs\Gtfs;
use Drupal\Core\Controller\ControllerBase;
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

  public function BuildPage($routeId = NULL) {

    $config = $this->config('it_route_trip_tools.settings');
    $route_options_request = $config->get('route_options_request');
    $routes_options = it_route_trip_tools_build_routes_options(TRUE);
    if (empty($routeId) || $routeId === 'all') {
      $alerts = $this->gtfs->getArray('Alert');
      return [
        '#theme' => 'routes_new_page',
        '#routes_options' => $routes_options,
        '#alert_options' => $this->getActiveAlerts($alerts['entity']),
        '#alert_view_all_link' => '/plan-your-trip/alerts',
      ];
    }

    /*Need to grab the Routes form*/
    $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
    $routes_path = $config->get('route_page_path');

    if ($routeId != 'all') {
      /*Grab the route data by route ID using it_route_trip_tools_get_route_data, which is in the module file*/
      $route_data_weekdays = it_route_trip_tools_get_route_table_map_data($routeId, 1);
      $route_data_weekend = it_route_trip_tools_get_route_table_map_data($routeId, 2);
      $request = \Drupal::request();
      if (empty($route_data_weekdays)) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
      }
      if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
        $new_title = $route_data_weekdays['short_name'] . ' - ' . $route_data_weekdays['long_name'];
        $route->setDefault('_title', $new_title);
      }
      $all_routes_map_data = '';
      $all_routes_map_data_array = [];

      $routes_map_weekdays = [
        '#theme' => 'routes_map',
        '#route_data' => $route_data_weekdays
      ];
      $routes_table_weekdays = [
        '#theme' => 'routes_table',
        '#route_data' => $route_data_weekdays
      ];
      $routes_map_weekend = [
        '#theme' => 'routes_map',
        '#route_data' => $route_data_weekend
      ];
      $routes_table_weekend = [
        '#theme' => 'routes_table',
        '#route_data' => $route_data_weekend
      ];
      return [
        '#theme' => 'routes_page',
        '#route_id' => $routeId,
        '#routes_options' => $routes_options,
        '#routes_map_weekdays' => $routes_map_weekdays,
        '#routes_map_weekend' => $routes_map_weekend,
        '#routes_table_weekdays' => $routes_table_weekdays,
        '#routes_table_weekend' => $routes_table_weekend,
        '#route_data_weekdays' => $route_data_weekdays,
        '#route_data_weekend' => $route_data_weekend,
        '#all_routes_map_data' => $all_routes_map_data,
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