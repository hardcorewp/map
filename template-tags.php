<?php
if ( !function_exists( 'the_map' ) ) {
  /**
   * Output the map markup that will be replaced with a Google Map
   *
   * @param array $attributes Bootstrap Map data attributes
   * @param array $args output arguments
   *
   * @return string
   */
  function the_map( $attributes = array(), $args = array() ) {

    /**
     * Attribute options that are used to configure the Map plugin
     *
     * 'marker'      => '#main .post',          // css selector of markers
     * 'latitude'    => '[itemprop=latitude]',  // css selector of the latitude to be used for placing the marker
     * 'longitude'   => '[itemprop=longitude]', // css selector of the longitude to be used for placing the marker
     * 'name'        => '.entry-title',         // css selector of the title to show in the info window
     * 'link'        => '.entry-title a',       // css selector of the link to be used in the info window
     * 'description' => '.entry-content p',     // css selector of the description to show in the info window
     * 'image'       => '.entry-header img',    // css selector of the image to show in info window
     * 'center'      => '-33.87308,151.207001', // initial center point for the map ( LatLng string or 'geolocation' )
     * 'template'    => '<div><div class="info-window"><img/><h4><a></a></h4><p></p><hr></div></div>' // marker template
     * 'data-type'   => 'html'                  // 'html' or 'json'
     * 'url'         =>
     * 'query_vars'  => array()
     */

    $args = wp_parse_args( $args, array(
      'echo'      => true,            // echo the output
      'enqueue'   => true,            // enqueue scripts and styles
    ));

    $html = Hardcore_Map::get_map_html( $attributes, $args );

    if ( $args[ 'echo' ] ) {
      echo $html;
    }

    return $html;
  }
}

if ( !function_exists( 'the_map_attributes' ) ) {
  /**
   * Return array of configuration options that are passed to the javascript to configure the jQuery Hardcore Map plugin.
   *
   * This configuration tells the javascript plugin what selectors to use when looking for information to build the map.
   * You can overload this function in the child or the parent theme to provide theme specific selectors.
   *
   * @param array $attributes
   * @param bool $echo
   * @return array
   */
  function the_map_attributes( $attributes = array(), $echo = true ) {

    switch ( get_template() ) :
      case 'twentytwelve':
      case 'twentythirteen':
        $theme = array(
          'marker'       => '#content .hentry',
          'name'         => '.entry-title',
          'link'         => '.entry-title a',
          'description'  => '.entry-content p:first-child',
          'image'        => '.entry-header img',
          'latitude'     => '[itemprop=latitude]',
          'longitude'    => '[itemprop=longitude]',
        );
        break;
      case 'standard' :
        /**
         * @link: http://standardtheme.com/
         */
        $theme = array(
          'marker'       => '#main .post',
          'name'         => '.post-title',
          'link'         => '.post-title a',
          'description'  => '.entry-content p',
          'image'        => '.thumbnail img',
          'latitude'     => '[itemprop=latitude]',
          'longitude'    => '[itemprop=longitude]',
        );
        break;
      default:
        /**
         * To integrate this plugin with your commercial theme,
         * hook to the_map_attributes_theme_defaults filter and specify
         * your theme's default attributes
         */
        $theme = apply_filters( 'the_map_attributes_theme_defaults', array(
          'marker'       => '#content .hentry',
          'name'         => '.entry-title',
          'link'         => '.entry-title a',
          'description'  => '.entry-content p:first-child',
          'image'        => '.entry-header img',
          'latitude'     => '[itemprop=latitude]',
          'longitude'    => '[itemprop=longitude]',
        ) );
    endswitch;

    $attributes = wp_parse_args( $attributes, $theme );

    if ( $echo ) {
      $attr = '';
      foreach ( $attributes as $key => $value ) {
        $attr .= " data-$key='$value'";
      }
      echo $attr;
    }

    return $attributes;
  }
}
add_filter( 'the_map_attributes', 'the_map_attributes', 10, 2 );

