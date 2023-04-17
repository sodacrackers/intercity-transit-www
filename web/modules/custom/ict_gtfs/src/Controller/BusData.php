<?php

namespace Drupal\ict_gtfs\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\ict_gtfs\Gtfs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides route responses for the Example module.
 */
class BusData extends ControllerBase {

  /**
   * The service to pull remote data from.
   *
   * @var Gtfs
   */
  private $gtfs;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private RendererInterface $renderer;

  /**
   * BusData constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $gtfs
   *   The gtfs service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(Gtfs $gtfs, RendererInterface $renderer) {
    $this->gtfs = $gtfs;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ict.gtfs'),
      $container->get('renderer')
    );
  }

  public function json(Request $request) {
    $routeId = $request->query->get('route_id');
    $service_type = $request->query->get('service_type') ?: 1;
    if ($routeId) {
      $route_data = it_route_trip_tools_get_route_table_map_data($routeId, $service_type);
      $trip_updates = $this->gtfs->getArray('TripUpdate')['entity'];
      foreach ($route_data['stop_markers'] as &$direction) {
        foreach ($direction as $stop_id => &$stop_data) {
          $stop_data['real_time'] = $this->getRealTimeDataByStopId($stop_id, $trip_updates);
        }
      }
      //      foreach ($this->gtfs->getAllowedTypes() as $allowed_type) {
      //        $payload[$allowed_type] = $this->gtfs->getArray($allowed_type);
      //      }
      $context = new RenderContext();
      /** @var \Drupal\Core\Cache\CacheableJsonResponse $response */
      $response = $this->renderer->executeInRenderContext($context, function () use ($route_data) {
        $response = CacheableJsonResponse::create($route_data);
        $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
          '#cache' => [
            'max-age' => 30,
          ],
        ]));
        return $response;
      });
      return $response;
    }

  }

  private function getRealTimeDataByStopId(int|string $stop_id, mixed $trip_updates) {
    foreach ($trip_updates as $trip_update) {
      foreach ($trip_update['tripUpdate']['stopTimeUpdate'] as $stopUpdate) {
        if ($stopUpdate['stopId'] == $stop_id) {
          return $stopUpdate;
        }
      }
    }
  }

}