<?php
if ( !class_exists( 'Hardcore_Geo_Query' ) ) {
  class Hardcore_Geo_Query {

    static function add_filters() {
      add_filter( 'posts_fields',           array( __CLASS__, 'posts_fields' ), 10, 2 );
      add_filter( 'posts_join',             array( __CLASS__, 'posts_join' ), 10, 2 );
      add_filter( 'posts_where',            array( __CLASS__, 'posts_where' ), 10, 2 );
      add_filter( 'posts_orderby',          array( __CLASS__, 'posts_orderby' ), 10, 2 );
      add_filter( 'posts_clauses_request',  array( __CLASS__, 'posts_clauses_request' ), 10, 2 );
    }

    static function remove_filters() {
      remove_filter( 'posts_fields',          array( __CLASS__, 'posts_fields' ) );
      remove_filter( 'posts_join',            array( __CLASS__, 'posts_join' ) );
      remove_filter( 'posts_where',           array( __CLASS__, 'posts_where' ) );
      remove_filter( 'posts_orderby',         array( __CLASS__, 'posts_orderby' ) );
      remove_filter( 'posts_clauses_request', array( __CLASS__, 'posts_clauses_request' ) );
    }

    static function parse_query( $query ) {

      if ( isset( $query->query_vars[ 'geo_query' ] ) ) {

        $query->query_vars[ 'suppress_filters' ] = false;

        /** set default distance unit if its not set */
        if ( !isset( $query->query_vars[ 'geo_query' ][ 'unit' ] ) ) {
          $query->query_vars[ 'geo_query' ][ 'unit' ] = 'm';
        }

        /** set default value for beyond */
        if ( !isset( $query->query_vars[ 'geo_query' ][ 'beyond' ] ) ) {
          $query->query_vars[ 'geo_query' ][ 'beyond' ] = null;
        }

        /** set default range to 1 */
        if ( !isset( $query->query_vars[ 'geo_query' ][ 'range' ] ) ) {
          $query->query_vars[ 'geo_query' ][ 'range' ] = '3000';
        }

        /** convert range and beyond to miles if unit set to 'mi' */
        if ( 'mi' == $query->query_vars[ 'geo_query' ][ 'unit' ] ) {

        }

        /** set default latitude & longitude meta key */
        if ( !isset( $query->query_vars[ 'geo_query' ][ 'latitude_meta_key' ] ) ) {
          $query->query_vars[ 'geo_query' ][ 'latitude_meta_key' ] = HARDCORE_GEO_QUERY_LATITUDE_META_KEY;
        }
        if ( !isset( $query->query_vars[ 'geo_query' ][ 'longitude_meta_key' ] ) ) {
          $query->query_vars[ 'geo_query' ][ 'longitude_meta_key' ] = HARDCORE_GEO_QUERY_LONGITUDE_META_KEY;
        }

      }

    }

    static function posts_fields( $fields, $query ) {

      if ( isset( $query->query_vars[ 'geo_query' ] ) ) {
        /*** @var wpdb $wpdb * */
        $wpdb = $GLOBALS[ 'wpdb' ];

        switch( $query->query_vars[ 'geo_query' ][ 'unit' ] ) {
          case 'm':
          case 'meter':
          case 'meters':
            $radius = 6371000;
            break;
          case 'km':
          case 'kilometer':
          case 'kilometers':
            $radius = 6371;
            break;
          case 'mi':
          case 'miles':
            $radius = 3959;
            break;
          case 'ft':
          case 'foot':
          case 'feet':
            $radius = 20903520;
            break;
          default:
            $radius = 1;
        }

        $fields .= $wpdb->prepare( ", latitude.meta_value as latitude, longitude.meta_value as longitude, ( %f * acos( cos( radians( %f ) ) * cos( radians( latitude.meta_value ) ) * cos( radians( longitude.meta_value ) - radians( %f ) ) + sin( radians( %f ) ) * sin( radians( latitude.meta_value ) ) ) ) as distance, %s as distance_unit ",
          $radius,
          $query->query_vars[ 'geo_query' ][ 'latitude' ],
          $query->query_vars[ 'geo_query' ][ 'longitude' ],
          $query->query_vars[ 'geo_query' ][ 'latitude' ],
          $query->query_vars[ 'geo_query' ][ 'unit' ] );
      }

      return $fields;
    }

    static function posts_join( $join, $query ) {

      if ( isset( $query->query_vars[ 'geo_query' ] ) ) {
        /*** @var wpdb $wpdb * */
        $wpdb = $GLOBALS[ 'wpdb' ];

        $join .= $wpdb->prepare( " LEFT JOIN {$wpdb->postmeta} as latitude ON( {$wpdb->posts}.ID = latitude.post_id AND latitude.meta_key = %s ) ", $query->query_vars[ 'geo_query' ][ 'latitude_meta_key' ] );
        $join .= $wpdb->prepare( " LEFT JOIN {$wpdb->postmeta} as longitude ON( {$wpdb->posts}.ID = longitude.post_id AND longitude.meta_key = %s ) ", $query->query_vars[ 'geo_query' ][ 'longitude_meta_key' ] );
      }

      return $join;
    }

    static function posts_where( $where, $query ) {

      /*** @var wpdb $wpdb * */
      $wpdb = $GLOBALS[ 'wpdb' ];

      if ( isset( $query->query_vars[ 'geo_query' ] ) ) {
        $where .= "	AND ( longitude.meta_value IS NOT NULL AND latitude.meta_value IS NOT NULL ) ";
      }

      return $where;
    }

    static function posts_orderby( $orderby, $query ) {

      if ( isset( $query->query_vars[ 'orderby' ] ) && 'distance' == $query->query_vars[ 'orderby' ] ) {
        $orderby = " distance " . $query->query_vars[ 'order' ];
      }

      return $orderby;
    }

    static function posts_clauses_request( $clauses, $query ) {
      if ( isset( $query->query_vars[ 'geo_query' ] ) ) {
        /*** @var wpdb $wpdb * */
        $wpdb = $GLOBALS[ 'wpdb' ];

        if ( stristr( ' having ', $clauses[ 'where' ] ) ) {
          $clauses[ 'where' ] .= " AND ";
        } else {
          $clauses[ 'where' ] .= " HAVING ";
        }

        if ( isset( $query->query_vars[ 'geo_query' ][ 'beyond' ] ) ) {
          $clauses[ 'where' ] .= $wpdb->prepare( " distance BETWEEN %f AND %f",
            $query->query_vars[ 'geo_query' ][ 'beyond' ],
            $query->query_vars[ 'geo_query' ][ 'range' ]
          );
        } else {
          if ( $query->query_vars[ 'geo_query' ][ 'range' ] ) {
            $clauses[ 'where' ] .= $wpdb->prepare( " distance <= %f ", $query->query_vars[ 'geo_query' ][ 'range' ] );
          }
        }

      }
      return $clauses;
    }

    /**
     * Register custom request query vars
     */
    static function add_query_vars() {
      /** @var wp $wp */
      $wp = $GLOBALS[ 'wp' ];
      $wp->add_query_var( 'latitude' );
      $wp->add_query_var( 'longitude' );
      $wp->add_query_var( 'beyond' );
      $wp->add_query_var( 'range' );
      $wp->add_query_var( 'unit' );
    }
  }
}