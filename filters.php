<?php
if ( !function_exists( 'the_nearby_prettify_distance' ) ) {
  /**
   * Make the distance pretty.
   *  in km
   *    - if less than 1km than show in meters
   *  in miles
   *    - if less than 1 mile than show in feet
   *
   * @param int     $distance
   * @param string  $unit
   * @return string
   */
  function the_nearby_prettify_distance( $distance, $unit ) {
    switch ( $unit ) {
      case 'mi':
      case 'mile':
        /* meters */
        if ( $distance < 1000 ) {
          $distance = round( $distance * 5.280 );
          $unit     = __( 'ft', 'geo_query' );
        } else {
          $distance = round( $distance / 1000, 2 );
        }
        break;
      case 'km':
      case 'kilometer':
        if ( $distance < 1000 ) {
          $distance = round( $distance );
          $unit     = __( 'm', 'geo_query' );
        } else {
          $distance = round( $distance / 1000, 2 );
        }
        break;
    }
    return sprintf( "<span class='amount'>%s</span><span class='unit'>%s</span>", $distance, $unit );
  }
}
add_filter( 'the_nearby_prettify_distance', 'the_nearby_prettify_distance', 10, 2 );