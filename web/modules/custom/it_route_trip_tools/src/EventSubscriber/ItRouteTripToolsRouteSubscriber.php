<?php

namespace Drupal\it_route_trip_tools\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Route subscriber.
 */
class ItRouteTripToolsRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($collection as $route_id => $route) {
      // Hide taxonomy pages from unprivileged users.
      if ($route_id === 'fullcalendar_view.add_event') {
        $defaults = $route->getDefaults();
        $defaults['_controller'] = '\Drupal\it_route_trip_tools\Controller\CustomCalendarEventController::addEvent';
        $route->setDefaults($defaults);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents():array   {
    $events = parent::getSubscribedEvents();

    // Use a lower priority than \Drupal\views\EventSubscriber\RouteSubscriber
    // to ensure the requirement will be added to its routes.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -300];

    return $events;
  }

}
