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
      if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)):
        $title = it_route_trip_tools_build_stop_title($stopId);
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
    $stop_data = array();
    $stop_id = '';
    $stop_name = '';
    $stop_lat = '';
    $stop_lon = '';
    if (($stopId != '') && (is_numeric($stopId))):
      $stop_result = it_route_trip_tools_get_stop_details($stopId);
      $schedule_count = 0;
      $stops_map_data_array = array();
      if (!empty($stop_result)):
        $schedule_times = array();
        foreach ($stop_result['routes'] as $route):
            foreach($route['schedule'] as $schedule):
              if($schedule['stopTimes'] != NULL):
                $schedule_times_init[$schedule['scheduleId']]['times'] = $schedule['stopTimes'];
                $schedule_count++;
              endif;
            endforeach;
            if($schedule_count > 1):
              if ($schedule_times_init['1.0'] === $schedule_times_init['2.0']):
                $schedule_times['everyday'] = $schedule_times_init['1.0'];
              else:
                foreach ($schedule_times_init as $schedule_id => $times):
                  if ($schedule_id == '1.0'):
                    $schedule_times['weekdays'] = $times;
                  elseif ($schedule_id == '2.0'):
                    $schedule_times['weekends'] = $times;
                  endif;
                endforeach;
              endif;
            else:
              if (isset($schedule_times_init['1.0'])):
                $schedule_times['weekdays'] = $schedule_times_init['1.0'];
              elseif (isset($schedule_times_init['2.0'])):
                $schedule_times['weekends'] = $schedule_times_init['2.0'];
              endif;
            endif;
            $schedule_count = 0;
            $route_times[$route['routeShortName']] = $schedule_times;
            $stops_map_data_array[$route['routeShortName']] = $route['shape'];
            $schedule_times = array();         
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
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
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