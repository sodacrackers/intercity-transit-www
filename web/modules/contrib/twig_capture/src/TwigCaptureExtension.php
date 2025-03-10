<?php

namespace Drupal\twig_capture;

use Drupal\Core\Template\TwigEnvironment;
use Twig\Extension\AbstractExtension;

class TwigCaptureExtension extends AbstractExtension {

  /**
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected TwigEnvironment $environment;

  public function __construct(TwigEnvironment $environment) {
    $this->environment = $environment;
  }

  public function getNodeVisitors() {
    return [
      new TwigCaptureNodeVisitor($this->environment),
    ];
  }

}

