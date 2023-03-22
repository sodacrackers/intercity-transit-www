<?php

namespace Drupal\ict_gtfs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ict_gtfs\Gtfs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * BusData constructor.
     *
     * @param \Drupal\Core\Form\FormBuilder $form_builder
     *   The form builder.
     */
    public function __construct(Gtfs $gtfs) {
        $this->gtfs = $gtfs;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('ict.gtfs')
        );
    }

    public function json() {
        $payload = [];
        foreach ($this->gtfs->getAllowedTypes() as $allowed_type) {
            $payload[$allowed_type] = $this->gtfs->getArray($allowed_type);
        }
        return new JsonResponse($payload);
    }
}