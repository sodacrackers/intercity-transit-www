<?php
// Import all config changes.
echo "Running deploy routine...\n";
passthru('drush -y deploy');
echo "Import of configuration complete.\n";
//Clear all cache
echo "Rebuilding sitemap.\n";
passthru('drush xmlsitemap:rebuild');
echo "Rebuilding cache complete.\n";
