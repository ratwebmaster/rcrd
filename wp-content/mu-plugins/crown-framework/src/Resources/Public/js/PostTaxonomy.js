(function($) {

	$(document).ajaxComplete(function(e, xhr, settings) {
		try {
			var response = $.parseXML(xhr.responseText);
			if($(response).find('wp_error').length) return;
			$(response).find('response').each(function(i,e) {
				if($(e).attr('action').indexOf('add-tag') > -1) {
					var tid = $(e).find('term_id');
					if(tid) {
						clearForm($('form[action=\'edit-tags.php\']'));
					}
				}
			});
		} catch(err) {}
	});

	function clearForm(form) {
		console.log('Clearing form...');

		// text and textarea
		$('.crown-framework-field input[type=text], .crown-framework-field textarea', form).val('');

		// checkbox
		// $('.crown-framework-field input[type=checkbox]',form).prop('checked', false);
		
		// radio
		// $('.crown-framework-field input[type=radio]',form).prop('checked', false);

		// select
		// $('.crown-framework-field select', form).val('');

		// rich textarea
		$('.crown-framework-field .wp-editor-wrap .wp-editor-area', form).each(function(i, el) {
			tinyMCE.get($(el).attr('id')).setContent('');
		});

		// media
		$('.crown-framework-field .crown-framework-media-input', form).each(function(i, el) {
			$(el).removeClass('has-media');
			$('input[type=hidden]', el).val('');
			$('.media-input-preview, .media-input-name', el).text('');
		});

		// gallery
		$('.crown-framework-field .crown-framework-gallery-input', form).each(function(i, el) {
			$(el).removeClass('has-media');
			$('ul.gallery-images li', el).remove();
		});
		
	}

})(jQuery);