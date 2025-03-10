CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended Modules
 * Installation
 * Configuration
 * Troubleshooting
 * Maintainers
 * Supporters


INTRODUCTION
------------

The Scheduled Updates module provides the ability to schedule.

 * For a full description of the module
   visit: https://www.drupal.org/project/scheduled_updates

 * To submit bug reports and feature suggestions, or to track changes
   visit: https://www.drupal.org/project/issues/scheduled_updates.


REQUIREMENTS
------------

This module requires Inline Entity Form [inline_entity_form].


RECOMMENDED MODULES
-------------------

This module has no additional recommended modules.


INSTALLATION
------------

 * Install the Scheduled Updates module as you would normally install a
   contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
   further information.


CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module and the Inline
       Entity Form module.
    2. Clear caches.
    3. Navigate to Entity's Managed Fields page.
        Example 1: Navigate to Administration >> Structure >> Content Types >>
                   Article >> Manage Fields
        Example 2: Navigate to Administration >> Configuration >> People >>
                   Account Settings >> Manage Fields
    4. Click 'Add Update field'.
    5. Select entity field to add schedule.
        Update Reference Options and Update Runner Settings boxes appear.
    6. Optional: If the field supports a Default Value, then the Default Value
                 and Date Only Updates box appears above other boxes.
    7. Select bundle(s) to add new update field.
    8. Create or re-use Reference field.
    9. Update Runner Settings - needs more review.
    10.Save update field.
    11.Add or edit Entity Type and find the Update field you created. Enter
       desired values and click Create button, save entity.


TROUBLESHOOTING
---------------

If the module is not shown in the list try deleting the module and try cloning
it again. or else try clearing the cache, and then try
installing it.


MAINTAINERS
-----------

 * tedbow(https://www.drupal.org/u/tedbow)


SUPPORTERS
-----------

 * Acquia(https://www.drupal.org/acquia)
