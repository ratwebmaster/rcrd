(function($) {

	$(document).ready(function() {

		$(document).on('googleMapMarkerDrag', '.crown-framework-geo-coordinates-input .google-map', function(e, map, marker) {
			var container = map.closest('.crown-framework-geo-coordinates-input');
			$('input.coordinate-lat', container).val(marker.position.lat());
			$('input.coordinate-lng', container).val(marker.position.lng());
		});

	});

})(jQuery);