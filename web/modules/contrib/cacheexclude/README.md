CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Configuration

INTRODUCTION
------------
This module provides a simple way to exclude certain pages from being cached.
Sometimes you want all pages to be cached for anonymous users except for one or
two pages that have dynamic or random or rotating content. If those pages are
cached, the dynamic parts cease to be dynamic.

This module allows an administrator to selectively exclude certain paths from
being cached so that dynamic content is actually dynamic.

For a full description of the module, visit the project page:
https://www.drupal.org/project/cacheexclude


INSTALLATION
------------
* Install as you would normally install a contributed Drupal module.
* Visit https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
for further information.

CONFIGURATION
-------------

Go to `Configuration > System > Cacheexclude` settings. From the configuration
page, you can:

  * Configure paths you want excluded from caching.
  * Configure content types to exclude from caching.
