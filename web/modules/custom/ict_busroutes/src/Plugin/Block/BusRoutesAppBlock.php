<?php

namespace Drupal\ict_busroutes\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Bus Routes App block.
 *
 * @Block(
 *   id = "bus_routes_app_block",
 *   admin_label = @Translation("Bus Routes App"),
 *   category = @Translation("Custom")
 * )
 */
class BusRoutesAppBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->getApplication(),
      '#attached' => [
        'library' => [
          'ict_busroutes/busroutes_app',
        ],
      ],
    ];
  }

  /**
   * Get the application HTML content.
   *
   * @return string
   *   The HTML content for the application.
   */
  private function getApplication() {
    $module_path = \Drupal::service('extension.list.module')->getPath('ict_busroutes');
    $file_path = DRUPAL_ROOT . '/' . $module_path . '/app/stop_times.include.html';

    if (file_exists($file_path)) {
      return file_get_contents($file_path);
    }

    // Fallback content if file doesn't exist.
    return '<div id="ict-busroutes-app" class="ict-busroutes-container">
  <!-- React application will be mounted here -->
  <div id="loading" style="text-align: center; padding: 50px;">
    <p>Loading bus routes...</p>
  </div>
</div>';
  }

}
