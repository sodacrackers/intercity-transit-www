<?php

namespace Drupal\it_route_trip_tools;

use Drupal\Core\Render\Markup;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ItTwigExtensions extends AbstractExtension {
  /**
   * Here is where we declare our new filter.
   * @return array
   */
  public function getFilters() {
    $filters = [ 
      new TwigFilter('timeonly',['Drupal\it_route_trip_tools\ItTwigExtensions', 'timeOnly'])
    ];
    return $filters;
  }

  public function getFunctions() {
    $functions = [
      new TwigFunction('tripplanner',['Drupal\it_route_trip_tools\ItTwigExtensions', 'tripPlanner']),
    ];
    return $functions;
  }
  /**
  * This is the same name we used on the services.yml file
  * @return string
  */
  public function getName() {
    return "it_route_trip_tools.twig_extension";
  }
  /**
   * @param $string
   * @return float
   */
  public static function timeOnly($string) {
    if ($string):
      $date = date_create($string, timezone_open('America/Los_Angeles'));
      return date_format($date, 'g:i a');
    else:
      return $string;
    endif;
  }
  public static function tripPlanner() {
    if (!empty($_POST)):
      $trip_data = array();  
      $trip_data['start_add'] = $_POST['start_add'];
      $trip_data['start_add_id'] = $_POST['start_add_id'];
      $trip_data['dest_add'] = $_POST['dest_add'];
      $trip_data['dest_add_id'] = $_POST['dest_add_id'];
      $trip_data['opt'] = $_POST['opt'];
      $trip_data['selected_time'] = $_POST['selected_time'];
      $trip_data['time'] = $_POST['time'];
      $trip_data['date'] = $_POST['date'];
      $trip_data['ttype'] = $_POST['ttype'];
      return $trip_data;
    endif;
  }
}