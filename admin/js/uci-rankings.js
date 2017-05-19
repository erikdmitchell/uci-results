jQuery(document).ready(function($) {
	
	// add-rider-rankings button - opens media uploader, runs ajax and imports into db //
	$('.button.add-rider-rankings').click(function(e) {
		e.preventDefault();
		
		var customDate=$(this).parents('form').find('input#custom-date').val();
		var _custom_media = true;
	    var _orig_send_attachment = wp.media.editor.send.attachment;
	    var send_attachment_bkp = wp.media.editor.send.attachment;
	    var button = $(this);

	    _custom_media = true;

	    wp.media.editor.send.attachment = function(props, attachment) {
	      if (_custom_media) {
		    showLoader('#wpcontent');

			var data={
				'action' : 'uci_add_rider_rankings',
				'file' : attachment.url,
				'custom_date' : customDate,
			};

			$.post(ajaxurl, data, function(response) {
				$('#fc-admin-message').html(response);

				hideLoader();
			});
	      } else {
	        return _orig_send_attachment.apply( this, [props, attachment] );
	      }
	    }

	    wp.media.editor.open(button);

	    return false;
	});	
	
});