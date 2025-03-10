<?php

namespace Drupal\twig_capture;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Omit the debug info: it contains the line number and causes a mismatch.
 */
class TwigCaptureCompiler extends Compiler {
  public function addDebugInfo(Node $node) {
  }
}
