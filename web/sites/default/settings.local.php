<?php

/**
 * @file
 * Local development override configuration feature.
 *
 * To activate this feature, copy and rename it such that its path plus
 * filename is 'sites/default/settings.local.php'. Then, go to the bottom of
 * 'sites/default/settings.php' and uncomment the commented lines that mention
 * 'settings.local.php'.
 */

// Enable local development services.
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';

// Show all error messages, with backtrace information.
$config['system.logging']['error_level'] = 'verbose';

// Disable CSS and JS aggregation.
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

// Allow test modules and themes to be installed.
$settings['extension_discovery_scan_tests'] = TRUE;

// Enable access to rebuild.php.
$settings['rebuild_access'] = TRUE;

// Skip file system permissions hardening.
$settings['skip_permissions_hardening'] = TRUE;

// Exclude modules from configuration synchronization.
$settings['config_exclude_modules'] = ['devel', 'stage_file_proxy'];

/**
 * Lando Database Configuration.
 *
 * Lando creates database containers and exposes them via specific hostnames.
 * These settings work with the default Lando Drupal recipe.
 */
$databases['default']['default'] = [
  'database' => 'drupal10',
  'username' => 'drupal10',
  'password' => 'drupal10',
  'host' => 'database',
  'port' => '3306',
  'driver' => 'mysql',
];

/**
 * Disable all caching for development.
 */
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['discovery'] = 'cache.backend.null';

// Trusted host configuration.
$settings['trusted_host_patterns'] = [
  '^localhost$',
  '^.*\.lndo\.site$',
  '^.*\.ddev\.site$',
];

// Private file path.
$settings['file_private_path'] = 'sites/default/files/private';

// Temporary file path.
$settings['file_temp_path'] = '/tmp';

// Hash salt for one-time login links, etc.
$settings['hash_salt'] = 'local-development-salt';

// Deployment identifier.
$settings['deployment_identifier'] = \Drupal::VERSION;
