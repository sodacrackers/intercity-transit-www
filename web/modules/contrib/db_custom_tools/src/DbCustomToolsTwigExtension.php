<?php

namespace Drupal\db_custom_tools;

use Drupal\Core\Render\Markup;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DbCustomToolsTwigExtension extends AbstractExtension {
  /**
   * Here is where we declare our new filter.
   * @return array
   */
  public function getFilters() {
    $filters = [
      new TwigFilter('basedomain',['Drupal\db_custom_tools\DbCustomToolsTwigExtension', 'baseDomainFilter']),
      new TwigFilter('dashify',['Drupal\db_custom_tools\DbCustomToolsTwigExtension', 'dashifyFilter'])
    ];
    return $filters;
  }

  /**
   * This is the same name we used on the services.yml file
   * @return string
   */
  public function getName() {
    return "db_custom_tools.twig_extension";
  }

  /**
   * @param $string
   * @return float
   */
  public static function baseDomainFilter($string) {
    if($string) { // we check if the $string is an instance of Markup Object.
      $urlParse = parse_url($string);
      $domainValue = $urlParse['host'];
      return $domainValue;
    } else {
      return $string;
    }
  }
  public static function dashifyFilter($string) {
    if($string) { // we check if the $string is an instance of Markup Object.
      $dashed = str_replace('_', '-', $string);
      return $dashed;
    } else {
      return $string;
    }
  }
}