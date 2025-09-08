# Transit Data Persistent Cache

## Overview

The IT Route Trip Tools module now uses a custom database table to store transit data that persists even when Drupal's cache is cleared. This ensures that critical transit information remains available without needing to re-fetch from external APIs.

## Implementation Details

### Database Table

The module creates a custom table `it_route_trip_tools_cache` with the following structure:
- `cid`: Cache ID (primary key)
- `data`: Serialized data
- `expire`: Expiration timestamp
- `created`: Creation timestamp
- `tags`: Cache tags for invalidation

### Service

The `it_route_trip_tools.transit_data_cache` service provides methods to:
- `get($cid)`: Retrieve cached data
- `set($cid, $data, $expire, $tags)`: Store data with optional expiration
- `delete($cid)`: Remove specific cache entry
- `deleteAll()`: Clear all cache entries
- `deleteExpired()`: Remove expired entries
- `invalidateTags($tags)`: Invalidate entries by tags

### Migration

All previous `\Drupal::cache()` calls have been replaced with the persistent cache service:

```php
// Old method:
if ($cache = \Drupal::cache()->get($cid)) {
  return $cache->data;
}

// New method:
$cache_service = \Drupal::service('it_route_trip_tools.transit_data_cache');
if ($cache = $cache_service->get($cid)) {
  return $cache;
}
```

## Installation

1. Run database updates to create the new table:
   ```bash
   drush updatedb
   ```

2. Clear Drupal cache to ensure services are registered:
   ```bash
   drush cr
   ```

3. Optionally, refresh transit data cache:
   ```bash
   drush transit:cache-refresh
   ```

## Drush Commands

The module provides several Drush commands for cache management:

- `drush transit:cache-clear` (alias: `tcc`): Clear all transit data cache
- `drush transit:cache-clear-expired` (alias: `tcce`): Clear only expired entries
- `drush transit:cache-refresh` (alias: `tcr`): Clear and refresh cache with fresh data

## Benefits

1. **Persistence**: Data survives Drupal cache clears
2. **Performance**: Reduces API calls to external services
3. **Reliability**: Ensures transit data is always available
4. **Control**: Separate management from Drupal's cache system
5. **Flexibility**: Custom expiration and invalidation logic

## Cached Data

The following data is stored in the persistent cache:
- Route information (24-hour expiration)
- Calendar dates (24-hour expiration)
- GTFS alerts (5-minute expiration)
- Route-specific data by date and direction

## Maintenance

### Automatic Cleanup
Expired entries are automatically removed when accessed. You can also manually clean expired entries:
```bash
drush transit:cache-clear-expired
```

### Manual Refresh
To force a refresh of all transit data:
```bash
drush transit:cache-refresh
```

### Monitoring
Check the database table size periodically:
```sql
SELECT COUNT(*) FROM it_route_trip_tools_cache;
SELECT COUNT(*) FROM it_route_trip_tools_cache WHERE expire > 0 AND expire < UNIX_TIMESTAMP();
```

## Troubleshooting

If you encounter issues:

1. Verify the table exists:
   ```sql
   SHOW TABLES LIKE 'it_route_trip_tools_cache';
   ```

2. Check for errors in logs:
   ```bash
   drush watchdog:show --type=it_route_trip_tools
   ```

3. Manually clear and refresh:
   ```bash
   drush transit:cache-clear
   drush transit:cache-refresh
