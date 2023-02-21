<?php

namespace Drupal\it_route_trip_tools\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class TripPlannerPage extends ControllerBase {

  protected function getEditableConfigNames() {
    return [
      'it_route_trip_tools.settings',
    ];
  }
  
  public function BuildTitle() {
    $config = $this->config('it_route_trip_tools.settings');
    $title = $config->get('trip_planner_page_title');
    return $title;
  }

  /**
   * Returns a render-able array for a test page.
   */
  public function BuildPage() {
    $build = [
      '#theme' => 'trip_planner_page',
      'trip_data' => [],
    ];
    return $build;
  }

}