<?php
/**
 * Plugin Name: Maps
 */

define( 'HARDCORE_MAPS_DIR', dirname( __FILE__ ) );

include( HARDCORE_MAPS_DIR . '/classes/class-plugin.php' );

/**
 * Hook to WordPress' plugins_loaded hook
 */
add_action( 'plugins_loaded', array( 'Hardcore_Maps_Plugin', 'plugins_loaded' ) );