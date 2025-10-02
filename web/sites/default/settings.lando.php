<?php

/**
 * @file
 * Local development override configuration feature.
 *
 * IMPORTANT:
 * This file is for local development only. It is not suitable for
 * production environments, as it contains settings that are insecure.
 * DO NOT copy this file to a production environment!
 *
 * You can copy this file to settings.local.php and customize it further
 * to suit your local development environment needs.
 *
 * If you copy this file to settings.local.php, you should also set
 * $settings['container_yamls'][] = __DIR__ . '/development.services.yml';
 * in settings.php to enable the use of the development services.
 *
 * See https://www.drupal.org/docs/develop/local-server-setup/local-development-configuration
 * for more information.
 */

$databases['default']['default'] = [
  'database' => 'drupal10',
  'username' => 'drupal10',
  'password' => 'drupal10',
  'host' => 'database',
  'driver' => 'mysql',
  'port' => 3306,
  'prefix' => '',
];


$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';

$settings['hash_salt'] = 'c697eca3026cc30dbd0a938c195467e53bbe9780ec2c2d62474154f39b778380';

$config['system.logging']['error_level'] = 'verbose';
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
# $settings['cache']['bins']['render'] = 'cache.backend.null';
# $settings['cache']['bins']['discovery_migration'] = 'cache.backend.memory';
# $settings['cache']['bins']['page'] = 'cache.backend.null';
# $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
# $settings['extension_discovery_scan_tests'] = TRUE;
$settings['rebuild_access'] = TRUE;
$settings['skip_permissions_hardening'] = TRUE;
# $settings['config_exclude_modules'] = ['devel', 'stage_file_proxy'];
