<?php

namespace Drupal\it_route_trip_tools\Controller;

use Drupal\fullcalendar_view\Controller\CalendarEventController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Calendar Event Controller.
 */
class CustomCalendarEventController extends CalendarEventController {

  /**
   * New event handler function.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Http Request object.
   *
   * @return array
   *   A event entity form render array
   */
  public function addEvent(Request $request) {
    $entity_type_id = $request->get('entity', '');
    $bundle = $request->get('bundle', '');

    if (!empty($bundle) && !empty($entity_type_id)) {
      $access_control_handler = $this->entityTypeManager()->getAccessControlHandler($entity_type_id);
      // Check the user permission.
      if ($access_control_handler->createAccess($bundle)) {
        $data = [
          'type' => $bundle,
        ];
        // Create a new event entity for this form.
        $entity = $this->entityTypeManager()
          ->getStorage($entity_type_id)
          ->create($data);

        if (!empty($entity)) {
          // Add form.
          $form = $this->entityFormBuilder()->getForm($entity);
          $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['uuid' => '10c284fb-434e-4379-bd72-3448652c0813']);
          $term = reset($terms);
          $form['field_event_type']['widget']['#value'] = $term->id();
          $form['field_event_type']['#access'] = FALSE;
          // Hide preview button.
          if (isset($form['actions']['preview'])) {
            $form['actions']['preview']['#access'] = FALSE;
          }
          // Move the Save button to the bottom of this form.
          $form['actions']['#weight'] = 10000;

          return $form;
        }
      }
    }
    // Return access denied for users don't have the permission.
    throw new AccessDeniedHttpException();
  }

}
