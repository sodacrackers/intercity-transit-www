<?php

namespace Drupal\it_route_trip_tools\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
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
    $base_uri = $config->get('route_api_base');
    $route_options_request = $config->get('route_options_request');

    $routes_options = it_route_trip_tools_build_routes_options($route_options_request);
    $cur_dir = dirname($_SERVER['REQUEST_URI']);
    $selected_route = basename($_SERVER['REQUEST_URI']);
    if (\Drupal::request()->request->get('service_option')):
    $serv = \Drupal::request()->request->get('service_option');
    else:
      $serv = '1';
    endif;
    
    if ($cur_dir == $page_path):
      $form['routes'] = [
        '#type' => 'select',
        '#title' => $this->t('Route'),
        '#required' => TRUE,
        '#options' => $routes_options,
        '#default_value' => $selected_route
      ];
    else:
      $form['routes'] = [
        '#type' => 'select',
        '#title' => $this->t('Route'),
        '#required' => TRUE,
        '#options' => $routes_options
      ];
    endif;
    $form['service_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Service Option'),
      '#required' => TRUE,
      '#options' => [
        '1' => $this->t('Monday - Friday'),
        '2' => $this->t('Weekend'),
      ],
      '#default_value' => $serv
    ];
    $form['actions']['find_route_button'] = [
      '#type' => 'submit',
      '#value' => $this->t('View Route'),
    ];
    return $form;
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}