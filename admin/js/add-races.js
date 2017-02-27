jQuery(document).ready(function($) {
	var $modal=$('.loading-modal');
	
	/**
	 * gets an output of races via the season selected
	 */
	$('#get-races-curl').click(function(e) {
		e.preventDefault();
		
		$modal.show();
		
		$('#get-race-data').html('');
		
		var data={
			'action' : 'get_race_data_non_db',
			'form' : $('form.get-races').serialize()
		};

		$.post(ajaxurl,data,function(response) {
			$('#get-race-data').html(response);
			$modal.hide();
		});
	});
	
	/**
	 * gets the checked races and adds them to db, returning a success (or error) message
	 */
	$('#add-races-curl-to-db').live('click',function(e) {
		e.preventDefault();

		var selected = [];
		var season=$('.url-dd#season option:selected').text();

		$('#add-races-to-db input:checked').each(function() {
			if ($(this).attr('id')!='selectall')
	    	selected.push($(this).val());
		});

		var data={
			'action' : 'prepare_add_races_to_db',
			'races' : selected
		};

		$.post(ajaxurl,data,function(response) {
			$('#get-race-data').html('');

			var races=$.parseJSON(response);
			var counter=0;

			$('#get-race-data').append('<div id="counter"><span class="ctr">'+counter+'</span> out of '+races.length+' proccessed.');

			for (var i in races) {
				//$modal.show();

				var data={
					'action' : 'add_race_to_db',
					'race' : races[i]
				};

				$.post(ajaxurl,data,function(response) {
					counter++;

					$('#get-race-data').append(response);
					$('#get-race-data').find('#counter span.ctr').text(counter);

					//$modal.hide();
					// after we are done races //
					if (counter>=races.length) {
						$('#get-race-data').find('#counter span.ctr').text(counter); // update counter on screen

						var data={
							action : 'update_rider_rankings',
							season : season
						};

						$('#get-race-data').append('<div class="note">Calculating rider rankings...</div>');

						$('#get-race-data').append('<div class="note">You need to do this on the other page for now.</div>');

						//$.post(ajaxurl, data, function(response) {
							//$('#get-race-data').append('<div class="updated">Rider rankings complete.</div>');
						//});

						return false;
					}
				});
			}
		});
	});	
});