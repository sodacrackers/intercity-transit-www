<?php

/**
 * @file
 * Post update functions for Inline Styled Images module.
 */

use Drupal\filter\Entity\FilterFormat;

/**
 * Update structure of settings inline_responsive_images filters.
 */
function inline_responsive_images_post_update_change_settings_structure() {
  /** @var \Drupal\filter\Entity\FilterFormat $filter_format */
  foreach (FilterFormat::loadMultiple() as $filter_format) {
    $filters = [
      'filter_imagestyle' => 'image_style_',
      'filter_responsive_image_style' => 'responsive_style_',
    ];

    foreach ($filters as $filter_name => $filter_prefix) {
      if ($filter = $filter_format->filters($filter_name)) {
        $filter_config = $filter->getConfiguration();
        // Check if new format already active.
        if (
          $filter_config['settings']
          &&
          !(
            array_key_exists('image_styles', $filter_config['settings'])
            && count($filter_config['settings']['image_styles']) > 0
          )
        ) {
          $new_config = [];
          foreach ($filter_config['settings'] as $style_name => $value) {
            // Only inlude prefixed values.
            $has_prefix = str_starts_with($style_name, $filter_prefix);
            if ($has_prefix) {
              $style_name = str_replace($filter_prefix, '', $style_name);
              $new_config[$style_name] = !empty($value) ? $style_name : 0;
            }
          }
          $filter_config['settings'] = [
            'image_styles' => $new_config,
          ];
          $filter_format->setFilterConfig($filter_name, $filter_config);
        }
      }
    }
    $filter_format->save();
  }
  return t('Filter format configs were updated.');
}
