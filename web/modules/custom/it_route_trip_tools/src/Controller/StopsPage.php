<?php

namespace Drupal\it_route_trip_tools\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class StopsPage extends ControllerBase {

  protected function getEditableConfigNames() {
    return [
      'it_route_trip_tools.settings',
    ];
  }
  
  public function BuildTitle($stopId = NULL) {
    if ($stopId != 'all'):
      $request = \Drupal::request();
      if ($route = $request->attributes->get(\Drupal\Core\Routing\RouteObjectInterface::ROUTE_OBJECT)):
        $stop = it_route_trip_tools_get_stop_details($stopId);
        $title = $stop['stopName'];
      endif;
    else:
      $config = $this->config('it_route_trip_tools.settings');
      $title = $config->get('stops_page_title');
    endif;
    return $title;
  }

  /**
   * Returns a render-able array for a test page.
   */
  public function BuildPage($stopId = NULL) {
    $date = \Drupal::request()->query->get('date');
    $date = empty($date) ? date('Y-m-d') : $date;
    $stop_data = array();
    $stop_id = '';
    $stop_name = '';
    $stop_lat = '';
    $stop_lon = '';
    if (($stopId != '') && (is_numeric($stopId))):
      $stop_result = it_route_trip_tools_get_stop_details($stopId);
      $stops_map_data_array = array();
      if (!empty($stop_result)):
        $route_times = [];
        $schedule_times = array();
        foreach ($stop_result['routes'] as $route):
          foreach ($route['schedule'] as $times):
            if (isset($times['scheduleId']) && $times['scheduleId'] == '1.0'):
              $schedule_times['Inbound'] = $times['stopTimes'];
            elseif (isset($times['scheduleId']) && $times['scheduleId'] == '2.0'):
              $schedule_times['Outbound'] = $times['stopTimes'];
            endif;
          endforeach;
          $route_times[$route['routeShortName']] = $schedule_times;
          $stops_map_data_array[$route['routeShortName']] = $route['shape'];
        endforeach;
        $stop_data = array(
          'stop_name' => $stop_result['stopName'],
          'stop_lat' => $stop_result['stopLat'],
          'stop_lon' => $stop_result['stopLon'],
          'timepoint' => $stop_result['timepoint'],
          'schedule_times' => $route_times
        );
        $stop_id = $stopId;
        $stop_name = $stop_result['stopName'];
        $stop_lat = $stop_result['stopLat'];
        $stop_lon = $stop_result['stopLon'];
      endif;
    else:
      return [
        '#markup' => $this->t('Please select a Stop ID and a date to view details.'),
      ];
    endif;
    $build = [
      '#theme' => 'stops_page',
      '#stop_id' => $stopId,
      '#stop_data' => $stop_data,
      '#attached' => [
        'drupalSettings' => [
          'it_route_trip_tools' => [
            'stops_map_data_array' => [
              $stops_map_data_array
            ],
            'stop_id' => $stop_id,
            'stop_name' => $stop_name,
            'stop_lat' => $stop_lat,
            'stop_lon' => $stop_lon,
          ]
        ]   
      ]
    ];
    return $build;
  }

}