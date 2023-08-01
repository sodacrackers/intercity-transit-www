<?php

namespace Drupal\Tests\twig_capture\Kernel;

use Drupal\Core\Template\TwigEnvironment;
use Drupal\KernelTests\KernelTestBase;
use Twig\Source;
use Twig\TokenStream;

use function file_get_contents;

/**
 * Tests the module works properly.
 *
 * @group twig_capture
 */
class TwigCaptureTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['twig_capture'];

  /**
   * @var array|\Drupal\Core\Template\TwigEnvironment
   */
  protected TwigEnvironment $twig;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->twig = $this->container->get('twig');
  }

  public function testConversion() {
    $path = __DIR__ . '/../../fixtures/';
    $name = 'test1';
    $content = file_get_contents($path . $name);
    $stream = $this->twig->tokenize(new Source($content, $name, $path));
    $this->assertInstanceOf(TokenStream::class, $stream);
  }

}
