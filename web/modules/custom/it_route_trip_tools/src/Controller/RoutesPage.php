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

    if (empty($routeId) || $routeId === 'all') {
      $config = $this->config('it_route_trip_tools.settings');
      $route_options_request = $config->get('route_options_request');
      $routes_options = it_route_trip_tools_build_routes_options($route_options_request);
      $alerts = $this->gtfs->getArray('Alert');
      return [
        '#theme' => 'routes_new_page',
        '#routes_options' => $routes_options,
        '#alert_options' => $this->getActiveAlerts($alerts['entity']),
        '#alert_view_all_link' => '/plan-your-trip/alerts',
      ];
    }
    
    /*Need to grab the Routes form*/
    $routes_form = \Drupal::formBuilder()->getForm('Drupal\it_route_trip_tools\Form\RoutesForm');
    $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
    $routes_path = $config->get('route_page_path');

    if ($routeId != 'all') {
      /*Grab the route data by route ID using it_route_trip_tools_get_route_data, which is in the module file*/
      $route_data = it_route_trip_tools_get_route_data($routeId);
      $request = \Drupal::request();
      if (empty($route_data)) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
      }
      if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
        $new_title = $route_data['short_name'] . ' - ' . $route_data['long_name'];
        $route->setDefault('_title', $new_title);
      }
      $all_routes_map_data = '';
      $all_routes_map_data_array = [];
      /*Build the routes form*/
      $build_form = [
        '#theme' => 'routes_form',
        '#form' => $routes_form,
        '#route_data' => $route_data,
      ];
      /*Build the routes map*/
      $routes_map = [
        '#theme' => 'routes_map',
        '#route_data' => $route_data
      ];
      $routes_table = [
        '#theme' => 'routes_table',
        '#route_data' => $route_data
      ];
      return [
        '#theme' => 'routes_page',
        '#routes_form' => $build_form,
        '#routes_map' => $routes_map,
        '#routes_table' => $routes_table,
        '#route_data' => $route_data,
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