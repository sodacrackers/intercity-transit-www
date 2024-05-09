<?php

namespace Drupal\ict_gtfs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Configure GTFS Import settings for this site.
 */
class StaticDataSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ict_gtfs_static_data_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ict_gtfs.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('ict_gtfs.settings');
    $items = $form_state->getValue('items', $config->get('items') ?: [
      [
        'detail' => ['file' => NULL, 'date_from' => NULL, 'date_to' => NULL],
        'weight' => 1,
      ],
    ]);
    $form['items'] = [
      '#prefix' => '<div id="items-add-more">',
      '#suffix' => '</div>',
      '#type' => 'table',
      '#header' => [
        $this->t('Items'),
        $this->t('Weight'),
      ],
    ];
    $form['items']['#tabledrag'][] = [
      'action' => 'order',
      'relationship' => 'sibling',
      'group' => 'lb-items-weight',
    ];

    foreach ($items as $ix => $item) {
      $detail = $item['detail'];
      $form['items'][$ix] = [
        '#weight' => $item['weight'] ?? 50,
        'detail' => [
          '#type' => 'container',
          'file' => [
            '#type' => 'managed_file',
            '#title' => $this->t('Zip file'),
            '#default_value' => $detail['file'],
            '#prefix' => '<div class="inline-element-large">',
            '#suffix' => '</div>',
            '#upload_location' => 'private://avail/',
            '#upload_validators'  => ['file_validate_extensions' => ['zip']],
          ],
          'date_from' => [
            '#type' => 'date',
            '#title' => $this->t('Valid from'),
            '#default_value' => $detail['date_from'],
            '#prefix' => '<div class="inline-element-large">',
            '#suffix' => '</div>',
          ],
          'date_to' => [
            '#type' => 'date',
            '#title' => $this->t('Valid to'),
            '#default_value' => $detail['date_to'],
            '#prefix' => '<div class="inline-element-large">',
            '#suffix' => '</div>',
          ],
          'remove' => [
            '#type' => 'submit',
            '#value' => 'X',
            '#submit' => ['\Drupal\ict_gtfs\Form\StaticDataSettingsForm::removeItemSubmit'],
            '#ajax' => [
              'callback' => '\Drupal\ict_gtfs\Form\StaticDataSettingsForm::removeItemSubmit',
              'wrapper' => 'items-add-more',
              'effect' => 'fade',
              'method' => 'replaceWith',
            ],
            '#prefix' => '<div class="inline-element-small">',
            '#suffix' => '</div>',
          ],
        ],
        'weight' => [
          '#type' => 'weight',
          '#title' => $this->t('Weight for item %ix', ['%ix' => $ix + 1]),
          '#title_display' => 'invisible',
          '#delta' => 50,
          '#default_value' => $item['weight'] ?? 50,
          '#attributes' => [
            'class' => ['lb-items-weight'],
          ],
        ],
        '#attributes' => [
          'class' => ['draggable', 'js-form-wrapper'],
        ],
      ];
    }

    uasort($form['items'], [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightProperty',
    ]);

    $form['add_another'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another item'),
      '#submit' => ['\Drupal\ict_gtfs\Form\StaticDataSettingsForm::addMoreSubmit'],
      '#ajax' => [
        'callback' => '\Drupal\ict_gtfs\Form\StaticDataSettingsForm::addMoreSubmit',
        'wrapper' => 'items-add-more',
        'effect' => 'fade',
        'method' => 'replaceWith',
      ],
    ];

    $routes = it_route_trip_tools_build_routes_options();
    if ($routes) {
      $form['disable_routes'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Disable routes'),
        '#options' => $routes,
        '#default_value' => $config->get('disable_routes') ?: [],
      ];
    }

    $form['#attached']['library'][] = 'ict_gtfs/ict_custom_backend';
    return $form;
  }

  /**
   * Submission handler for the "Add another item" button.
   */
  public static function addMoreSubmit(array $form, FormStateInterface $form_state) {
    $items = $form_state->getValue('items', []);

    $count = count($items) + 1;

    $items[] = [
      'detail' => ['text' => 'Item ' . $count, 'anchor' => 'item-' . $count],
      'weight' => $count,
    ];

    $form_state->setValue('items', $items);
    $form_state->setRebuild();
    return $form['items'];
  }

  /**
   * Submission handler for the "Remove item" button.
   */
  public static function removeItemSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $items = $form_state->getValue('items', []);
    $parents = $button['#parents'];
    // Remove the button.
    array_pop($parents);
    // Remove the detail container.
    array_pop($parents);
    unset($items[end($parents)]);
    $form_state->setValue('items', $items);
    $form_state->setRebuild();
    return $form['items'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $items = $form_state->getValue('items') ?: [];
    $disable_routes = $form_state->getValue('disable_routes') ?: [];
    $file_storage = \Drupal::entityTypeManager()->getStorage('file');
    foreach ($items as $item) {
      $file = !empty($item['detail']['file'][0]) ? $file_storage->load($item['detail']['file'][0]) : NULL;
      if ($file instanceof FileInterface) {
        $file->setPermanent();
        $file->save();
        $zip = new \ZipArchive;
        $res = $zip->open(\Drupal::service('file_system')->realpath($file->getFileUri()));
        if ($res === TRUE) {
          $folder_name = $item['detail']['date_from'] . '-' . $item['detail']['date_to'];
          $zip->extractTo('private://avail/' . $folder_name .'/');
          $zip->close();
        }
      }
    }
    $this->config('ict_gtfs.settings')
      ->set('items', $items)
      ->set('disable_routes', $disable_routes)
      ->save();
    drupal_flush_all_caches();
    parent::submitForm($form, $form_state);
  }


}
