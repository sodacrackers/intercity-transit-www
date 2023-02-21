<?php
namespace Drupal\it_route_trip_tools\Controller;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class AdminFormPage extends ControllerBase {

  public function BuildTitle() {
    return 'IT Route and Trip Tool Settings';
  }
  public function BuildPage() {
    $admin_form = \Drupal::formBuilder()->getForm('Drupal\it_route_trip_tools\Form\AdminForm');
    $build_form = [
      '#theme' => 'admin_form',
      '#form' => $admin_form,
    ];
    return [
      '#theme' => 'admin_form_page',
      '#admin_form' => $admin_form,
    ];
  }
}