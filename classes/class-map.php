<?php
if ( !class_exists( 'Hardcore_Map' ) ) {
  class Hardcore_Map {
    /**
     * Return icon name for a post with $args[ 'post_ID' ]
     *
     * @param $args
     * @return mixed
     */
    static function get_map_marker_icon( $args ) {
      $plugin = Hardcore_Map_Plugin::this();
      $icon = get_post_meta( $args[ 'post_ID' ], $plugin->marker_icon_meta_key, true );
      return $icon;
    }

    /**
     * Turn an associative array of attributes into a string
     *
     * @param array $attributes
     * @return string
     */
    static function get_html_attributes( $attributes ) {
      $attr = '';
      foreach ( $attributes as $key => $value ) {
        if ( !is_null( $value ) ) {
          $attr .= " data-$key='$value'";
        }
      }
      return $attr;
    }
  }
}