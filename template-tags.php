<?php
if ( !function_exists( 'the_map' ) ) {
  /**
   *
   * @param array $args
   * @return string
   */
  function the_map( $args = array() ) {

    $default = array(
      'echo'     => true,       // echo the output
      'enqueue'  => true,       // enqueue scripts and styles
      'options'  => array(),    // options to be passed to javascript
      'marker_template' => '',  // Mustache template to be used for
    );

    $args = wp_parse_args( $args, $default );

    if ( $args[ 'enqueue' ] ) {
      Hardcore_Maps_Plugin::enqueue_scripts();
    }

    $options  = apply_filters( 'the_map_options', $args[ 'options' ] );
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
          'container'    => '#content',
          'marker'       => '.hentry',
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
          'container'    => '#main',
          'marker'       => '.post',
          'name'         => '.post-title',
          'link'         => '.post-title a',
          'description'  => '.entry-content p',
          'image'        => 'img',
          'latitude'     => '[itemprop=latitude]',
          'longitude'    => '[itemprop=longitude]',
        );
        break;
      default:
        $theme = array();
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

    if ( $args[ 'latitude' ] && $args[ 'longitude' ] ) {
      $html = <<<HTML
<span itemprop="geo" itemscope="itemscope" itemtype="http://schema.org/GeoCoordinates">
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
