(function($) {

	$(document).ready(function() {

		$('.crown-framework-checkbox-set-input.sortable > .inner').sortable()
			.on('sortstart', function(event) {
				event.stopPropagation();
			})
			.on('sortstop', function(event) {
				event.stopPropagation();
			});
			
		$('.crown-framework-radio-set-input.sortable > .inner').sortable()
			.on('sortstart', function(event) {
				event.stopPropagation();
			})
			.on('sortstop', function(event) {
				event.stopPropagation();
			});

	});

})(jQuery);