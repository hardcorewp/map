( function($) {

  $.fn.Hardcore_Maps = function() {

    function marker_content( map, marker, options ) {
      return Mustache.render( map.find( options.template ).html(), {
        description:  marker.find( options.description ).text(),
        name:         marker.find( options.name ).text(),
        url:          marker.find( options.link ).attr( 'href' ),
        image:        marker.find( options.image ).attr( 'src' )
      } );
    }

    // iterate over all matched maps and show them
    $( this ).each( function(){

      var $map = $( this );

      // merge options from data-options attribute with default options
      var options = $.extend( {}, {
        canvas:         '.canvas',
        marker:         '.marker',
        latitude:       '[itemprop=latitude]',
        longitude:      '[itemprop=longitude]',
        description:    '[itemprop=description]',
        name:           '[itemprop=name]',
        link:           '[itemprop=url]',
        image:          '[itemprop=image]',
        template:       'script',
        container:      '#main'
      }, $.parseJSON( $map.attr( 'data-options' ) ) );

      var $canvas = $map.find( options.canvas );

      $canvas.gmap().bind( 'init', function(){

        // find all markers in the container
        $( options.container + ' ' + options.marker ).each( function(){
          var $marker = $( this );
          var lat = $marker.find( options.latitude ).attr( 'content' );
          var lng = $marker.find( options.longitude ).attr( 'content' );
          if ( lat && lng ) {
            $canvas.gmap( 'addMarker', {
              'position': new google.maps.LatLng( lat, lng ),
              'bounds'  : true
            } ).click( function(){
                $canvas.gmap( 'openInfoWindow', {
                  'content': marker_content( $map, $marker, options )
                }, this )
              });
          }
        } );
      });
    });

    return this;
  }

  $( '.hardcore.map' ).Hardcore_Maps();

} (jQuery) );