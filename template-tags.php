<?php
if ( !function_exists( 'the_map' ) ) {
  /**
   * Output the map markup that hardcore.maps.js will replace with a Google Map
   *
   * When the page is rendered by the browser, hardcore-maps.js will look for DOM elements that match selector ".hardcore.maps"
   *  - for every found instance of the map markup
   *    - get options from data-options attribute
   *    - initialize the map in the DOM element that matches the selector in canvas option ( default: ".canvas" )
   *    - find all markers that match markers option ( default: "#content .hentry" )
   *        - for every found marker
   *            -  add a marker to the map
   *
   * @param array $options map options
   * @param array $args output arguments
   *
   * @return string
   */
  function the_map( $options = array(), $args = array() ) {

    /**
     * Some options are commented out because they're here for educational purposes.
     */
    $options = wp_parse_args( $options, array(
      'width'        => '100%',                  // width of the map canvas ( include the units )
      'height'       => '400px',                 // height of the map canvas ( include the units )
#      'markers'      => '#main .post',          // css selector of markers
#      'name'         => '.entry-title',         // css selector of the title to show in the info window
#      'link'         => '.entry-title a',       // css selector of the link to be used in the info window
#      'description'  => '.entry-content p',     // css selector of the description to show in the info window
#      'image'        => '.entry-header img',    // css selector of the image to show in info window
#      'latitude'     => '[itemprop=latitude]',  // css selector of the latitude to be used for placing the marker
#      'longitude'    => '[itemprop=longitude]', // css selector of the longitude to be used for placing the marker
      'canvas'       => '.canvas',               // css selector of the map canvas
      'center'       => array( -33.87308, 151.207001 )  // default center point for the map
    ));

    $args = wp_parse_args( $args, array(
      'echo'      => true,            // echo the output
      'enqueue'   => true,            // enqueue scripts and styles
      'marker_template' => '',        // Mustache template to be used for
    ));

    if ( $args[ 'enqueue' ] ) {
      Hardcore_Maps_Plugin::enqueue_scripts();
    }

    $options  = apply_filters( 'the_map_options', $options );
    $template = apply_filters( 'the_map_marker_template', $args[ 'marker_template' ] );

    $json = json_encode( $options );

    $html = <<<HTML
  <div class="hardcore map" data-options='{$json}'>
      <div class="canvas"></div>
      <script type="text/html">
      {$template}
      </script>
  </div>
HTML;

    if ( $args[ 'echo' ] ) {
      echo $html;
    }

    return $html;
  }
}

if ( !function_exists( 'the_map_options' ) ) {
  /**
   * Return array of configuration options that are passed to the javascript to configure the jQuery Hardcore Map plugin.
   *
   * This configuration tells the javascript plugin what selectors to use when looking for information to build the map.
   * You can overload this function in the child or the parent theme to provide theme specific selectors.
   *
   * @param array $options
   * @param bool $echo
   * @return array
   */
  function the_map_options( $options = array(), $echo = false ) {

    switch ( get_template() ) :
      case 'twentytwelve':
      case 'twentythirteen':
        $theme = array(
          'markers'      => '#content .hentry',
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
          'markers'      => '#main .post',
          'name'         => '.post-title',
          'link'         => '.post-title a',
          'description'  => '.entry-content p',
          'image'        => '.thumbnail img',
          'latitude'     => '[itemprop=latitude]',
          'longitude'    => '[itemprop=longitude]',
        );
        break;
      default:
        $theme = array(
          'markers'     => '#content .hentry',
          'name'         => '.entry-title',
          'link'         => '.entry-title a',
          'description'  => '.entry-content p:first-child',
          'image'        => '.entry-header img',
          'latitude'     => '[itemprop=latitude]',
          'longitude'    => '[itemprop=longitude]',
        );
    endswitch;

    $options = wp_parse_args( $options, $theme );

    if ( $echo ) {
      echo json_encode( $options );
    }

    return $options;
  }
}
add_filter( 'the_map_options', 'the_map_options' );

if ( !function_exists( 'the_map_marker_template' ) ) {
  /**
   * Return default map marker mustache template
   *
   * @param string $template
   * @param bool $echo
   * @return string
   */
  function the_map_marker_template( $template = '', $echo = false ) {

    if ( empty( $template ) ) {
      $template = <<<TEMPLATE
<div class="info-window">
  <h4><a href="{{url}}">{{name}}</a></h4>
  <img src="{{image}}" />
  <p>{{description}}</p>
  <hr>
</div>
TEMPLATE;
    }

    if ( $echo ) {
      echo $template;
    }

    return $template;
  }
}
add_filter( 'the_map_marker_template', 'the_map_marker_template' );

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

    $plugin = Hardcore_Maps_Plugin::this();

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

    $icon_url = Hardcore_Maps_Plugin::locate_icon( $icon );

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

    $plugin = Hardcore_Maps_Plugin::this();

    $icon = get_post_meta( $args[ 'post_ID' ], $plugin->marker_icon_meta_key, true );

    return $icon;
  }

}
add_filter( 'the_map_marker_icon', 'the_map_marker_icon' );