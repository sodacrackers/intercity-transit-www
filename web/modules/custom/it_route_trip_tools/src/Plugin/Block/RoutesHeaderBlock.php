<?php

namespace Drupal\it_route_trip_tools\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a routes header block.
 *
 * @Block(
 *   id = "it_route_trip_tools_routes_header",
 *   admin_label = @Translation("Routes Header"),
 *   category = @Translation("New Routes")
 * )
 */
class RoutesHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

    /**
     * The config factory service.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * RoutesHeaderBlock constructor.
     *
     * @param array $configuration
     *   The block configuration.
     * @param string $plugin_id
     *   The plugin ID.
     * @param mixed $plugin_definition
     *   The plugin definition.
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The config factory service.
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->configFactory = $config_factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('config.factory')
        );
    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function defaultConfiguration() {
//        return [
//            'foo' => $this->t('Hello world!'),
//        ];
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function blockForm($form, FormStateInterface $form_state) {
//        $form['foo'] = [
//            '#type' => 'textarea',
//            '#title' => $this->t('Foo'),
//            '#default_value' => $this->configuration['foo'],
//        ];
//        return $form;
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function blockSubmit($form, FormStateInterface $form_state) {
//        $this->configuration['foo'] = $form_state->getValue('foo');
//    }
//
    /**
     * {@inheritdoc}
     */
    public function build() {
        $routes_options = it_route_trip_tools_pics_get_routes();
        $build['content'] = [
            '#theme' => 'routes_header_block',
            '#routes_options' => $routes_options,
        ];
        return $build;
    }

}
