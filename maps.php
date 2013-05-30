<?php
/**
 * Plugin Name: Maps
 */

define( 'HARDCORE_MAPS_PATH', __FILE__ );
define( 'HARDCORE_MAPS_DIR', dirname( __FILE__ ) );
define( 'HARDCORE_MAPS_VERSION', '1.0' );

include( HARDCORE_MAPS_DIR . '/classes/class-plugin.php' );

/**
 * Activate the plugin in this runtime by creating an instance of Hardcore_Maps_Plugin class
 */
new Hardcore_Maps_Plugin();

add_action( 'plugins_loaded',     array( 'Hardcore_Maps_Plugin', 'plugins_loaded' ) );
add_action( 'after_setup_theme',  array( 'Hardcore_Maps_Plugin', 'after_setup_theme' ) );

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