jQuery(document).ready(function($) {
	var $modal=$('.loading-modal');

	/**
	 * on our curl page, this runs our select all checkbox functionality
	 */
  $('#selectall').live('click',function(event) {
	  event.preventDefault();

	  if ($(this).hasClass('unselect')) {
		 	$(this).removeClass('unselect');
    	$('.race-checkbox').each(function() {
      	this.checked = false;  //select all checkboxes
      });
		} else {
			$(this).addClass('unselect');
    	$('.race-checkbox').each(function() {
	    	if (!$(this).is(':disabled')) {
	      	this.checked = true; //deselect all checkboxes
	      }
      });
		}
  });

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
				var data={
					'action' : 'add_race_to_db',
					'race' : races[i]
				};

				$.post(ajaxurl,data,function(response) {
					$('#get-race-data').append(response);
					$('#get-race-data').find('#counter span.ctr').text(counter);
					counter++;

					// after we are done races //
					if (counter>=races.length) {
						var endData={
							'action' : 'get_all_riders',
							'season' : '2015/2016' // NEEDS TO BE DYNAMIC
						};
						var cntr=1;

						$.post(ajaxurl,endData,function(riders) {
							riders=$.parseJSON(riders); // gets all riders

							$('#get-race-data').append('<div id="rider-counter"><span class="ctr">'+cntr+'</span> out of '+riders.length+' proccessed.');

							for (var i in riders) {
								var ridersData={
									'action' : 'add_riders_weekly_rankings',
									'season' : '2015/2016', // NEEDS TO BE DYNAMIC
									'rider' : riders[i]
								};
								// prcoess our riders for weekly rankings //
								$.post(ajaxurl,ridersData,function(response) {
									$('#get-race-data').append(response);
									$('#get-race-data').find('#rider-counter span.ctr').text(cntr);
									cntr++;
								});
							}
						});
					}
				});
			}
		});
	});
///////
	/**
	 * view db page, race results/details
	 */
	$('.race-details a').click(function(e) {
		e.preventDefault();

		if ($(this).hasClass('details')) {
			$('.race-table .race-fq').each(function() {
				$(this).removeClass('active');
			});

			$('#'+$(this).data('id')+'.race-fq').addClass('active');
		} else if ($(this).hasClass('result')) {
			$('.race-table .results').each(function() {
				$(this).removeClass('active');
			});

			$('#'+$(this).data('id')+'.results').addClass('active');
		}
	});

	/**
	 * form filter (view db for now)
	 */
	$('.form-filter select').change(function() {
		var dataName=$(this).attr('name');
		var dataValue=$(this).val();

		$('.race-table .race').each(function() {
			if (dataValue==0) {
				$(this).show();
			} else {
				$(this).hide();

				if ($(this).data(dataName)==dataValue) {
					$(this).show();
				}
			}
		});
	});

});