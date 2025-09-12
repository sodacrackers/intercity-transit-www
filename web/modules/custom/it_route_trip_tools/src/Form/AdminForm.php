<?php

namespace Drupal\it_route_trip_tools\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;


class AdminForm extends ConfigFormBase {

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
    return 'admin_form';
  }

  protected function getEditableConfigNames() {
    return [
      'it_route_trip_tools.settings',
    ];
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
    $config = $this->config('it_route_trip_tools.settings');
    if ($config->get('route_page_parent') != ''):
      $route_page_node = \Drupal\node\Entity\Node::load($config->get('route_page_parent'));
    else:
      $route_page_node = '';
    endif;
    if ($config->get('stops_page_parent')):
      $stops_page_node = \Drupal\node\Entity\Node::load($config->get('stops_page_parent'));
    else:
      $stops_page_node = '';
    endif;
    if ($config->get('trip_planner_page_parent') != ''):
      $trip_planner_page_node = \Drupal\node\Entity\Node::load($config->get('trip_planner_page_parent'));
    else:
      $trip_planner_page_node = '';
    endif;
    $route_page_title_desc = 'The clean title for this page is <strong>' . $config->get('route_page_title_clean') . '</strong>';
    $stops_page_title_desc = 'The clean title for this page is <strong>' . $config->get('stops_page_title_clean') . '</strong>';
    $trip_planner_page_title_desc = 'The clean title for this page is <strong>' . $config->get('trip_planner_page_title_clean') . '</strong>';
    $form['it_route_trip_tools_route_page_title'] = [
      '#type' => 'textfield',
      '#description' => $route_page_title_desc,
      '#default_value' => $config->get('route_page_title'),
      '#title' => $this->t('Route Page Title'),
    ];
    $form['it_route_trip_tools_route_page_parent'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#selection_settings' => array(
        'target_bundles' => 'page',
      ),
      '#default_value' => $route_page_node,
      '#description' => 'The full path for the Route page is: <strong><a href="' . $config->get('route_page_path') . '">' . $config->get('route_page_path') . '</a></strong>',
      '#title' => $this->t('Route Page Parent'),
    ];
    $form['it_route_trip_tools_stops_page_title'] = [
      '#type' => 'textfield',
      '#description' => $stops_page_title_desc,
      '#default_value' => $config->get('stops_page_title'),
      '#title' => $this->t('Stops Page Title'),
    ];
    $form['it_route_trip_tools_stops_page_parent'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#selection_settings' => array(
        'target_bundles' => 'page',
      ),
      '#default_value' => $stops_page_node,
      '#description' => 'The full path for the Stops page is: <strong><a href="' . $config->get('stops_page_path') . '">' . $config->get('stops_page_path') . '</a></strong>',
      '#title' => $this->t('Stops Page Parent'),
    ];
    $form['it_route_trip_tools_trip_planner_page_title'] = [
      '#type' => 'textfield',
      '#description' => $trip_planner_page_title_desc,
      '#default_value' => $config->get('trip_planner_page_title'),
      '#title' => $this->t('Trip Planner Page Title'),
    ];
    $form['it_route_trip_tools_trip_planner_page_parent'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#selection_settings' => array(
        'target_bundles' => 'page',
      ),
      '#default_value' => $trip_planner_page_node,
      '#description' => 'The full path for the trip planner page is: <strong><a href="' . $config->get('trip_planner_page_path') . '">' . $config->get('trip_planner_page_path') . '</a></strong>',
      '#title' => $this->t('Trip Planner Page Parent'),
    ];
    // Force fetch routes without cache to ensure we get data
    $routes = it_route_trip_tools_pics_get_routes_raw(FALSE);
    
    // Add a section title for routes management
    $form['routes_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Routes Management'),
      '#open' => TRUE,
      '#description' => $this->t('Drag routes to reorder them. Uncheck to disable routes from display.'),
      '#tree' => TRUE,
    ];
    
    if ($routes && !empty($routes)) {
      // Get saved route order and disabled routes
      $route_order = $config->get('route_order') ?: [];
      $disabled_routes_config = $config->get('disable_routes') ?: [];
      
      // Handle both array keys and array values for disabled routes
      $disabled_routes = [];
      if (is_array($disabled_routes_config)) {
        // If it's an associative array (from old checkbox format), get the keys
        if (array_values($disabled_routes_config) !== $disabled_routes_config) {
          $disabled_routes = array_keys($disabled_routes_config);
        } else {
          // If it's a numeric array, use as is
          $disabled_routes = $disabled_routes_config;
        }
      }
      
      // Sort routes based on saved order
      $sorted_routes = [];
      $weight = 0;
      
      // First, add routes in saved order
      foreach ($route_order as $route_id => $saved_weight) {
        if (isset($routes[$route_id])) {
          $sorted_routes[$route_id] = $routes[$route_id];
          $sorted_routes[$route_id]['weight'] = $weight++;
        }
      }
      
      // Then, add any new routes that aren't in the saved order
      foreach ($routes as $route_id => $route) {
        if (!isset($sorted_routes[$route_id])) {
          $sorted_routes[$route_id] = $route;
          $sorted_routes[$route_id]['weight'] = $weight++;
        }
      }
      
      // Build the draggable table
      $form['routes_section']['routes_table'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Route'),
          $this->t('Enabled'),
          $this->t('Weight'),
        ],
        '#empty' => $this->t('No routes available.'),
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'route-order-weight',
          ],
        ],
        '#tree' => TRUE,
        '#attributes' => [
          'id' => 'routes-order-table',
        ],
      ];
      
      foreach ($sorted_routes as $route_id => $route) {
        $form['routes_section']['routes_table'][$route_id] = [
          '#attributes' => [
            'class' => ['draggable'],
          ],
          '#weight' => $route['weight'],
          'route' => [
            '#plain_text' => $route['route_short_name'] . ' - ' . $route['route_long_name'],
          ],
          'enabled' => [
            '#type' => 'checkbox',
            '#default_value' => !in_array($route_id, $disabled_routes),
          ],
          'weight' => [
            '#type' => 'weight',
            '#title' => $this->t('Weight for @title', ['@title' => $route['route_short_name']]),
            '#title_display' => 'invisible',
            '#default_value' => $route['weight'],
            '#attributes' => [
              'class' => ['route-order-weight'],
            ],
          ],
        ];
      }
      
      // Add a reset button
      $form['routes_section']['reset_order'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset to Default Order'),
        '#submit' => ['::resetOrder'],
        '#limit_validation_errors' => [],
      ];
    }
    $form['actions'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $route_title = $form_state->getValue('it_route_trip_tools_route_page_title');
    $route_parent = $form_state->getValue('it_route_trip_tools_route_page_parent');
    $trip_title = $form_state->getValue('it_route_trip_tools_trip_planner_page_title');
    $trip_parent = $form_state->getValue('it_route_trip_tools_trip_planner_page_parent');
    $stops_title = $form_state->getValue('it_route_trip_tools_stops_page_title');
    $stops_parent = $form_state->getValue('it_route_trip_tools_stops_page_parent');
    if (!$route_title) {
      // Set an error for the form element with a key of "title".
      $form_state->setErrorByName('it_route_trip_tools_route_page_title', $this->t('We really need a route page title.'));
    }
    if (!$route_parent){
      // Set an error for the form element with a key of "accept".
      $form_state->setErrorByName('it_route_trip_tools_route_page_parent', $this->t('Our route page needs a parent.'));
    }
    if (!$trip_title) {
      // Set an error for the form element with a key of "title".
      $form_state->setErrorByName('it_route_trip_tools_trip_planner_page_title', $this->t('We really need a trip planner page title.'));
    }
    if (!$trip_parent){
      // Set an error for the form element with a key of "accept".
      $form_state->setErrorByName('it_route_trip_tools_trip_planner_page_parent', $this->t('Our trip planner page needs a parent.'));
    }
    if (!$stops_title) {
      // Set an error for the form element with a key of "title".
      $form_state->setErrorByName('it_route_trip_tools_stops_page_title', $this->t('We really need a stops page title.'));
    }
    if (!$stops_parent){
      // Set an error for the form element with a key of "accept".
      $form_state->setErrorByName('it_route_trip_tools_stops_page_parent', $this->t('Our stops page needs a parent.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $messenger = \Drupal::messenger();
    $messenger->addMessage('Your settings have been saved. Yay.');

    $config = $this->config('it_route_trip_tools.settings');

    $route_page_title = $form_state->getValue('it_route_trip_tools_route_page_title');
    $route_page_parent = $form_state->getValue('it_route_trip_tools_route_page_parent');
    $route_node = \Drupal\node\Entity\Node::load($route_page_parent);
    $route_nid = $route_node->id();
    $route_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$route_nid);
    $route_page_title_clean = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($route_page_title));
    $route_page_title_clean =  preg_replace('!\s+!', '-', $route_page_title_clean);

    $stops_page_title = $form_state->getValue('it_route_trip_tools_stops_page_title');
    $stops_page_parent = $form_state->getValue('it_route_trip_tools_stops_page_parent');
    $stops_node = \Drupal\node\Entity\Node::load($stops_page_parent);
    $stops_nid = $stops_node->id();
    $stops_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$stops_nid);
    $stops_page_title_clean = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($stops_page_title));
    $stops_page_title_clean =  preg_replace('!\s+!', '-', $stops_page_title_clean);

    $trip_planner_page_title = $form_state->getValue('it_route_trip_tools_trip_planner_page_title');
    $trip_planner_page_parent = $form_state->getValue('it_route_trip_tools_trip_planner_page_parent');
    $trip_planner_node = \Drupal\node\Entity\Node::load($trip_planner_page_parent);
    $trip_planner_nid = $trip_planner_node->id();
    $trip_planner_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$trip_planner_nid);
    $trip_planner_page_title_clean = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($trip_planner_page_title));
    $trip_planner_page_title_clean =  preg_replace('!\s+!', '-', $trip_planner_page_title_clean);
    
    // Process routes table data
    $routes_section = $form_state->getValue('routes_section');
    $disable_routes = [];
    $route_order = [];
    
    // Debug: Check all form values
    $all_values = $form_state->getValues();
    $messenger->addMessage($this->t('All form value keys: @keys', ['@keys' => implode(', ', array_keys($all_values))]));
    
    // Debug: Check what we're getting
    if (!$routes_section) {
      $messenger->addMessage($this->t('No routes_section found in form values.'), 'warning');
    } else {
      $messenger->addMessage($this->t('routes_section keys: @keys', ['@keys' => implode(', ', array_keys($routes_section))]));
    }
    
    if ($routes_section && isset($routes_section['routes_table'])) {
      $routes_table = $routes_section['routes_table'];
      foreach ($routes_table as $route_id => $route_data) {
        // Skip non-route entries (like the reset button)
        if (!is_array($route_data) || !isset($route_data['enabled'])) {
          continue;
        }
        // Store disabled routes (where enabled is FALSE)
        if (!$route_data['enabled']) {
          $disable_routes[] = $route_id;
        }
        // Store route order
        $route_order[$route_id] = $route_data['weight'];
      }
    }
    
    // Debug: Add message to show what's being saved
    $messenger->addMessage($this->t('Saving @count routes with @disabled disabled routes.', [
      '@count' => count($route_order),
      '@disabled' => count($disable_routes),
    ]));

    $config->set('route_page_title', $route_page_title);
    $config->set('route_page_title_clean', $route_page_title_clean);
    $config->set('route_page_path', $route_alias . '/' . $route_page_title_clean);
    $config->set('route_page_parent', $route_page_parent);
    
    $config->set('stops_page_title', $stops_page_title);
    $config->set('stops_page_title_clean', $stops_page_title_clean);
    $config->set('stops_page_path', $stops_alias . '/' . $stops_page_title_clean);
    $config->set('stops_page_parent', $stops_page_parent);

    $config->set('trip_planner_page_title', $trip_planner_page_title);
    $config->set('trip_planner_page_title_clean', $trip_planner_page_title_clean);
    $config->set('trip_planner_page_path', $trip_planner_alias . '/' . $trip_planner_page_title_clean);
    $config->set('trip_planner_page_parent', $trip_planner_page_parent);

    $config->set('disable_routes', $disable_routes);
    $config->set('route_order', $route_order);

    $config->save();
  }

  /**
   * Reset route order to default.
   */
  public function resetOrder(array &$form, FormStateInterface $form_state) {
    $config = $this->config('it_route_trip_tools.settings');
    $config->set('route_order', []);
    $config->save();
    
    $messenger = \Drupal::messenger();
    $messenger->addMessage($this->t('Route order has been reset to default.'));
    
    $form_state->setRebuild();
  }

}
