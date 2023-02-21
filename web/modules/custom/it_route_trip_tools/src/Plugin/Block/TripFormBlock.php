<?php

namespace Drupal\it_route_trip_tools\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 *
 * @Block(
 *   id = "trip_form_block",
 *   admin_label = @Translation("Trip Form Block"),
 *   category = @Translation("Trip Form Block"),
 * )
 */

class TripFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */

  public function build() {
    $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
    $trip_planner_path = $config->get('trip_planner_page_path');
    return [
      '#theme' => 'trip_form_block',
      '#trip_planner_path' => $trip_planner_path,
    ];
  }

}