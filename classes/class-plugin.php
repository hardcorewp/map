<?php
if ( !class_exists( 'Hardcore_Maps_Plugin' ) ) {

  class Hardcore_Maps_Plugin {

    /**
     *
     * @var Hardcore_Maps_Plugin|null
     */
    private static $_this = null;

    /**
     * Endpoint that will be used to access map for home page, category or tags
     *
     * @var string
     */
    var $map_endpoint = null;

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

    /**
     * @var null
     */
    var $epmasks = null;

    function __construct( $args = array() ) {

      if ( isset( self::$_this ) ) {
        wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.',
          'hardcore-maps-plugin' ), get_class( $this ) ) );
      } else {
        self::$_this = $this;
      }

      add_action( 'plugins_loaded',         array( $this, 'plugins_loaded' ) );
      add_action( 'get_template_part_map',  array( $this, 'get_template_part' ), 10, 2 );

      self::configure( $args );
      self::register_scripts();

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
     * Callback for get_template_part_map action
     *
     * @todo: Remove this for 3.6 and implement template stack
     *
     * @param $slug
     * @param $name
     */
    function get_template_part( $slug, $name = '' ) {

      if ( 'map' == $slug ) {
        /**
         * WordPress is not able to find templates that are included with plugins,
         * therefore we'll need to give it some help. The following code checks if map-marker.php files doesn't exist
         * in the child or parent theme and includes the template from the plugin's template directory.
         * if it does find a map-marker.php file in the parent or child theme directory then it does nothing and allows
         * WordPress to take care of the include.
         */
        if ( empty( $name ) ) {
          $template = "{$slug}.php";
        } else {
          $template = "{$slug}-{$name}.php";
        }

        if ( file_exists( STYLESHEETPATH .  '/' . $template ) ) {
          // template found in child theme, do nothing
        } elseif ( file_exists( TEMPLATEPATH   .  '/' . $template ) ) {
          // template found in parent theme, do nothing
        } else {
          load_template( HARDCORE_MAPS_DIR . '/templates/' . $template, false );
        }

        self::enqueue_scripts();
      }

    }

    /**
     * Callback to after_setup_theme action
     */
    static function after_setup_theme() {

      include HARDCORE_MAPS_DIR . '/template-tags.php';

      $plugin = self::this();

      if ( is_null( $plugin->map_endpoint ) ) {
        $plugin->map_endpoint = 'map';         // slug that is added to end of urls to show maps
      }

      if ( is_null( $plugin->epmasks ) ) {
        $plugin->epmasks = EP_ROOT | EP_CATEGORIES | EP_TAGS | EP_SEARCH; // where end points should be added
      }

      if ( is_null( $plugin->latitude_meta_key ) ) {
        if ( defined( 'HARDCORE_GEO_QUERY_LATITUDE_META_KEY' ) ) {
          /**
           * Use Geo Query constants if Geo Query Plugin is available
           */
          $plugin->latitude_meta_key = HARDCORE_GEO_QUERY_LATITUDE_META_KEY;
        } else {
          $plugin->latitude_meta_key = 'latitude';    // meta key that's used to get post's latitude
        }
      }

      if ( is_null( $plugin->longitude_meta_key ) ) {
        if ( defined( 'HARDCORE_GEO_QUERY_LONGITUDE_META_KEY' ) ) {
          /**
           * Use Geo Query constants if Geo Query Plugin is available
           */
          $plugin->longitude_meta_key = HARDCORE_GEO_QUERY_LONGITUDE_META_KEY;
        } else {
          $plugin->longitude_meta_key = 'longitude';   // meta key that's used to get post's longitude
        }
      }

    }

    /**
     * Callback for WordPress' plugins_loaded action
     */
    function plugins_loaded() {

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

  }

}