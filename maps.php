<?php
/**
 * Plugin Name: Maps
 */

define( 'HARDCORE_MAPS_DIR', dirname( __FILE__ ) );

include( HARDCORE_MAPS_DIR . '/classes/class-plugin.php' );

/**
 * Activate the plugin in this runtime by creating an instance of Hardcore_Maps_Plugin class
 */
new Hardcore_Maps_Plugin();

add_action( 'plugins_loaded',         array( 'Hardcore_Maps_Plugin', 'plugins_loaded' ) );
add_action( 'init',                   array( 'Hardcore_Maps_Plugin', 'init' ) );
add_action( 'template_include',       array( 'Hardcore_Maps_Plugin', 'template_include' ) );
add_action( 'get_template_part_map',  array( 'Hardcore_Maps_Plugin', 'get_template_part' ), 10, 2 );

register_activation_hook(   __FILE__, array( 'Hardcore_Maps_Plugin', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'Hardcore_Maps_Plugin', 'deactivation' ) );
register_uninstall_hook(    __FILE__, array( 'Hardcore_Maps_Plugin', 'uninstall' ) );

/**
 * All functions in functions.php and template-tags.php are intended for the use by site builders, themers and plugin
 * developers. These functions can be considered reliable and their functionality will not change between major plugin
 * releases.
 */

/**
 * Functions for use in plugins.
 */
include HARDCORE_MAPS_DIR . '/functions.php';

/**
 * Template Tags for use in themes.
 *
 * Note: Provided template tags can be overloaded in the theme by defining a function with same name as the template tag.
 */
add_action( 'after_setup_theme', array( 'Hardcore_Maps_Plugin', 'after_setup_theme' ) );