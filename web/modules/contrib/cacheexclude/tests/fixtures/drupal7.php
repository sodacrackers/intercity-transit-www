<?php
// phpcs:ignoreFile
/**
 * @file
 * A database agnostic dump for testing purposes.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

$connection->schema()->createTable('system', array(
  'fields' => array(
    'filename' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
      'default' => '',
    ),
    'name' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
      'default' => '',
    ),
    'type' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '12',
      'default' => '',
    ),
    'owner' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
      'default' => '',
    ),
    'status' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
    ),
    'bootstrap' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
    ),
    'schema_version' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'small',
      'default' => '-1',
    ),
    'weight' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
    ),
    'info' => array(
      'type' => 'blob',
      'not null' => FALSE,
      'size' => 'normal',
    ),
  ),
  'primary key' => array(
    'filename',
  ),
  'indexes' => array(
    'system_list' => array(
      'status',
      'bootstrap',
      'type',
      'weight',
      'name',
    ),
    'type_name' => array(
      'type',
      'name',
    ),
  ),
  'mysql_character_set' => 'utf8mb3',
));

$connection->insert('system')
->fields(array(
  'filename',
  'name',
  'type',
  'owner',
  'status',
  'bootstrap',
  'schema_version',
  'weight',
  'info',
))
->values(array(
  'filename' => 'modules/system/system.module',
  'name' => 'system',
  'type' => 'module',
  'owner' => '',
  'status' => '1',
  'bootstrap' => '0',
  'schema_version' => '7084',
  'weight' => '0',
  'info' => 'a:14:{s:4:"name";s:6:"System";s:11:"description";s:54:"Handles general site configuration for administrators.";s:7:"package";s:4:"Core";s:7:"version";s:4:"7.82";s:4:"core";s:3:"7.x";s:5:"files";a:6:{i:0;s:19:"system.archiver.inc";i:1;s:15:"system.mail.inc";i:2;s:16:"system.queue.inc";i:3;s:14:"system.tar.inc";i:4;s:18:"system.updater.inc";i:5;s:11:"system.test";}s:8:"required";b:1;s:9:"configure";s:19:"admin/config/system";s:7:"project";s:6:"drupal";s:9:"datestamp";s:10:"1626883669";s:5:"mtime";i:1626883669;s:12:"dependencies";a:0:{}s:3:"php";s:5:"5.2.4";s:9:"bootstrap";i:0;}',
))
->values(array(
  'filename' => 'modules/node/node.module',
  'name' => 'node',
  'type' => 'module',
  'owner' => '',
  'status' => '1',
  'bootstrap' => '0',
  'schema_version' => '7016',
  'weight' => '0',
  'info' => 'a:15:{s:4:"name";s:4:"Node";s:11:"description";s:66:"Allows content to be submitted to the site and displayed on pages.";s:7:"package";s:4:"Core";s:7:"version";s:4:"7.82";s:4:"core";s:3:"7.x";s:5:"files";a:2:{i:0;s:11:"node.module";i:1;s:9:"node.test";}s:8:"required";b:1;s:9:"configure";s:21:"admin/structure/types";s:11:"stylesheets";a:1:{s:3:"all";a:1:{s:8:"node.css";s:21:"modules/node/node.css";}}s:7:"project";s:6:"drupal";s:9:"datestamp";s:10:"1626883669";s:5:"mtime";i:1626883669;s:12:"dependencies";a:0:{}s:3:"php";s:5:"5.2.4";s:9:"bootstrap";i:0;}',
))
->values(array(
  'filename' => 'sites/all/modules/contrib/cacheexclude/cacheexclude.module',
  'name' => 'cacheexclude',
  'type' => 'module',
  'owner' => '',
  'status' => '1',
  'bootstrap' => '0',
  'schema_version' => '0',
  'weight' => '0',
  'info' => 'a:11:{s:4:"name";s:13:"Cache Exclude";s:11:"description";s:40:"Exclude certain pages from being cached.";s:4:"core";s:3:"7.x";s:9:"configure";s:32:"admin/config/system/cacheexclude";s:5:"mtime";i:1639487691;s:12:"dependencies";a:0:{}s:7:"package";s:5:"Other";s:7:"version";N;s:3:"php";s:5:"5.2.4";s:5:"files";a:0:{}s:9:"bootstrap";i:0;}',
))
->execute();

$connection->schema()->createTable('variable', array(
  'fields' => array(
    'name' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '128',
      'default' => '',
    ),
    'value' => array(
      'type' => 'blob',
      'not null' => TRUE,
      'size' => 'big',
    ),
  ),
  'primary key' => array(
    'name',
  ),
  'mysql_character_set' => 'utf8mb3',
));

$connection->insert('variable')
->fields(array(
  'name',
  'value',
))
->values(array(
  'name' => 'cacheexclude_list',
  'value' => "s:14:\"node/3\r\nblog/*\";",
))
->values(array(
  'name' => 'cacheexclude_node_types',
  'value' => 'a:5:{s:5:"range";s:5:"range";s:12:"test_display";s:12:"test_display";s:5:"video";s:5:"video";s:7:"article";i:0;s:4:"page";i:0;}',
))
->execute();

$connection->schema()->createTable('node_type', array(
  'fields' => array(
    'type' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '32',
    ),
    'name' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
      'default' => '',
    ),
    'base' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
    ),
    'module' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
    ),
    'description' => array(
      'type' => 'text',
      'not null' => TRUE,
      'size' => 'medium',
    ),
    'help' => array(
      'type' => 'text',
      'not null' => TRUE,
      'size' => 'medium',
    ),
    'has_title' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'unsigned' => TRUE,
    ),
    'title_label' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
      'default' => '',
    ),
    'custom' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
    ),
    'modified' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
    ),
    'locked' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
    ),
    'disabled' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
    ),
    'orig_type' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
      'default' => '',
    ),
  ),
  'primary key' => array(
    'type',
  ),
  'mysql_character_set' => 'utf8mb3',
));

$connection->insert('node_type')
->fields(array(
  'type',
  'name',
  'base',
  'module',
  'description',
  'help',
  'has_title',
  'title_label',
  'custom',
  'modified',
  'locked',
  'disabled',
  'orig_type',
))
->values(array(
  'type' => 'article',
  'name' => 'Article',
  'base' => 'node_content',
  'module' => 'node',
  'description' => 'Use <em>articles</em> for time-sensitive content like news, press releases or blog posts.',
  'help' => '',
  'has_title' => '1',
  'title_label' => 'Title',
  'custom' => '1',
  'modified' => '1',
  'locked' => '0',
  'disabled' => '0',
  'orig_type' => 'article',
))
->values(array(
  'type' => 'page',
  'name' => 'Basic page',
  'base' => 'node_content',
  'module' => 'node',
  'description' => "Use <em>basic pages</em> for your static content, such as an 'About us' page.",
  'help' => '',
  'has_title' => '1',
  'title_label' => 'Title',
  'custom' => '1',
  'modified' => '1',
  'locked' => '0',
  'disabled' => '0',
  'orig_type' => 'page',
))
->values(array(
  'type' => 'panel',
  'name' => 'Panel',
  'base' => 'panels_node_hook',
  'module' => 'panels_node',
  'description' => 'A panel layout broken up into rows and columns.',
  'help' => '',
  'has_title' => '1',
  'title_label' => 'Title',
  'custom' => '0',
  'modified' => '0',
  'locked' => '1',
  'disabled' => '1',
  'orig_type' => 'panel',
))
->values(array(
  'type' => 'range',
  'name' => 'Range',
  'base' => 'node_content',
  'module' => 'node',
  'description' => '',
  'help' => '',
  'has_title' => '1',
  'title_label' => 'Title',
  'custom' => '1',
  'modified' => '1',
  'locked' => '0',
  'disabled' => '0',
  'orig_type' => 'range',
))
->values(array(
  'type' => 'recipe',
  'name' => 'Recipe',
  'base' => 'recipe',
  'module' => 'recipe',
  'description' => 'Share your favorite recipes with your fellow cooks.',
  'help' => '',
  'has_title' => '1',
  'title_label' => 'Title',
  'custom' => '0',
  'modified' => '0',
  'locked' => '1',
  'disabled' => '1',
  'orig_type' => 'recipe',
))
->values(array(
  'type' => 'test_display',
  'name' => 'Test Display',
  'base' => 'node_content',
  'module' => 'node',
  'description' => 'Display page for products',
  'help' => '',
  'has_title' => '1',
  'title_label' => 'Test Display',
  'custom' => '1',
  'modified' => '1',
  'locked' => '0',
  'disabled' => '0',
  'orig_type' => 'test_display',
))
->values(array(
  'type' => 'video',
  'name' => 'Video',
  'base' => 'node_content',
  'module' => 'node',
  'description' => 'Test video embed field.',
  'help' => '',
  'has_title' => '1',
  'title_label' => 'Title',
  'custom' => '1',
  'modified' => '1',
  'locked' => '0',
  'disabled' => '0',
  'orig_type' => 'video',
))
->execute();
