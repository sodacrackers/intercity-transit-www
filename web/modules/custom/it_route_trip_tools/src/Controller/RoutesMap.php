<?php
namespace Drupal\it_route_trip_tools\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class RoutesMap extends ControllerBase {
  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function routesMap($routeId = NULL) {
    return [
      '#theme' => 'routes_map'
    ];
  }
}