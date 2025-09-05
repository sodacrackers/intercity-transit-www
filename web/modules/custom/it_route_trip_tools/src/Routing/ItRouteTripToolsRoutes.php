<?php
    namespace Drupal\it_route_trip_tools\Routing;

    use Symfony\Component\Routing\Route;

    class ItRouteTripToolsRoutes {
        public function RoutesPageRouting() {
            $route_routing = array();
            $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
            $routes_path = $config->get('route_page_path') . '/{routeId}';
            $route_routing['it_route_trip_tools.routes'] = new Route(
                $routes_path,
                array(
                    '_controller' => '\Drupal\it_route_trip_tools\Controller\RoutesPage::BuildPage',
                    '_title_callback' => '\Drupal\it_route_trip_tools\Controller\RoutesPage::BuildTitle',
                    'routeId' => 'all'
              ),
                array(
                    '_permission'  => 'access content',
              )
            );
            return $route_routing;
        }
        public function TripPlannerPageRouting() {
            $trip_planner_routing = array();
            $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
            $trip_planner_path = $config->get('trip_planner_page_path');
            $trip_planner_routing['it_route_trip_tools.trip_planner'] = new Route(
                $trip_planner_path,
                array(
                    '_controller' => '\Drupal\it_route_trip_tools\Controller\TripPlannerPage::BuildPage',
                    '_title_callback' => '\Drupal\it_route_trip_tools\Controller\TripPlannerPage::BuildTitle',
                ),
                array(
                    '_permission'  => 'access content',
                )
            );
            return $trip_planner_routing;
        }
        public function StopsPageRouting() {
            $stops_routing = array();
            $config = \Drupal::service('config.factory')->getEditable('it_route_trip_tools.settings');
            $stops_path = $config->get('stops_page_path') . '/{stopId}';
            //$stops_path = '/route-trip-placeholder/stops';
            $stops_routing['it_route_trip_tools.stops'] = new Route(
                $stops_path,
                array(
                    '_controller' => '\Drupal\it_route_trip_tools\Controller\StopsPage::BuildPage',
                    '_title_callback' => '\Drupal\it_route_trip_tools\Controller\StopsPage::BuildTitle',
                    'stopId' => 'all'
                ),
                array(
                    '_permission'  => 'access content',
                )
            );
            return $stops_routing;
        }
    }