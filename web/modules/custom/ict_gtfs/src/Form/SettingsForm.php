<?php

namespace Drupal\ict_gtfs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure GTFS Import settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ict_gtfs_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['ict_gtfs.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['base_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL for the GTFS server'),
      '#default_value' => $this->config('ict_gtfs.settings')->get('base_url'),
    ];
    $form['max_age'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum cache age'),
      '#min' => 0,
      '#default_value' => $this->config('ict_gtfs.settings')->get('max_age'),
    ];
    $form['allowed_types'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allowed GTFS types'),
      '#description' => $this->t('Enter one per line.'),
      '#default_value' => implode(PHP_EOL, $this->config('ict_gtfs.settings')->get('allowed_types')),
    ];
    $form['json_from_object'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Get JSON from GTFS object'),
      '#description' => $this->t('If selected, then fetch binary data from the GTFS server and convert to JSON. Otherwise, fetch JSON from the server using the debug parameter. This setting has no effect when using GTFS objects instead of PHP arrays.'),
      '#default_value' => $this->config('ict_gtfs.settings')->get('json_from_object') ?? TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $allowed_types = array_values(array_filter(array_map('trim',
      explode("\n", $form_state->getValue('allowed_types'))
    )));

    $this->config('ict_gtfs.settings')
      ->set('base_url', $form_state->getValue('base_url'))
      ->set('max_age', $form_state->getValue('max_age'))
      ->set('allowed_types', $allowed_types)
      ->set('json_from_object', $form_state->getValue('json_from_object'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
