<?php

namespace Drupal\it_route_trip_tools\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Routing;

/**
 *
 * @Block(
 *   id = "route_trip_tools_form_block",
 *   admin_label = @Translation("Route and Trip Forms Block"),
 *   category = @Translation("Route and Trip Forms Block"),
 * )
 */

class RouteTripToolsFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */

  public function build($routeId = NULL) {
    function block_renderer($bid) {
      $block_manager = \Drupal::service('plugin.manager.block');
      $config = [];
      $plugin_block = $block_manager->createInstance($bid, $config);
      $render = $plugin_block->build();
      return \Drupal::service('renderer')->render($render);
    }
    $route_id = \Drupal::routeMatch()->getParameter('routeId');
    if (($route_id != 'all') && ($route_id != NULL)):
        $route_param = $route_id;
    else:
      $route_param = '';
    endif;
    $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
    $trip_planner_path = $config->get('trip_planner_page_path');
    $routes_path = $config->get('route_page_path');
    $stops_path = $config->get('stops_page_path');
    $current_uri = \Drupal::request()->getRequestUri();
    $current_page = 'none';
    if(strpos($current_uri, $trip_planner_path) !== FALSE):
      $current_page = 'trip_planner';
    elseif(strpos($current_uri, $routes_path) !== FALSE):
      $current_page = 'routes';
    elseif(strpos($current_uri, $stops_path) !== FALSE):
      $current_page = 'stops';
    endif;
    return [
      '#theme' => 'route_trip_tools_form_block',
      '#trip_form_block' => block_renderer('trip_form_block'),
      '#stops_form_block' => block_renderer('stops_form_block'),
      '#routes_form_block' => block_renderer('routes_form_block'),
      '#routes_media_block' => block_renderer('routes_media_block'),
      '#route_id' => $route_param,
      '#route_param' => $route_param,
      '#trip_planner_path' => $trip_planner_path,
      '#stops_path' => $routes_path,
      '#stops_path' => $stops_path,
      '#current_page' => $current_page,
      '#attached' => [
        'drupalSettings' => [
          'it_route_trip_tools' => [
            'routes_action_path' => $routes_path,
            'stops_action_path' => $stops_path
          ]
        ]   
      ]
    ];
  }

}