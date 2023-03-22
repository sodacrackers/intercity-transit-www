<?php

namespace Drupal\ict_routes_react_app\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a reactapp block.
 *
 * @Block(
 *   id = "ict_routes_react_app_block",
 *   admin_label = @Translation("Real time bus data react app"),
 *   category = @Translation("Custom")
 * )
 */
class ReactAppBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $url = Url::fromRoute('ict_gtfs.json_endpoint');
    $build['ict_routes_react_app_block'] = [
      '#markup' => '<div id="ict-routes-react-app" data-api-url="' . $url->toString() . '"></div>',
      '#attached' => [
        'library' => 'ict_routes_react_app/ict_routes_react_app'
      ],
    ];
    return $build;
  }

}
