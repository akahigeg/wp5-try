<?php
/*
Plugin Name: Static Post Type
Version: 0.2.0-beta
Description: Create custom post types, taxonomies and custom fields by static YAML file
Author: akahigeg
Author URI: http://higelog.brassworks.jp/
Plugin URI: https://github.com/akahigeg/static-post-type
Text Domain: static-post-type
License: Apache License 2.0
Domain Path: /languages
*/

if (!array_key_exists('static-post-type', $GLOBALS)) {
  $include_path = plugin_dir_path(__FILE__) . 'includes';
  require_once($include_path . '/StaticPostType.php');
  require_once($include_path . '/StaticPostTypeUtil.php');

foreach (scandir($include_path . '/StaticPostTypeFormRenderer') as $filename) {
    $path = $include_path . '/StaticPostTypeFormRenderer' . '/' . $filename;
    if (is_file($path)) {
        require_once $path;
    }
}


  $GLOBALS['static-post-type'] = new StaticPostType();
  StaticPostType::addActions();
  StaticPostType::enableSortableColumns();
  StaticPostType::enableTaxonomyCustomFields();
}

/*
TODO: show args in admin console
TODO: comment
TODO: add_filter('request', <order>)
*/
