( function($) {

  $.fn.Hardcore_Maps = function() {

    // iterate over all matched maps and show them
    $( this ).each( function(){

      var $wrapper = $( this );

      // merge options from data-options attribute with default options
      var options = $.extend( {}, $.fn.Hardcore_Maps.defaults, $.parseJSON( $wrapper.attr( 'data-options' ) ) );

      var $map = $wrapper.find( options.canvas );
      $map.height( options.height );
      $map.width( options.width );

      $map.gmap({
        center: new google.maps.LatLng( options.center[0], options.center[1] )
      }).bind( 'init', function(){
          if ( options.position == 'autodetect' ) {
            $map.gmap( 'getCurrentPosition', function(position, status) {
              if ( status === 'OK' ) {
                var clientPosition = new google.maps.LatLng( position.coords.latitude, position.coords.longitude );
                $map.gmap( 'option', 'center', clientPosition );
              }
            });
          }
          $.fn.Hardcore_Maps.add_markers( $wrapper, $map, options );
        });
    });

    return this;
  }

  $.fn.Hardcore_Maps.info_window_content = function( $wrapper, $marker, options ) {
    return Mustache.render( $wrapper.find( options.template ).html(), {
      description:  $marker.find( options.description ).text(),
      name:         $marker.find( options.name ).text(),
      url:          $marker.find( options.link ).attr( 'href' ),
      image:        $marker.find( options.image ).attr( 'src' )
    } );
  }

  $.fn.Hardcore_Maps.add_markers = function( $wrapper, $map, options ) {
    // find all markers in the container
    $( options.markers ).each( function(){
      var $marker = $( this );
      var lat = $marker.find( options.latitude ).attr( 'content' );
      var lng = $marker.find( options.longitude ).attr( 'content' );
      if ( lat && lng ) {
        $map.gmap( 'addMarker', {
          position  : new google.maps.LatLng( lat, lng ),
          bounds    : true,
          icon      : $marker.find( options.geo ).attr( 'data-icon-url' )
        } ).click( function(){
            $map.gmap( 'openInfoWindow', {
              'content': $.fn.Hardcore_Maps.info_window_content( $wrapper, $marker, options )
            }, this )
          });
      }
    } );
  }

  $.fn.Hardcore_Maps.defaults = {
    canvas:         '.canvas',
    markers:        '.marker',
    latitude:       '[itemprop=latitude]',
    longitude:      '[itemprop=longitude]',
    description:    '[itemprop=description]',
    name:           '[itemprop=name]',
    link:           '[itemprop=url]',
    image:          '[itemprop=image]',
    geo:            '[itemprop=geo]',
    template:       'script',
    center:         [ -33.87308, 151.207001 ], // [ lat, lng ]
    position:       undefined,
    height:         '400px',
    width:          '100%'
  };

  $( '.hardcore.map' ).Hardcore_Maps();

} (jQuery) );