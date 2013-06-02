<?php
if ( !class_exists( 'Hardcore_Maps' ) ) {
  class Hardcore_Maps {

    /**
     * Return icon name for a post with $args[ 'post_ID' ]
     *
     * @param $args
     * @return mixed
     */
    static function get_map_marker_icon( $args ) {
      $plugin = Hardcore_Maps_Plugin::this();
      $icon = get_post_meta( $args[ 'post_ID' ], $plugin->marker_icon_meta_key, true );
      return $icon;
    }

  }
}