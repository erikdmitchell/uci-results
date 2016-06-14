jQuery(document).ready(function($) {

	var $modal=$('.loading-modal');

	$('form.update-rankings #update').click(function(e) {
		e.preventDefault();

		$modal.show();

		var $btn=$(this);
		var season=$('.season').val();
		var data={
			action : 'update_rider_rankings_get_rider_ids',
			season : season
		};

		$btn.prop('disabled', true);

		$.post(ajaxurl, data, function(rider_ids) {
			var total_riders=rider_ids.length;
			var rider_counter=0;

			$('.update-rider-ranking-notes .rider-ranking-totals .total').text(total_riders);
			$('.update-rider-ranking-notes').show();

			$modal.hide();

			// cycle through rider ids and update //
			for (var i in rider_ids) {
				var update_data={
					'action' : 'update_rider_rankings',
					'rider_id' : rider_ids[i],
					'season' : season
				};

				$.post(ajaxurl, update_data, function(response) {
					rider_counter++;

					$('.update-rider-ranking-notes .rider-ranking-totals .current-count').text(rider_counter);
					$('.update-rider-ranking-notes .response-result').append(response);
				});
			}
		});

		$btn.prop('disabled', false);
	});

});