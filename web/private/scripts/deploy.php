<?php
// Import all config changes.
echo "Running deploy routine...\n";
passthru('drush -y deploy');
echo "Deploy complete.\n";
echo "Warming caches...\n";
passthru('drush php:eval "it_route_trip_tools_pics_get_all_routes_data();"');
//Clear all cache
echo "Queues warmed.\n";