<?php

namespace Drupal\menu_link\Render\Element;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Provides a pre-render callback for menu field details.
 */
class MenuDetails implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRender'];
  }

  /**
   * Pre-render callback: Builds a renderable array for the menu link widget.
   *
   * @param array $element
   *   A renderable array.
   *
   * @return array
   *   A renderable array.
   */
  public static function preRender($element) {
    $element['menu']['enabled'] = $element['enabled'];
    $element['menu']['title'] = $element['title'];
    $element['menu']['description'] = $element['description'];
    $element['menu']['menu_parent'] = $element['menu_parent'];
    $element['menu']['weight'] = $element['weight'];

    // Hide the elements when enabled is disabled.
    foreach (['title', 'description', 'menu_parent', 'weight'] as $form_element) {
      $element['menu'][$form_element]['#states'] = [
        'invisible' => [
          'input[name="' . $element['menu']['enabled']['#name'] . '"]' => ['checked' => FALSE],
        ],
      ];
    }

    unset($element['enabled'], $element['title'], $element['description'], $element['menu_parent'], $element['weight']);

    return $element;
  }

}
