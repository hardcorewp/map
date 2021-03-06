<?php
if ( !class_exists( 'Hardcore_Map_Plugin' ) ) {

  class Hardcore_Map_Plugin {

    /**
     *
     * @var Hardcore_Map_Plugin|null
     */
    private static $_this = null;

    /**
     * Meta key of the latitude of a post
     *
     * @var string
     */
    var $latitude_meta_key  = null;

    /**
     * Meta key of the get longitude of a post
     *
     * @var null
     */
    var $longitude_meta_key = null;

    /**
     * Meta key of the marker icon
     *
     * @param array $args
     */
    var $marker_icon_meta_key = 'marker_icon';

    /**
     * Nonce value used by this plugin
     *
     * @var null
     */
    static $action = 'hardcore_map';

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

      if ( is_admin() ) {
        /**
         * ACF Fields
         */
        add_filter('acf_load_field-marker_icon',      array( $this, 'acf_load_field_marker_icon' ));
        add_filter('acf/load_field/name=marker_icon', array( $this, 'acf_load_field_marker_icon' ) );
      }

      // Let's make sure we are actually doing AJAX first
      if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        // Add our callbacks for AJAX requests
        add_action( 'wp_ajax_hardcore_map',        array( $this, 'wp_ajax_hardcore_map' ) ); // For logged in users
        add_action( 'wp_ajax_nopriv_hardcore_map', array( $this, 'wp_ajax_nopriv_hardcore_map' ) ); // For logged out users
      }

    }

    /**
     * Callback for WordPress' init function
     */
    static function init() {
      self::add_query_vars();
      Hardcore_Geo_Query::add_query_vars();
      Hardcore_Geo_Query::add_filters();
    }

    /**
     * Return existing instance of this plugin
     *
     * @return Hardcore_Map_Plugin|null
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

      if ( !defined( 'HARDCORE_GEO_QUERY_LATITUDE_META_KEY' ) ) {
        define( 'HARDCORE_GEO_QUERY_LATITUDE_META_KEY', 'latitude' );
      }

      if ( !defined( 'HARDCORE_GEO_QUERY_LONGITUDE_META_KEY' ) ) {
        define( 'HARDCORE_GEO_QUERY_LONGITUDE_META_KEY', 'longitude' );
      }

      include HARDCORE_MAP_DIR . '/template-tags.php';
    }

    /**
     * Callback for WordPress' plugins_loaded action
     */
    static function plugins_loaded() {
      include HARDCORE_MAP_DIR . '/functions.php';
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

      wp_register_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=true&libraries=geometry', array(), '3.0', true );
      wp_register_script( 'jquery-ui-map', plugin_dir_url( dirname( __FILE__ ) ) . "assets/jquery.ui.map{$min}.js", array( 'jquery', 'google-maps' ), '3.0-rc', true );
      wp_register_script( 'hardcore-map', plugin_dir_url( dirname( __FILE__ ) ) . "assets/hardcore.map.js", array( 'jquery-ui-map' ), HARDCORE_MAP_VERSION, true );

      wp_register_style( 'hardcore-map', plugin_dir_url( dirname( __FILE__ ) ) . "assets/hardcore.map.css", array(), HARDCORE_MAP_VERSION );

    }

    /**
     * Enqueue scripts for this plugin.
     * Can be used as callback for wp_enqueue_scripts action
     */
    static function enqueue_scripts() {
      wp_enqueue_script( 'jquery-ui-map' );
      wp_enqueue_script( 'jquery-ui-map-extensions' );
      wp_enqueue_script( 'hardcore-map' );
      wp_enqueue_style( 'hardcore-map' );

      // Pass a collection of variables to our JavaScript
      wp_localize_script( 'hardcore-map', 'Hardcore_Map', array(
        'ajax_url'  => admin_url('admin-ajax.php'),
        'action'    => self::$action,
        'nonce'     => wp_create_nonce( self::$action ),
      ) );
    }

    /**
     * Add GeoCoordinates schema to content
     *
     * @param $content
     * @return string
     */
    static function the_content( $content ) {
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
    static function the_excerpt( $excerpt ) {
      $excerpt .= the_geo_coordinates_schema( get_the_ID(), array(
        'echo' => false
      ));
      return $excerpt;
    }

    /**
     * Return url of an icon with specific name or false if not found
     *
     * @param $icon
     * @return bool|string
     */
    static function locate_icon_url( $icon ) {

      $icon_file = "icons/{$icon}.png";

      if ( file_exists( get_stylesheet_directory() . "/$icon_file" ) ) {
        // get icon from child theme
        $url = get_stylesheet_directory_uri() . $icon_file;
      } elseif ( file_exists( get_template_directory() . "/$icon_file" ) ) {
        // get icon from parent theme
        $url = get_template_directory_uri() . $icon_file;
      } elseif ( file_exists( plugin_dir_path( HARDCORE_MAP_PATH ) . $icon_file ) ) {
        // get icon from the plugin
        $url = plugin_dir_url( HARDCORE_MAP_PATH ) . $icon_file;
      } else {
        $url = false;
      }

      return $url;
    }

    /**
     * Dynamically populate Marker Icon select field with icons from icons directory.
     *
     * @param $field
     * @return mixed
     */
    function acf_load_field_marker_icon( $field ) {

      $field[ 'choices' ] = array(
        '' => '-- Select an icon --',
      );

      foreach ( glob( plugin_dir_path( HARDCORE_MAP_PATH ) . 'icons/*.png' ) as $filename ) {
        $icon = pathinfo($filename, PATHINFO_FILENAME);
        $field[ 'choices' ][ $icon ] = $icon;
      }

      return $field;

    }

    function wp_ajax_hardcore_map() {

      $request = $_REQUEST;

      // By default, let's start with an error message
      $response = array(
        'success' => false,
        'data'    => array(),
        'message' => 'Invalid nonce',
      );

      // Next, check to see if the nonce is valid
      if( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], self::$action ) ){
        // Update our message / status since our request was successfully processed
        $response['success'] = true;
        $response['message'] = "Request processed successfully";

        unset( $request[ 'nonce' ] );
        unset( $request[ 'action' ] );

        if ( isset( $request[ 'exclude' ] ) ) {
          $request[ 'post__not_in' ] = $request[ 'exclude' ];
          unset( $request[ 'exclude' ] );
        }

        $geo_query = array_intersect_key( $request, array(
          'unit' => 'm',
          'latitude' => 0,
          'longitude' => 0,
          'beyond'    => null,
          'range'     => 3000,
        ));

        $query_vars = array_diff_assoc( $request, $geo_query );
        $query_vars[ 'geo_query' ] = $geo_query;

        if ( !isset( $query_vars[ 'orderby' ] ) ) {
          $query_vars[ 'orderby' ] = 'distance';
        }

        if ( !isset( $query_vars[ 'order' ] ) ) {
          $query_vars[ 'order' ] = 'ASC';
        }

        $query = new WP_Query( $query_vars );
        $posts = $query->get_posts();
        foreach ( $posts as $post ) {
          $marker = new stdClass();
          $marker->id           = $post->ID;
          $marker->name         = $post->post_title;
          $marker->description  = $post->post_excerpt;
          $marker->link         = get_permalink( $post->ID );
          $marker->latitude     = $post->latitude;
          $marker->longitude    = $post->longitude;
          $marker->distance     = $post->distance;
          $marker->unit         = $post->distance_unit;
          if ( has_post_thumbnail( $post->ID ) ) {
            $marker->image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );
          } else {
            $marker->image = null;
          }
          $response[ 'data' ][] = $marker;
        }
      }

      if ( $response['success'] ) {
        status_header( 200 );
      } else {
        status_header( 403 );
      }

      header('Content-Type: application/json');
      echo json_encode( $response[ 'data' ] );
      die;
    }

    function wp_ajax_nopriv_hardcore_map() {
      $this->wp_ajax_hardcore_map();
    }

    /**
     * Register custom request query vars
     */
    static function add_query_vars() {
      /** @var wp $wp */
      $wp = $GLOBALS[ 'wp' ];
      $wp->add_query_var( 'exclude' );
    }

  }

}