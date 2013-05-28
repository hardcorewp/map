<?php
if ( !function_exists( 'the_map_marker_latitude' ) ) {
  /**
   * Output latitude for current post or post specified by $post_ID.
   *
   * @param int $post_ID
   * @param array $args
   */
  function the_map_marker_latitude( $post_ID = null, $args = array() ) {

    if ( is_null( $post_ID ) ) {
      $post_ID = get_the_ID();
    }

    $default = array(
      'echo'  => true,
    );

    $args = wp_parse_args( $args, $default );

    $coordinates = get_map_marker_coordinates( $post_ID, $args );

    if ( is_array( $coordinates ) && isset( $coordinates[ 'latitude' ] ) ) {
      $latitude = $coordinates[ 'latitude' ];
    } else {
      $latitude = '';
    }

    if ( $args[ 'echo' ] ) {
      echo $latitude;
    }
    
  }
}

if ( !function_exists( 'the_map_marker_longitude' ) ) {
  /**
   * Output longitude for current post or post specified by $post_ID.
   *
   * @param int $post_ID
   * @param array $args
   */
  function the_map_marker_longitude( $post_ID = null, $args = array() ) {

    if ( is_null( $post_ID ) ) {
      $post_ID = get_the_ID();
    }

    $default = array(
      'echo'  => true,
    );

    $args = wp_parse_args( $args, $default );

    $coordinates = get_map_marker_coordinates( $post_ID, $args );

    if ( is_array( $coordinates ) && isset( $coordinates[ 'longitude' ] ) ) {
      $longitude = $coordinates[ 'longitude' ];
    } else {
      $longitude = '';
    }

    if ( $args[ 'echo' ] ) {
      echo $longitude;
    }

  }
}
