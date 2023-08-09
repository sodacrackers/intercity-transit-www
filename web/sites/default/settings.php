<?php
/* Store the current Pantheon site environment */
$current_env = $_ENV["PANTHEON_ENVIRONMENT"];

if ($current_env != 'live'):
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  $config['system.logging']['error_level'] = 'verbose';
endif;

if ($current_env == 'live'):
  $config['saml_sp_drupal_login.config']['force_saml_only'] = TRUE;
else:
  $config['saml_sp_drupal_login.config']['force_saml_only'] = FALSE;
endif;

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';
$settings['container_yamls'][] = __DIR__ . '/../development.services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all environments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Skipping permissions hardening will make scaffolding
 * work better, but will also raise a warning when you
 * install Drupal.
 *
 * https://www.drupal.org/project/drupal/issues/3091285
 */
// $settings['skip_permissions_hardening'] = TRUE;

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

/**
 * Place the config directory outside of the Drupal root.
 */
$settings['config_sync_directory'] = dirname(DRUPAL_ROOT) . '/config';


$docuri = $_SERVER['REQUEST_URI'];
if ((strpos($docuri, 'traveloptions/vanpoolandcarpool/currentvanpools/Pages/VanpoolDetails.aspx?itemId') !== false) &&
  (php_sapi_name() != "cli")) {
  $newloc = '/vanpool/join';
  header('Location: http://intercitytransit.com' . $newloc);
  exit();
}

// Automatically generated include for settings managed by ddev.
$ddev_settings = dirname(__FILE__) . '/settings.ddev.php';
if (getenv('IS_DDEV_PROJECT') == 'true' && is_readable($ddev_settings)) {
  require $ddev_settings;
}
