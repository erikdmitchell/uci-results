jQuery(document).ready(function($) {
	var $modal=$('.loading-modal');

	/**
	 * populates the url field on the UCI cURL page when a year is selected (years are pre populated)
	 */
	$('#season').change(function() {
		$('#url').val($(this).val());
	});

	/**
	 * select all
	 */
  $('#select-all').live('click',function(event) {
	  event.preventDefault();

	  if ($(this).hasClass('unselect')) {
		 	$(this).removeClass('unselect');
    	$('.check-column > input').each(function() {
      	this.checked = false;  //select all checkboxes
      });
		} else {
			$(this).addClass('unselect');
    	$('.check-column > input').each(function() {
	    	if (!$(this).is(':disabled')) {
	      	this.checked = true; //deselect all checkboxes
	      }
      });
		}
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

						$.post(ajaxurl, data, function(response) {
							$('#get-race-data').append('<div class="updated">Rider rankings complete.</div>');
						});

						return false;
					}
				});
			}
		});
	});

	/**
	 * related races ajax search
	 */
	$("#search-related-races").live("keyup", function(e) {
		// Set Search String
		var search_string = $(this).val();

	  // Do Search
	  if (search_string!=='' && search_string.length>=3) {
			$.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					action : 'search_related_races',
					query : search_string,
					race_id : $('#main_race_id').val()
				},
				success: function(response){
					$('#related-races-search-results').html(response);
				}
			});
	  }

	  return false;
	});

});