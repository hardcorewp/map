<?php
/**
 * Plugin Name: Maps
 */

define( 'HARDCORE_MAP_PATH', __FILE__ );
define( 'HARDCORE_MAP_DIR', dirname( __FILE__ ) );
define( 'HARDCORE_MAP_VERSION', '1.0' );

include( HARDCORE_MAP_DIR . '/classes/class-map.php' );
include( HARDCORE_MAP_DIR . '/classes/class-plugin.php' );

/**
 * Activate the plugin in this runtime by creating an instance of Hardcore_Map_Plugin class
 */
new Hardcore_Map_Plugin();

// include Functions API after all plugins were loaded
add_action( 'plugins_loaded',     array( 'Hardcore_Map_Plugin', 'plugins_loaded' ) );

// include Template Tags API after parent and child themes were loaded
add_action( 'after_setup_theme',  array( 'Hardcore_Map_Plugin', 'after_setup_theme' ) );

// Callbacks that add GeoCoordinates schema markup to the end of the content
add_filter( 'the_content', array( 'Hardcore_Map_Plugin', 'the_content' ) );
add_filter( 'the_excerpt', array( 'Hardcore_Map_Plugin', 'the_excerpt' ) );

/**
 * All functions in functions.php and template-tags.php are intended for the use by site builders, themers and plugin
 * developers. These functions can be considered reliable and their functionality will not change between major plugin
 * releases.
 *
 * functions.php - functions for use in plugins.
 * template-tags.php - template tags for use in themes.
 *
 * Note: Provided template tags can be overloaded in the theme by defining a function with same name as the template tag.
 */