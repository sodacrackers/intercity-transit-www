<?php

namespace Drupal\ict_routes_react_app\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a reactapp block.
 *
 * @Block(
 *   id = "ict_routes_react_app_block",
 *   admin_label = @Translation("Real time bus data react app"),
 *   category = @Translation("Custom")
 * )
 */
class ReactAppBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The request stack.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Constructs a new ReactAppBlock object.
   *
   * @param array $configuration
   *   The block configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $current_path
   *   The request stack.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentPathStack $current_path) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentPath = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.current')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $route_id_path = $this->currentPath->getPath();
    $route_id_parts = explode('/', $route_id_path);
    $route_id = $route_id_parts[3] ?? NULL;
    $service_type = date('N', strtotime('now')) >= 6 ? '1' : '2';
    if ($route_id) {
      $url = Url::fromRoute('ict_gtfs.json_endpoint', [], [
        'query' => [
          'route_id' => $route_id,
          'service_type' => $service_type,
        ],
      ]);
      $build['ict_routes_react_app_block'] = [
        '#markup' => '<div id="ict-routes-react-app" data-api-url="' . $url->toString() . '"></div>',
        '#attached' => [
          'library' => 'ict_routes_react_app/ict_routes_react_app'
        ],
      ];
    }
    return $build;
  }

}
