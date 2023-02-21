<?php

namespace Drupal\it_route_trip_tools\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 *
 * @Block(
 *   id = "routes_form_block",
 *   admin_label = @Translation("Routes Form Block"),
 *   category = @Translation("Routes Form Block"),
 * )
 */

class RoutesFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */

  public function build($routeId = NULL) {
    $routes_form = \Drupal::formBuilder()->getForm('Drupal\it_route_trip_tools\Form\RoutesForm');
    $build_form = [
      '#theme' => 'routes_form',
      '#form' => $routes_form,
    ];
    return [
      '#theme' => 'routes_form_block',
      '#routes_form' => $build_form
    ];
  }

}