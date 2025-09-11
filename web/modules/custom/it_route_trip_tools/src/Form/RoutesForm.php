<?php

namespace Drupal\it_route_trip_tools\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * HelloForm controller.
 */
class RoutesForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'routes_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //Grab the config data
    $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
    $page_path = $config->get('route_page_path');

    $routes_options = it_route_trip_tools_pics_get_routes();

    $routes_options_select = [];
    foreach ($routes_options as $key => $row) {
      $routes_options_select[$key] = $row['route_short_name'] . ' - ' . $row['route_long_name'];
    }

    $form['routes'] = [
      '#type' => 'select',
      '#title' => $this->t('Route'),
      '#required' => TRUE,
      '#options' => $routes_options_select,

    ];

    $current_path = \Drupal::service('path.current')->getPath();
    $cur_dir = dirname($current_path);
    $selected_route = basename($current_path);
    if ($cur_dir == $page_path) {
      $form['routes']['#default_value'] = $selected_route;
    }
    $form['actions']['find_route_button'] = [
      '#type' => 'submit',
      '#value' => $this->t('View Route'),
    ];
    return $form;
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}