( function($) {

  $.fn.Hardcore_Maps = function( options ) {

    var options = $.extend( {}, {
      canvas:         '.canvas',
      marker:         '.marker',
      latitude:       '[itemprop=latitude]',
      longitude:      '[itemprop=longitude]',
      description:    '[itemprop=description]',
      name:           '[itemprop=name]',
      link:           '[itemprop=url]',
      image:          '[itemprop=image]',
      content_template:
        '<div class="marker">' +
        '<h4><a href="{{url}}">{{name}}</a></h4>' +
        '<img src="{{image}}" />' +
        '<p>{{description}}</p>' +
        '<hr>' +
        '</div>'
    }, options );

    function marker_content( marker ) {
      return Mustache.render( options.content_template, {
        description: marker.find( options.description ).text(),
        name: marker.find( options.name ).text(),
        url: marker.find( options.link ).attr( 'href' ),
        image: marker.find( options.image ).attr( 'src' )
      } );
    }

    $( this ).each( function(){
      var $map = $( this );
      var $canvas = $map.find( options.canvas );
      $canvas.gmap().bind( 'init', function(){
        $map.find( options.marker ).each( function(){
          var $marker = $( this );
          $canvas.gmap( 'addMarker', {
            'position': new google.maps.LatLng( $marker.find( options.latitude ).attr( 'content' ), $marker.find( options.longitude ).attr( 'content' ) ),
            'bounds'  : true
          } ).click( function(){
              $canvas.gmap( 'openInfoWindow', {
                'content': marker_content( $marker )
              }, this )
            });
        } );
      });
    });

    return this;
  }

  $( '.hardcore.map' ).Hardcore_Maps({});

} (jQuery) );