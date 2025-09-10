<?php

namespace Drupal\it_route_trip_tools\Commands;

use Drush\Commands\DrushCommands;
use Drupal\it_route_trip_tools\TransitDataCache;

/**
 * Drush commands for managing transit data cache.
 */
class TransitDataCacheCommands extends DrushCommands {

  /**
   * The transit data cache service.
   *
   * @var \Drupal\it_route_trip_tools\TransitDataCache
   */
  protected $transitDataCache;

  /**
   * Constructs a TransitDataCacheCommands object.
   *
   * @param \Drupal\it_route_trip_tools\TransitDataCache $transit_data_cache
   *   The transit data cache service.
   */
  public function __construct(TransitDataCache $transit_data_cache) {
    parent::__construct();
    $this->transitDataCache = $transit_data_cache;
  }

  /**
   * Clear all transit data cache.
   *
   * @command transit:cache-clear
   * @aliases tcc
   * @usage drush transit:cache-clear
   *   Clear all transit data cache entries.
   */
  public function clearCache() {
    $this->transitDataCache->deleteAll();
    $this->logger()->success('Transit data cache cleared.');
  }

  /**
   * Clear expired transit data cache entries.
   *
   * @command transit:cache-clear-expired
   * @aliases tcce
   * @usage drush transit:cache-clear-expired
   *   Clear only expired transit data cache entries.
   */
  public function clearExpiredCache() {
    $this->transitDataCache->deleteExpired();
    $this->logger()->success('Expired transit data cache entries cleared.');
  }

  /**
   * Refresh transit data cache by fetching new data.
   *
   * @command transit:cache-refresh
   * @aliases tcr
   * @usage drush transit:cache-refresh
   *   Clear cache and fetch fresh data from APIs.
   */
  public function refreshCache() {
    // Trigger cache rebuild by calling the functions
    $routes = it_route_trip_tools_pics_get_all_routes_data(NULL, 0, FALSE);
    $dates = it_route_trip_tools_pics_get_dates();

    $this->logger()->success(dt('Transit data cache refreshed. Cached @routes routes and @dates dates.', [
      '@routes' => count($routes),
      '@dates' => count($dates),
    ]));
  }
}