if ( !function_exists( 'the_geo_coordinates_schema' ) ) {
  /**
   * Output the GeoCoordinates schema markup for current post or post with $post_ID
   *
   * @see: http://schema.org/GeoCoordinates
   *
   * @param null  $post_ID
   * @param array $args
   * @return string
   */
  function the_geo_coordinates_schema( $post_ID = null, $args = array() ) {

    if ( is_null( $post_ID ) ) {
      $post_ID = get_the_ID();
    }

    $default = array(
      'echo'      => true,
    );

    $args = wp_parse_args( $args, $default );

    $plugin = Hardcore_Map_Plugin::this();

    if ( !isset( $args[ 'latitude' ] ) ) {
      $args[ 'latitude' ] = get_post_meta( $post_ID, $plugin->latitude_meta_key, true );
    }

    if ( !isset( $args[ 'longitude' ] ) ) {
      $args[ 'longitude' ] = get_post_meta( $post_ID, $plugin->longitude_meta_key, true );
    }

    $icon = apply_filters_ref_array( 'the_map_marker_icon', array( null, array(
      'echo'    => false,
      'post_ID' => $post_ID,
    )));

    $icon_url = Hardcore_Map_Plugin::locate_icon_url( $icon );

    if ( $args[ 'latitude' ] && $args[ 'longitude' ] ) {
      $html = <<<HTML
<span itemprop="geo" itemscope="itemscope" itemtype="http://schema.org/GeoCoordinates" data-icon-url="{$icon_url}">
  <meta itemprop="latitude" content="{$args['latitude']}" />
  <meta itemprop="longitude" content="{$args['longitude']}" />
</span>
HTML;
    } else {
      $html = '';
    }
    $html = apply_filters( 'the_geo_coordinates_schema_html', $html, $post_ID, $args );

    if ( $args[ 'echo' ] ) {
      echo $html;
    }

    return $html;
  }
}

if ( !function_exists( 'the_map_marker_icon' ) ) {
  /**
   * Return the name of the icon
   *
   * To programmatically change the marker, define a function in the same name in your plugin or theme and include the
   * logic that determines what marker should be displayed. The return value should be a string that will be used to
   * the create the filename of the using {$icon}.png template.
   *
   * @param null $icon
   * @param array $args
   *
   * @return string
   */
  function the_map_marker_icon( $icon = null, $args = array() ) {

    $default = array(
      'echo'    => true,
      'post_ID' => get_the_ID(),
    );

    $args = wp_parse_args( $args, $default );

    $icon = Hardcore_Map::get_map_marker_icon( $args );

    if ( $args[ 'echo' ] ) {
      echo $icon;
    }

    return $icon;
  }

}
add_filter( 'the_map_marker_icon', 'the_map_marker_icon', 10, 2 );

if ( !function_exists( 'the_nearby' ) ) {
  /**
   * Output a list of nearby posts
   *
   * @param int   $post_ID
   * @param array $args
   * @param array $query_vars
   * @return string
   */
  function the_nearby( $post_ID = null, $args = array(), $query_vars = array() ) {

    if ( is_null( $post_ID ) ) {
      $post_ID = get_the_ID();
    }

    $default = array(
      'title_format'          => '<h3>%s</h3>',
      'before_list'           => '<ul class="nearby">',
      'after_list'            => '</ul>',
      'before_item'           => '<li>',
      'after_item'            => '</li>',
      'before_title'          => '<h4>',
      'after_title'           => '</h4>',
      'distance_format'       => __( '<span class="distance">%s away</span>', 'geo_query' ),
      'post_thumbnail_show'   => false,
      'post_thumbnail_size'   => 'thumbnail',
      'post_thumbnail_attr'   => array(),
      'echo'                  => true,
    );

    $args = wp_parse_args( $args , $default );

    $posts = get_nearby( $post_ID, $query_vars );

    if ( $posts ) {
      $html = array();
      if ( $args[ 'title_format' ] ) {
        $html[] = sprintf( $args[ 'title_format' ], __( 'Nearby', 'geo_query' ) );
      }
      $html[] = $args[ 'before_list' ];
      foreach ( $posts as $post ) {
        setup_postdata( $post );
        $url    = get_permalink( $post->ID );
        $title  = apply_filters( 'the_title', $post->post_title, $post->ID );
        $html[] = $args[ 'before_item' ];
        if ( $args[ 'post_thumbnail_show' ] && has_post_thumbnail() ) {
          $html[] = "<a href='{$url}'>";
          $html[] = get_the_post_thumbnail( get_the_ID(), $args[ 'post_thumbnail_size' ], $args[ 'post_thumbnail_attr' ] );
          $html[] = "</a>";
        }
        $html[] = "{$args[ 'before_title' ]}<a href='{$url}'>{$title}</a>{$args[ 'after_title' ]}";
        $html[] = sprintf( $args[ 'distance_format' ], apply_filters( 'the_nearby_prettify_distance', $post->distance, $post->distance_unit ) );
        $html[] = $args[ 'after_item' ];
      }
      $html[] = $args[ 'after_list' ];
      wp_reset_postdata();

      if ( $args[ 'echo' ] ) {
        echo implode( "\n", $html );
      } else {
        return implode( "\n", $html );
      }
    }

  }
}