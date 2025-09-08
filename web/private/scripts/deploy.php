<?php
// Import all config changes.
echo "Running deploy routine...\n";
passthru('drush -y deploy');
echo "Deploy complete.\n";
echo "Warming routes caches...\n";
passthru('drush transit:cache-refresh');
//Clear all cache
echo "Routes caches warmed.\n";