<?php
if ( !class_exists( 'Hardcore_Maps_Addon' ) ) {
  class Hardcore_Maps_Addon extends ScaleUp_Addon {

    function activation() {
      $this->set( 'url', '' );

      $this->add_view( 'nearby_map', '/map/nearby/{latitude},{longitude}', array(
        '__CLASS__' => 'Hardcore_Map_View',
        'template'  => 'map',
      ) );

      $this->add_view( 'map_marker', '/map/{map_name}/{marker_name}', array(
        '__CLASS__' => 'Hardcore_Map_Marker_View'
      ));

      $this->add_view( 'map', '/map/{map_name}', array(
        '__CLASS__' => 'Hardcore_Map_View',
      ) );

      $this->add_view( 'default_map', '/map', array(
        '__CLASS__' => 'Hardcore_Map_View',
        'template'  => 'map',
      ) );

      $this->add_map( $this->get( 'map_name' ) );
    }

    /**
     * Add a map to the site and return instance of it
     *
     * @param $name
     * @param array $args
     * @return mixed
     */
    function add_map( $name, $args = array() ) {

      $site = ScaleUp::get_site();

      $map = $site->add( 'map', wp_parse_args( array(
        'name'  => $name,
      ), $args ) );

      return $map;
    }

    function get_defaults() {
      return wp_parse_args( array(
        'map_name'  => 'default',
      ), parent::get_defaults());
    }
  }
}
ScaleUp::register( 'addon', array( 'name' => 'maps', '__CLASS__' => 'Hardcore_Maps_Addon' ) );