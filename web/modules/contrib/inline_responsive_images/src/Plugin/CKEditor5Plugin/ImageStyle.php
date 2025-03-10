<?php

declare(strict_types=1);

namespace Drupal\inline_responsive_images\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\editor\EditorInterface;
use Drupal\image\Entity\ImageStyle as Style;

/**
 * This class transmits the enabled styles to the javascript plugin.
 */
class ImageStyle extends CKEditor5PluginDefault {

  use CKEditor5PluginConfigurableTrait;

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(
    array $static_plugin_config,
    EditorInterface $editor,
  ): array {
    $format = $editor->getFilterFormat();
    /** @var \Drupal\filter\Plugin\FilterInterface $filter */
    $filter = $format->filters('filter_imagestyle');
    $filter_config = $filter->getConfiguration();
    $image_styles = array_keys(array_filter($filter_config['settings']['image_styles']));
    $enabledStyles = [];

    foreach ($image_styles as $image_style_id) {
      if ($style = Style::load($image_style_id)) {
        $enabledStyles[$image_style_id] = $style->label();
      }
    }

    $parent_config = parent::getDynamicPluginConfig($static_plugin_config, $editor);
    return array_merge_recursive($parent_config,
      [
        'DrupalImageStyle' =>
          [
            'enabledStyles' => $enabledStyles,
          ],
      ]);
  }

}
