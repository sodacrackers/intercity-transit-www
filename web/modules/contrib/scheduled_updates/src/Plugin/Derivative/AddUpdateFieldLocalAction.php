<?php
/**
 * @file
 * Contains
 * \Drupal\scheduled_updates\Plugin\Derivative\AddUpdateFieldLocalAction.
 */


namespace Drupal\scheduled_updates\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AddUpdateFieldLocalAction.
 */
class AddUpdateFieldLocalAction extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a FieldUiLocalAction object.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider to load routes by name.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(RouteProviderInterface $route_provider, EntityTypeManagerInterface $entity_type_manager) {
    $this->routeProvider = $route_provider;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = array();

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type_id == 'scheduled_update') {
        continue;
      }
      if ($entity_type->get('field_ui_base_route')) {
        $this->derivatives["scheduled_update_field_add_$entity_type_id"] = array(
          'route_name' => "entity.scheduled_update_type.add_form.field.$entity_type_id",
          'title' => $this->t('Add Update field'),
          'appears_on' => array("entity.$entity_type_id.field_ui_fields"),
          'query' => ['entity_type_id' => $entity_type_id],
          'route_parameters' => ['entity_type_id' => $entity_type_id],
          'defaults' => ['mode' => 'embedded'],
        );
      }
    }

    foreach ($this->derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }

    return $this->derivatives;
  }

}

