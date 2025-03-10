<?php

namespace Drupal\twig_capture;

use Drupal\Core\Template\TwigEnvironment;
use Twig\Environment;
use Twig\Node\CheckToStringNode;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\AssignNameExpression;
use Twig\Node\Expression\ConditionalExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Expression\Test\DefinedTest;
use Twig\Node\IfNode;
use Twig\Node\Node;
use Twig\Node\SetNode;
use Twig\NodeVisitor\AbstractNodeVisitor;

/**
 * The node visitor class for the twig capture functionality.
 *
 * This class replaces {% if foo|render %} with
 * {% set foo_rendered = foo|render %}{% if foo_rendered %}
 * and consequent {{ foo }} with
 *{{ (foo_rendered is defined) ? foo_rendered : foo }}
 */
class TwigCaptureNodeVisitor extends AbstractNodeVisitor {

  /**
   * @var \Drupal\twig_capture\TwigCaptureCompiler
   */
  protected TwigCaptureCompiler $compiler;

  protected $seen = [];

  protected \SplObjectStorage $parents;

  protected array $names = [];

  public function __construct(TwigEnvironment $environment) {
    $this->compiler = new TwigCaptureCompiler($environment);
    $this->parents = new \SplObjectStorage();
  }

  /**
   * {@inheritdoc}
   */
  protected function doEnterNode(Node $node, Environment $env) {
    foreach ($node as $child) {
      $this->parents[$child] = $node;
    }
    if ($this->isFilter($node, 'render')) {
      for ($ancestor = $node; isset($this->parents[$ancestor]); $ancestor = $this->parents[$ancestor]) {
        // When hitting a list of nodes, it can either be a list of tags or
        // a list of tests for {% if %}. Either way, it's time to stop
        // processing. For example,
        // {% if something%}
        //   {{ foo | render }}
        // {% endif %}
        // The {% if %} is an ancestor of foo | render but it is not something
        // we want to replace.
        if ($this->isContainerNode($ancestor)) {
          $node = $this->handleIf($ancestor, $node);
          break;
        }
      }
    }
    if ($this->isFilter($node, 'escape') && $node->getNode('node') instanceof CheckToStringNode) {
      $this->handleAutoescape($node->getNode('node'));
    }
    return $node;
  }

  public function doLeaveNode(Node $node, Environment $env) {
    return $node;
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority() {
    return 0;
  }

  /**
   * Is this the filter we want?
   *
   * Drupal has autoecsape on which means {{ foo }} is converted to
   * {{ foo|escape }} so both {% if foo|render %} and {{ foo }} are filter
   * expressions.
   *
   * @param \Twig\Node\Node $node
   *   The potential filter node.
   * @param $filterName
   *   The name of the filter like 'render' or 'escape'.
   *
   * @return bool
   *   TRUE if it is indeed the right filter.
   */
  protected function isFilter(Node $node, $filterName) {
    return $node instanceof FilterExpression && $node->getNode('filter')->getAttribute('value') === $filterName;
  }

  /**
   * @param \Twig\Node\Node $tests
   * @param \Twig\Node\Expression\FilterExpression $filterExpression
   *
   * @return Node
   */
  protected function handleIf(Node $tests, FilterExpression $filterExpression): Node {
    $ifNode = $this->parents[$tests] ?? NULL;
    if (!$ifNode instanceof IfNode) {
      return $filterExpression;
    }
    /** @var \Twig\Node\Node $ifParent */
    $ifParent = $this->parents[$ifNode] ?? NULL;
    // Sanity check.
    if (!$ifParent || !$this->isContainerNode($ifParent)) {
      return $filterExpression;
    }
    $delta = 0;
    foreach ($ifParent as $child) {
      // Prepend the new assignement before the if.
      if ($child === $ifNode) {
        $filtered = $filterExpression->getNode('node');
        // Sanity check.
        if (!$filtered instanceof AbstractExpression) {
          return $filterExpression;
        }
        $lineno = $filtered->getTemplateLine();
        // It's very near impossible to get the original string for something
        // like content.somefield and so instead the PHP source code for the
        // expression is used. The Twig compiler adds debug information
        // including line number which would cause a mismatch so a custom
        // compiler is used which just skips all debug information as it is not
        // useful here anyways.
        $renderedName = $this->compiler->compile($filtered)->getSource();
        $renderedName = $this->removeLineNoFromAttrExpression($renderedName);
        $this->names[$renderedName] = TRUE;
        $ifParent->setNode($delta++, new SetNode(
          FALSE,
          new Node([new AssignNameExpression($renderedName, $lineno)]),
          new Node([$filterExpression]),
          $lineno
        ));
        $filterExpression = new NameExpression($renderedName, $lineno);
      }
      $ifParent->setNode($delta++, $child);
    }
    return $filterExpression;
  }

  protected function handleAutoescape(CheckToStringNode $node): void {
    $filtered = $node->getNode('expr');
    // Sanity check.
    if (!$filtered instanceof AbstractExpression) {
      return;
    }
    $lineno = $filtered->getTemplateLine();
    $renderedName = $this->compiler->compile($filtered)->getSource();
    $renderedName = $this->removeLineNoFromAttrExpression($renderedName);
    if (isset($this->names[$renderedName])) {
      // Convert {{ x }} into {{ rendered_x is defined) ? rendered_x : x }}
      $node->setNode('expr', new ConditionalExpression(
        new DefinedTest(new NameExpression($renderedName, $lineno), '', NULL, $lineno),
        // DefinedTest changes the first node so the same object can't be used
        // twice.
        new NameExpression($renderedName, $lineno),
        $filtered,
        $lineno
      ));
    }
  }

  protected function isContainerNode(Node $node) {
    $delta = 0;
    foreach ($node as $key => $child) {
      if ($key !== $delta) {
        return FALSE;
      }
      $delta++;
    }
    return TRUE;
  }

  /**
   * The line number from twig_get_attribute calls cause a mismatch, remove it.
   */
  protected function removeLineNoFromAttrExpression($renderedName) {
    return preg_replace('/^(twig_get_attribute.*), \d+\)$/', '$1)', $renderedName);
  }

}
