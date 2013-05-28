<?php
/**
 * Return array of coordinates for post with specified $post_ID
 *
 * @param $post_ID
 * @param array $args
 * @return array
 */
function get_map_marker_coordinates( $post_ID, $args = array() ) {

  $plugin = Hardcore_Maps_Plugin::this();

  $default = array(
    'latitude_meta_key' => $plugin->latitude_meta_key,
    'longitude_meta_key' => $plugin->longitude_meta_key,
  );

  $args = wp_parse_args( $args, $default );

  $coordinates = array();
  $coordinates[ 'latitude' ]  = get_post_meta( $post_ID, $args[ 'latitude_meta_key' ], true );
  $coordinates[ 'longitude' ] = get_post_meta( $post_ID, $args[ 'longitude_meta_key' ], true );

  return $coordinates;
}