<?php
if ( !class_exists( 'Hardcore_Map' ) ) {
  class Hardcore_Map extends ScaleUp_Feature {

    /**
     * Add a marker to the map and return its instance
     *
     * @param string $name
     * @param string $lat latitude
     * @param string $lng longitude
     * @param array $args
     * @return Hardcore_Map_Marker|bool
     */
    function add_marker( $name, $lat, $lng, $args = array() ) {
      return $this->add( 'map_marker', wp_parse_args( array(
        'name'  => $name,
        'lat'   => $lat,
        'lng'   => $lng,
      ), $args ) );
    }

  }
}
ScaleUp::register_feature_type( 'map', array(
  '__CLASS__'    => 'Hardcore_Map',
  '_plural'      => 'maps',
  '_supports'    => array( 'markers' ),
  '_duck_types'  => array( 'global' ),
) );