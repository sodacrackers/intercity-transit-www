<?php

namespace Drupal\menu_block_title;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Provides methods for preRender hook.
 */
class MenuBlockTitle implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRender'];
  }

  /**
   * The preRender callback which modifies the build output.
   */
  public static function preRender($build) {
    $items = [];

    if (!isset($build['#derivative_plugin_id'])) {
      return $build;
    }

    $menu_name = $build['#derivative_plugin_id'];

    $menu_tree = \Drupal::menuTree();
    // Build the typical default set of menu tree parameters.
    $params = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    // Load the tree based on this set of parameters.
    $tree = $menu_tree->load($menu_name, $params);
    // Build out the renderable array.
    $menu = $menu_tree->build($tree);
    // Start building the $items array to check for active menu.
    $items = array_merge($items, $menu['#items']);

    // Loop through each item checking for active menu trail.
    foreach ($items as $item) {
      if ($item['in_active_trail']) {
        // If this item is in the active menu trail, set the block label to the
        // menu title. Currently assuming there will only be one.
        $linked_title = [
          '#type' => 'link',
          '#url' => $item['url'],
          '#title' => $item['title'],
        ];
        $build['#configuration']['label'] = $linked_title;

        continue;
      }
    }

    return $build;
  }

}
