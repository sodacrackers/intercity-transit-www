<?php
namespace Drupal\it_route_trip_tools\Controller;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Controller\ControllerBase;

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
  public function BuildPage($routeId = NULL) {

    /*Need to grab the Routes form*/
//    $routes_form = \Drupal::formBuilder()->getForm('Drupal\it_route_trip_tools\Form\RoutesForm');
//    $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
//    $routes_path = $config->get('route_page_path');
//    $routes_stops_api_base = $config->get('route_stops_api_base');
//    $shapes_request = $config->get('route_shapes_request');
//    $shapes_path = $routes_stops_api_base . '/' . $shapes_request;
//
//    if ($routeId != 'all'):
//      /*Grab the route data by route ID using it_route_trip_tools_get_route_data, which is in the module file*/
//      $route_data = it_route_trip_tools_get_route_data($routeId);
//      $request = \Drupal::request();
//      if (empty($route_data)):
//        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
//      endif;
//      if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)):
//        $new_title = $route_data['short_name'] . ' - ' . $route_data['long_name'];
//        $route->setDefault('_title', $new_title);
//      endif;
//      $all_routes_map_data = '';
//      $all_routes_map_data_array = [];
//    else:
//      $all_routes_map_data_array = it_route_trip_tools_get_api_data($shapes_path,'all_routes_map');
//      $all_routes_map_data = 1;
//      $route_data = '';
//    endif;
//    /*Build the routes form*/
//    $build_form = [
//      '#theme' => 'routes_form',
//      '#form' => $routes_form,
//      '#route_data' => $route_data,
//    ];
//
//    /*Build the routes map*/
//    $routes_map = [
//      '#theme' => 'routes_map',
//      '#route_data' => $route_data
//    ];
//    $routes_table = [
//      '#theme' => 'routes_table',
//      '#route_data' => $route_data
//    ];
    return [
//      '#theme' => 'routes_page',
//      '#routes_form' => $build_form,
//      '#routes_map' => $routes_map,
//      '#routes_table' => $routes_table,
//      '#route_data' => $route_data,
//      '#all_routes_map_data' => $all_routes_map_data,
//      '#attached' => [
//        'drupalSettings' => [
//          'it_route_trip_tools' => [
//            'all_routes_map_data_array' => [
//              $all_routes_map_data_array
//            ],
//            'routes_path' => $routes_path
//          ]
//        ]
//      ]
    ];
  }
}