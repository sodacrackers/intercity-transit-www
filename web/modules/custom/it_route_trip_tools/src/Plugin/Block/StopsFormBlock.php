<?php

namespace Drupal\it_route_trip_tools\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 *
 * @Block(
 *   id = "stops_form_block",
 *   admin_label = @Translation("Stops Form Block"),
 *   category = @Translation("Stops Form Block"),
 * )
 */

class StopsFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */

  public function build() {
    $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
    $api_base = $config->get('route_stops_api_base');
    $stops_request = $config->get('stops_options_request'); 
    $stop_options_path = $api_base . '/' . $stops_request;
    $stop_options = it_route_trip_tools_get_api_data($stop_options_path, 'stop_options');
    $stops_path = $config->get('stops_page_path');
    $stop_url_id = basename($_SERVER['REQUEST_URI']);
    if (is_numeric($stop_url_id)):
      $stop_id = $stop_url_id;
    else:
      $stop_id = 'all';
    endif;
    return [
      '#theme' => 'stops_form_block',
      '#stop_options' => $stop_options,
      '#stops_path' => $stops_path,
      '#stop_id' => $stop_id
    ];
  }

}