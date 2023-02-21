<?php
namespace Drupal\it_route_trip_tools\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class RoutesTableData extends ControllerBase {
  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function buildTable($routeId = NULL) {
    $routes_form = \Drupal::formBuilder()->getForm('Drupal\it_route_trip_tools\Form\RoutesForm');
    $routes_table_data = \Drupal::formBuilder()->getForm('Drupal\it_route_trip_tools\Form\RoutesForm');
    $route_data = it_route_trip_tools_get_route_data($routeId);
    $build_form = [
      '#theme' => 'routes_form',
      '#form' => $routes_form,
    ];
    return [
      '#theme' => 'routes_page',
      '#routes_form' => $build_form,
      '#routes_table_data' => $routes_tables,
      '#route_data' => $route_data
    ];
  }
}