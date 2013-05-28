 /*!
 * jQuery UI Google Map 3.0-rc
 * http://code.google.com/p/jquery-ui-map/
 * Copyright (c) 2010 - 2011 Johan Säll Larsson
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 *
 * Depends:
 *      jquery.ui.map.js
 */
( function($) {

	$.extend($.ui.gmap.prototype, {
		 
		/**
		 * Gets the current position
		 * @param callback:function(position, status)
		 * @param geoPositionOptions:object, see https://developer.mozilla.org/en/XPCOM_Interface_Reference/nsIDOMGeoPositionOptions
		 */
		getCurrentPosition: function(callback, geoPositionOptions) {
			if ( navigator.geolocation ) {
				navigator.geolocation.getCurrentPosition ( 
					function(result) {
						callback(result, 'OK');
					}, 
					function(error) {
						callback(null, error);
					}, 
					geoPositionOptions 
				);	
			} else {
				callback(null, 'NOT_SUPPORTED');
			}
		}

	});
	
} (jQuery) );