<?php
if ( !class_exists( 'Hardcore_Map_Marker_Feature' ) ) {
  class Hardcore_Map_Marker_Feature extends ScaleUp_Feature {

    /**
     * @todo: add properties after refactoring ScaleUp to not include _ by default in property names
     */

  }
}
ScaleUp::register_feature_type( 'map_marker', array(
  '__CLASS__'    => 'Hardcore_Map_Marker',
  '_plural'      => 'markers',
  '_supports'    => array(),
  '_duck_types'  => array( 'contextual' ),
) );