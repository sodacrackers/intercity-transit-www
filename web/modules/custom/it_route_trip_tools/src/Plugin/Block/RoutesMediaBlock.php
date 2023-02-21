<?php

namespace Drupal\it_route_trip_tools\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 *
 * @Block(
 *   id = "routes_media_block",
 *   admin_label = @Translation("Routes Media Block"),
 *   category = @Translation("Routes Media Block"),
 * )
 */

class RoutesMediaBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */

  public function build($routeId = NULL) {
    $route_id = \Drupal::routeMatch()->getParameter('routeId');
    $route_table_url = '';
    $route_map_url = '';
    if (($route_id != 'all') && ($route_id != NULL)):
        $route_param = $route_id;
        $query = \Drupal::entityQuery('media'); 
        $query->condition('bundle', 'route_pdfs');
        $query->condition('name', $route_param, '=');
        $media_ids = $query->execute();
        $media = \Drupal::entityTypeManager()->getStorage('media')->loadMultiple($media_ids);
        foreach($media as $item):
          if ($item->get('field_document')->entity->getFileUri() !== NULL ):
            $route_table_url = file_create_url($item->get('field_document')->entity->getFileUri());
          else:
            $route_table_url = '';
          endif;
          if ($item->get('field_map')->entity->getFileUri() !== NULL ):
            $route_map_url = file_create_url($item->get('field_map')->entity->getFileUri());
          else:
            $route_map_url = '';
          endif;
        endforeach;
    else:
      $route_param = '';
    endif;
    return [
      '#theme' => 'routes_media_block',
      '#route_id' => $route_param,
      '#route_table_url' => $route_table_url,
      '#route_map_url' => $route_map_url
    ];
  }

}