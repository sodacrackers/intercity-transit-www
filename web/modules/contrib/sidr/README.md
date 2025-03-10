# Sidr: Responsive Menus

This module allows the admin to create "trigger" blocks which when clicked, use
the Sidr libraries to slide in / slide out a specified target element. This is
very useful for implementing responsive menus.

All you have to do is create one or more dedicated regions in your theme, say,
`mobile_menu` and then configure a Sidr trigger block to toggle it.

  * For a full description of the module, visit the
    [Sidr project page](https://www.drupal.org/project/sidr) on Drupal.org.
  * Submit bug reports and feature suggestions, or to track changes, see the
    [Sidr issue queue](https://www.drupal.org/project/issues/search/sidr) on
    Drupal.org.
  * To see a changelog / commit history see the
    [Git history page](https://git.drupalcode.org/project/sidr/commits).

## Installation

### Module installation

Install the Sidr module using the following command:
  ```
  composer require drupal/sidr
  ```

### Library installation

Next, install the Sidr JS libraries with composer with
[Asset Packagist](https://asset-packagist.org/).

  * Install the [Composer Merge Plugin](https://github.com/wikimedia/composer-merge-plugin).
  * Include Sidr's `composer.libraries.json` in the project's `composer.json` file.
    ```json
    {
      "extra": {
        "merge-plugin": {
          "include": [
            "web/modules/contrib/sidr/composer.libraries.json"
          ]
        }
      }
    }
    ```
  * Make sure there is an entry for `type:drupal-library` under the
    `installer-paths` section.
    ```json
    {
      "installer-paths": {
        "web/libraries/{$name}": [
          "type:drupal-library"
        ]
      }
    }
    ```
    Some platforms might have their Drupal document root in the non-standard
    `docroot` directory (e.g. Acquia). In those cases, you'll need to make
    sure the `installer-paths` point to the right location.
  * If you did everything correctly, running `composer install` at this point
    should automatically install Sidr JS libraries.

    If it doesn't work, you might have to reinstall the Sidr module as follows.
    ```
    composer require -W drupal/sidr
    ```

Done! Now the Sidr libraries should be in your Drupal project such that
`jquery.sidr.js` is located in `DRUPAL-ROOT/libraries/sidr/dist/jquery.sidr.js`.
Additionally, if you've enabled the Sidr module already, Drupal's *Status
report* page should show if the libraries are installed correctly.

## Configuration

  * Go to the "Block layout" page (`admin/structure/block`):
  * Click on the "Place block" button for the region in which you want to
    place the trigger for your Sidr and place a "Sidr trigger button" block.
    This trigger will toggle your Sidr.
  * Configure the block as per your needs and save your changes.

The Sidr trigger should be visible on your site and if you click on the
trigger, you should see a Sidr menu sliding out as per your configuration.

## Special behaviors

### js-sidr-close

  * If you want to create a *close* button for Sidr panels, you can add the
    `js-sidr-close` class to that element.
  * Clicking on elements that have the above class closes open Sidr panels.

## Development

This project uses Ddev for dockerized development. To set up a dev env,

- `git clone git@git.drupal.org:project/sidr.git`
- `cd` into the repository
- `cd .development`
- `ddev start`
- `ddev ssh`
- `git clone git@git.drupal.org:project/sidr.git`
  - This is the second clone of the repo which is available inside the Ddev
    container. This is where you'll make your changes.

You can read more about using Ddev on the Ddev website.

## Maintainers

Current maintainers:

  * Jigar Mehta (jigarius) - https://jigarius.com/
