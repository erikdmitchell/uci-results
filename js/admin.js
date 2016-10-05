jQuery(document).ready(function($) {
	var $modal=$('.loading-modal');

	/**
	 * populates the url field on the UCI cURL page when a year is selected (years are pre populated)
	 */
	$('#season').change(function() {
		var url=$('#season option:selected').data('url');

		$('#url').val(url);
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

	/**
	 * delete series
	 */
	$('.uci-results-series a.delete').click(function(e) {
		e.preventDefault();

		var data={
			'action' : 'delete_series',
			'series_id' : $(this).data('id')
		};

		$.post(ajaxurl, data, function(response) {
			location.reload(); // refresh
		});
	});

	/**
	 * build season weeks
	 */
	$('#build-season-weeks').click(function(e) {
		showLoader('#wpcontent');

		var data={
			'action' : 'uci_results_build_season_weeks'
		}

		$.post(ajaxurl, data, function(response) {
			hideLoader();

			$('#uci-results-actions-message').append(response);
		});
	});

	/**
	 * Remove Data button (settings) - remove all data from db
	 */
	$('#uci-results-empty-db').click(function(e) {
		var data={
			'action' : 'uci_results_empty_db',
			'security' : $('#uci-results-empty-db-nonce').val()
		}

		$.post(ajaxurl, data, function(response) {
			$('#uci-results-actions-message').append(response);
		});
	});

	/**
	 * Remove Database Tables button (settings) - remove all tables from db
	 */
	$('#uci-results-remove-db').click(function(e) {
		var data={
			'action' : 'uci_results_remove_db',
			'security' : $('#uci-results-remove-db-nonce').val()
		}

		$.post(ajaxurl, data, function(response) {
			$('#uci-results-actions-message').append(response);
		});
	});

});

// create/display loader //
function showLoader(self) {
	var loaderContainer = jQuery( '<div/>', {
		'class': 'loader-image-container'
	}).appendTo( self ).show();

	var loader = jQuery( '<img/>', {
		src: '/wp-admin/images/wpspin_light-2x.gif',
		'class': 'loader-image'
	}).appendTo( loaderContainer );
}

// remove loader //
function hideLoader() {
	jQuery('.loader-image-container').remove();
}