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

    static function get_map_html( $attributes, $args ) {
      /**
       * Set default values if AJAX is being used
       */
      if ( isset( $attributes[ 'ajax' ] ) && true === $attributes[ 'ajax' ] && !isset( $attributes[ 'url' ] ) ) {
        $attributes[ 'url' ]        = esc_url( admin_url( 'admin-ajax.php' ) );
        $attributes[ 'data-type' ]  = 'json';
        $attributes[ 'name' ]       = 'post_title';
        $attributes[ 'description' ]= 'post_content';
        $attributes[ 'image' ]      = 'post_thumbnail';
        $attributes[ 'link' ]       = 'post_permalink';

        $nonce = wp_create_nonce( Hardcore_Map_Plugin::$action );
        if ( isset( $attributes[ 'data' ] ) ) {
          $attributes[ 'data' ][ 'nonce' ] = $nonce;
        } else {
          $attributes[ 'data' ] = array(
            'action'  => Hardcore_Map_Plugin::$action,
            'nonce'   => $nonce
          );
        }
      }

      $id = '';
      if ( array_key_exists( 'id', $attributes ) ) {
        $id = $attributes[ 'id' ];
        unset( $attributes[ 'id' ] );
      } else {
        if ( is_single() ) {
          global $post;
          $id = $post->post_name;
        }
        if ( is_archive() ) {
          global $wp_query;
          $wp_query->get_queried_object();
          $id = $wp_query->slug;
        }
        if ( is_search() ) {
          $id = 'search';
        }
      }
      if ( $id ) {
        $id = "id='{$id}'";
      }

      if ( isset( $attributes[ 'query_vars' ] ) ) {
        if ( isset( $attributes[ 'data' ] ) ) {
          $data = wp_parse_args( $attributes[ 'data' ], $attributes[ 'query_vars' ] );
        } else {
          $data = $attributes[ 'query_vars' ];
        }
        unset( $attributes[ 'query_vars' ] );
        $attributes[ 'data' ] = json_encode( $data );
      }

      $attributes  = apply_filters_ref_array( 'the_map_attributes', array( $attributes, false ) );

      $args = wp_parse_args( $args, array(
        'echo'      => true,            // echo the output
        'enqueue'   => true,            // enqueue scripts and styles
      ));

      $attr = Hardcore_Map::get_html_attributes( $attributes );

      $html = "<div {$id} class='hardcore map' {$attr}></div>";

      if ( $args[ 'enqueue' ] ) {
        Hardcore_Map_Plugin::enqueue_scripts();
      }

      return $html;
    }
  }
}