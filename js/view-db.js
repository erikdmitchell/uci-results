jQuery(document).ready(function($) {

	var $loader=$('#loader');

	// race search //
	$("input#race-search").live("keyup", function(e) {
		// Set Search String
		var search_string = $(this).val();

		// Do Search
		if (search_string !== '' && search_string.length>=3) {
			var data={
				'action' : 'race_search',
				'search' : search_string
			};

			$.post(ajaxurl,data,function(response) {
				$('#race-search-results-text').show();
				$('#race-search-results-text').html(response);
			});
		}

		return false;
	});

	// race filters //
	$('#race_filters .season, #race_filters .class, #race_filters .nat').change(function() {
		var data={
			'action' : 'race_filter',
			'form' : $('#race_filters').serialize()
		};

		$.post(ajaxurl,data,function(response) {
			$('.row.data').html(response);
		});
	});

	// form reset //
	$('#clear-race-search').click(function(e) {
		e.preventDefault();

		$('#race-search').val(''); // clear search box
		$('#race-search-results-text').html('').hide(); // clear results box and hide

		return false;
	});

	// rider search //
	$("input#rider-search").live("keyup", function(e) {
		// Set Search String
		var search_string = $(this).val();

		// Do Search
		if (search_string !== '' && search_string.length>=3) {
			var data={
				'action' : 'rider_search',
				'search' : search_string
			};

			$.post(ajaxurl,data,function(response) {
				$('#rider-search-results-text').show();
				$('#rider-search-results-text').html(response);
			});
		}

		return false;
	});

	// race click //
	$('.view-db-single-rider .race a').click(function() {
		window.location=$(this).attr('href');
	});

	// rider table sort //
	$(".tablesorter").tablesorter();

	// rider filters //
	$('#rider_filters .season, #rider_filters .class, #rider_filters .nat').change(function() {
		$loader.show();
		var data={
			'action' : 'rider_filter',
			'form' : $('#rider_filters').serialize()
		};

		$.post(ajaxurl,data,function(response) {
			$('.row.data').html(response);
			$loader.hide();
		});
	});

	// check all
  $('#checkall').live('click',function(event) {
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

  // add rider uci points button
  $('#add_rider_season_uci_points').live('click',function(e) {
	  e.preventDefault();

		$loader.show();

		var values=[];

		// get checked values //
		$('#race-filter .race-checkbox').each(function () {
			if ($(this).is(':checked')) {
				values.push($(this).val());
			}
		});

		$('#get-race-rider').html('');

		var data={
			'action' : 'add_rider_season_uci_points',
			'value' : values
		};

		$.post(ajaxurl,data,function(response) {
			$('#get-race-rider').html(response);
			$loader.hide();
		});
	});

	// update rider sos //
  $('#update_rider_sos').live('click',function(e) {
	  e.preventDefault();

		$loader.show();

		// get checked values //


		$('#get-race-rider').html('');

		var data={
			'action' : 'update_season_sos',
			'season' : $('#rider_filters .season-dd').val(),
			'rider' : ''
		};

		$.post(ajaxurl,data,function(response) {
			$('#get-race-rider').html(response);
			$loader.hide();
		});
	});

});