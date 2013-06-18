<?php
if ( !function_exists( 'get_nearby' ) ) {
  /**
   * Return an array of nearby posts
   *
   * @param int $ID
   * @param array $args
   * @return array
   */
  function get_nearby( $ID = null, $args = array() ) {

    if ( is_null( $ID ) ) {
      $ID = get_the_ID();
    }

    $default = array(
      'post_type'       => get_post_type(),
      'post_status'     => 'publish',
      'posts_per_page'  => 10,
      'geo_query'       => array(
        'latitude'  => get_post_meta( get_the_ID(), HARDCORE_GEO_QUERY_LATITUDE_META_KEY, true ),
        'longitude' => get_post_meta( get_the_ID(), HARDCORE_GEO_QUERY_LONGITUDE_META_KEY, true ),
        'range'     => 5,
        'unit'      => 'km',
      ),
      'post__not_in' => array( $ID ),
      'orderby' => 'distance',
      'order'   => 'ASC'
    );

    $args = wp_parse_args( $args, $default );

    $query = new WP_Query( $args );

    return $query->get_posts();
  }
}

