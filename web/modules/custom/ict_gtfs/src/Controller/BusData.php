<?php

namespace Drupal\ict_gtfs\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\ict_gtfs\Gtfs;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    public function json() {
        $context = new RenderContext();
        $gtfs = $this->gtfs;
        /** @var \Drupal\Core\Cache\CacheableJsonResponse $response */
        $response = $this->renderer->executeInRenderContext($context, function () use ($gtfs) {
            $payload = [];
            foreach ($gtfs->getAllowedTypes() as $allowed_type) {
                $payload[$allowed_type] = $gtfs->getArray($allowed_type);
            }
            $response = CacheableJsonResponse::create($payload);
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