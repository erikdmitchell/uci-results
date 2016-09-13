jQuery(document).ready(function($) {

	var $modal=$('.loading-modal');

	// get weeks for weekly ranks //
	$('form.update-rankings .season').change(function() {
		var data={
			'action' : 'get_weeks_in_season',
			'season' : $(this).val()
		};

		$.post(ajaxurl, data, function(response) {
			$('#update-weekly').attr('data-weeks', response);
		});
	});

	// update season rankings //
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
console.log('pre update rider rankings ajax');
console.log(update_data);
				$.post(ajaxurl, update_data, function(response) {
console.log(response);
					rider_counter++;

					$('.update-rider-ranking-notes .rider-ranking-totals .current-count').text(rider_counter);
					$('.update-rider-ranking-notes .response-result').append(response);

					if (rider_counter>=total_riders) {
						$btn.prop('disabled', false);
					}
				});
			}
		});
	});

	// update rider weekly ranks //
	$('form.update-rankings #update-weekly').click(function(e) {
		e.preventDefault();

		var weeks=$(this).data('weeks').split(',');
		var totalWeeks=weeks.length;
		var weeksCounter=0;

		$('.update-rider-ranking-notes .rider-ranking-totals .total').text(totalWeeks);
		$('.update-rider-ranking-notes').show();

		for (var i in weeks) {
			var data={
				'action' : 'update_rider_weekly_rank',
				'week' : weeks[i],
				'season' : $('.season').val()
			};

			$.post(ajaxurl, data, function(response) {
				weeksCounter++;

				$('.update-rider-ranking-notes .rider-ranking-totals .current-count').text(weeksCounter);
				$('.update-rider-ranking-notes .response-result').append(response);
			});
		}
	});

});