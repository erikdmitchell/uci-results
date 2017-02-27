jQuery(document).ready(function($) {
	/**
	 * gets an output of races via the season selected
	 */
	$('#get-races-curl').click(function(e) {
		e.preventDefault();
		
		showLoader('#wpcontent');
		
		$('#get-race-data').html('');
		
		var data={
			'action' : 'get_race_data_non_db',
			'form' : $('form.get-races').serialize()
		};

		$.post(ajaxurl,data,function(response) {
			$('#get-race-data').html(response);
			hideLoader();
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
				});
			}
		});
	});	
});