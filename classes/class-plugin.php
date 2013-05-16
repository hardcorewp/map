<?php
if ( !class_exists( 'Hardcore_Maps_Plugin' ) ) {

  class Hardcore_Maps_Plugin {

    /**
     * Callback for WordPress' plugins_loaded action
     *  -
     */
    static function plugins_loaded() {

      if ( class_exists( 'ScaleUp' ) ) {

        include( HARDCORE_MAPS_DIR . '/classes/class-addon.php' );
        include( HARDCORE_MAPS_DIR . '/classes/class-map.php' );
        include( HARDCORE_MAPS_DIR . '/classes/class-map-view.php' );
        include( HARDCORE_MAPS_DIR . '/classes/class-map-marker.php' );
        include( HARDCORE_MAPS_DIR . '/classes/class-map-marker-view.php' );

      }

    }

  }

}