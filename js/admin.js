jQuery(document).ready(function($) {
	var $modal=$('.loading-modal');

	/**
	 * populates the url field on the UCI cURL page when a year is selected (years are pre populated)
	 */
	$('#get-race-season').change(function() {
		$('#url').val($(this).val());
	});

	/**
	 * gets an output of races via the season selected
	 */
	$('#get-races-curl').click(function(e) {
		$modal.show();
		$('#get-race-data').html('');
		e.preventDefault();

		var data={
			'action' : 'get_race_data_non_db',
			'url' : $('form.get-races').find('#url').val(),
			'limit' : $('form.get-races').find('#limit').val()
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
			var counter=1;

			$('#get-race-data').append('<div id="counter"><span class="ctr">'+counter+'</span> out of '+races.length+' proccessed.');

			for (var i in races) {
				//$modal.show();

				var data={
					'action' : 'add_race_to_db',
					'race' : races[i]
				};

				$.post(ajaxurl,data,function(response) {
					$('#get-race-data').append(response);
					$('#get-race-data').find('#counter span.ctr').text(counter);
					counter++;
					//$modal.hide();
					// after we are done races //
					if (counter>=races.length) {
						console.log('fin');
						return false;
					}
				});
			}
		});
	});

});