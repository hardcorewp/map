<?php
if ( !class_exists( 'Hardcore_Maps_Plugin' ) ) {

  class Hardcore_Maps_Plugin {

    /**
     *
     * @var Hardcore_Maps_Plugin|null
     */
    private static $_this = null;

    /**
     * Meta key that's used to get latitude of a post
     *
     * @var string
     */
    var $latitude_meta_key  = null;

    /**
     * Meta key that's used to get longitude of a post
     *
     * @var null
     */
    var $longitude_meta_key = null;

    function __construct( $args = array() ) {

      if ( isset( self::$_this ) ) {
        wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.',
          'hardcore-maps-plugin' ), get_class( $this ) ) );
      } else {
        self::$_this = $this;
      }

      $default = array(
        'latitude_meta_key'   => ( defined( 'HARDCORE_GEO_QUERY_LATITUDE_META_KEY' ) ? HARDCORE_GEO_QUERY_LATITUDE_META_KEY : 'latitude' ),
        'longitude_meta_key'  => ( defined( 'HARDCORE_GEO_QUERY_LONGITUDE_META_KEY' ) ? HARDCORE_GEO_QUERY_LONGITUDE_META_KEY : 'longitude' ),
      );

      self::configure( wp_parse_args( $args, $default ) );
      self::register_scripts();

      add_filter( 'the_content', array( $this, 'the_content' ) );
      add_filter( 'the_excerpt', array( $this, 'the_excerpt' ) );
    }

    /**
     * Return existing instance of this plugin
     *
     * @return Hardcore_Maps_Plugin|null
     */
    static function this() {
      return self::$_this;
    }

    /**
     * Configure this plugin by providing associative array of configurations
     *
     * @param array $args
     */
    static function configure( $args = array() ) {
      if ( isset( self::$_this ) ) {
        $plugin = self::$_this;
        foreach ( $args as $key => $value ) {
          $plugin->$key = $value;
        }
      }
    }

    /**
     * Callback to after_setup_theme action
     */
    static function after_setup_theme() {
      include HARDCORE_MAPS_DIR . '/template-tags.php';
    }

    /**
     * Callback for WordPress' plugins_loaded action
     */
    static function plugins_loaded() {
      include HARDCORE_MAPS_DIR . '/functions.php';

      if ( class_exists( 'ScaleUp' ) ) {
        include( HARDCORE_MAPS_DIR . '/classes/class-addon.php' );
        include( HARDCORE_MAPS_DIR . '/classes/class-map.php' );
        include( HARDCORE_MAPS_DIR . '/classes/class-map-view.php' );
        include( HARDCORE_MAPS_DIR . '/classes/class-map-marker.php' );
        include( HARDCORE_MAPS_DIR . '/classes/class-map-marker-view.php' );
      }
    }

    /**
     * Register all of the scripts that are necessary to display maps
     */
    static function register_scripts() {

      if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) {
        $min = '.min';
      } else {
        $min = '';
      }

      wp_register_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=true', array(), '3.0', true );
      wp_register_script( 'jquery-ui-map', plugin_dir_url( dirname( __FILE__ ) ) . "assets/jquery.ui.map{$min}.js", array( 'jquery', 'google-maps' ), '3.0-rc', true );
      wp_register_script( 'jquery-ui-map-extensions', plugin_dir_url( dirname( __FILE__ ) ) . "assets/jquery.ui.map.extensions.js", array( 'jquery', 'jquery-ui-map' ), '3.0-rc', true );
      wp_register_script( 'mustache', plugin_dir_url( dirname( __FILE__ ) ) . "assets/mustache.js", array(), '0.7.2', true );
      wp_register_script( 'hardcore-maps', plugin_dir_url( dirname( __FILE__ ) ) . "assets/hardcore.maps.js", array( 'jquery-ui-map', 'jquery-ui-map-extensions', 'mustache' ), HARDCORE_MAPS_VERSION, true );

      wp_register_style( 'hardcore-maps', plugin_dir_url( dirname( __FILE__ ) ) . "assets/hardcore.maps.css", array(), HARDCORE_MAPS_VERSION );

    }

    /**
     * Enqueue scripts for this plugin.
     * Can be used as callback for wp_enqueue_scripts action
     */
    static function enqueue_scripts() {
      wp_enqueue_script( 'jquery-ui-map' );
      wp_enqueue_script( 'jquery-ui-map-extensions' );
      wp_enqueue_script( 'hardcore-maps' );
      wp_enqueue_style( 'hardcore-maps' );
    }

    /**
     * Add GeoCoordinates schema to content
     *
     * @param $content
     * @return string
     */
    function the_content( $content ) {
      $content .= the_geo_coordinates_schema( get_the_ID(), array(
        'echo' => false
      ));
      return $content;
    }

    /**
     * Add GeoCoordinates schema to the excerpt
     *
     * @param $excerpt
     * @return string
     */
    function the_excerpt( $excerpt ) {
      $excerpt .= the_geo_coordinates_schema( get_the_ID(), array(
        'echo' => false
      ));
      return $excerpt;
    }

  }

}