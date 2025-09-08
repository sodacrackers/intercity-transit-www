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
$local_hosts = ['ict-pantheon.ddev.site', 'intercitytransit.lndo.site'];
if (file_exists($local_settings)) {
  if (in_array($_SERVER['HTTP_HOST'], $local_hosts)) {
    include $local_settings;
  }
  elseif (getenv('LANDO') == 'ON') {
    include $local_settings;
  }
  elseif (getenv('IS_DDEV_PROJECT') == 'true') {
    include $local_settings;
  }
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

ini_set('memory_limit', '1024M');

// Configure Redis

if (defined(
    'PANTHEON_ENVIRONMENT'
  ) && !\Drupal\Core\Installer\InstallerKernel::installationAttempted(
  ) && extension_loaded('redis')) {
  // Set Redis as the default backend for any cache bin not otherwise specified.
  $settings['cache']['default'] = 'cache.backend.redis';

  //phpredis is built into the Pantheon application container.
  $settings['redis.connection']['interface'] = 'PhpRedis';

  // These are dynamic variables handled by Pantheon.
  $settings['redis.connection']['host'] = $_ENV['CACHE_HOST'];
  $settings['redis.connection']['port'] = $_ENV['CACHE_PORT'];
  $settings['redis.connection']['password'] = $_ENV['CACHE_PASSWORD'];

  $settings['redis_compress_length'] = 100;
  $settings['redis_compress_level'] = 1;

  $settings['cache_prefix']['default'] = 'pantheon-redis';

  $settings['cache']['bins']['form'] = 'cache.backend.database'; // Use the database for forms

  // Apply changes to the container configuration to make better use of Redis.
  // This includes using Redis for the lock and flood control systems, as well
  // as the cache tag checksum. Alternatively, copy the contents of that file
  // to your project-specific services.yml file, modify as appropriate, and
  // remove this line.
  $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';

  // Allow the services to work before the Redis module itself is enabled.
  $settings['container_yamls'][] = 'modules/contrib/redis/redis.services.yml';

  // Manually add the classloader path, this is required for the container
  // cache bin definition below.
  $class_loader->addPsr4('Drupal\\redis\\', 'modules/contrib/redis/src');

  // Use redis for container cache.
  // The container cache is used to load the container definition itself, and
  // thus any configuration stored in the container itself is not available
  // yet. These lines force the container cache to use Redis rather than the
  // default SQL cache.
  $settings['bootstrap_container_definition'] = [
    'parameters' => [],
    'services' => [
      'redis.factory' => [
        'class' => 'Drupal\redis\ClientFactory',
      ],
      'cache.backend.redis' => [
        'class' => 'Drupal\redis\Cache\CacheBackendFactory',
        'arguments' => [
          '@redis.factory',
          '@cache_tags_provider.container',
          '@serialization.phpserialize',
        ],
      ],
      'cache.container' => [
        'class' => '\Drupal\redis\Cache\PhpRedis',
        'factory' => ['@cache.backend.redis', 'get'],
        'arguments' => ['container'],
      ],
      'cache_tags_provider.container' => [
        'class' => 'Drupal\redis\Cache\RedisCacheTagsChecksum',
        'arguments' => ['@redis.factory'],
      ],
      'serialization.phpserialize' => [
        'class' => 'Drupal\Component\Serialization\PhpSerialize',
      ],
    ],
  ];
}
