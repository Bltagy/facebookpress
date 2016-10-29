(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	
	// Select category Ajax request 
	$(document).on('change', '#choose_post_type', function(event) {
		var that = $(this);
		// event.preventDefault();
		var data = {
			'action': 'cat_select',
			'type': $(this).val()      // We pass php values differently!
		};
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(ajax_object.ajax_url, data, function(response) {
			if( ! response.success ){
				$('#choose_category').html('<option value="0">No Data</option>');
				return false;
			}
			$('#choose_category').text('');
			$.each(response.data, function(index, val) {
				 $('#choose_category').append('<option value="'+val.slug+'">'+val.name+'</option>');
			});
			$('#choose_category').removeAttr('disabled');
			// alert('Got this from the server: ' + response);
		});	
		/* Act on the event */


	});

	
	$( document ).ready(function() {
		if ( $('a.fb-button').hasClass('disabled') ){
		$( document ).on('click','a.fb-button', function(event) {
			event.preventDefault();
		});
	}
});

})( jQuery );
