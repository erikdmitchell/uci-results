jQuery(document).ready(function($) {

	var $modal=$('.loading-modal');

	$('form.update-rankings #update').click(function(e) {
		e.preventDefault();

		$modal.show();

		var data={
			action : 'update_rider_rankings',
			season : $('.season').val()
		};

		$('#update-rider-ranking-notes').append('<div class="note">Calculating rider rankings...</div>');

		$.post(ajaxurl, data, function(response) {
			$('#update-rider-ranking-notes').append('<div class="updated">Rider rankings complete.</div>');
		});
	});

});