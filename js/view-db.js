jQuery(document).ready(function($) {

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

	// season filter //
	$('#race-season, #race-class').change(function() {
			var data={
				'action' : 'race_filter',
				'season' : $(this).val()
			};

			$.post(ajaxurl,data,function(response) {
				$('.row.data').html(response);
			});
	});

	// class filter //
	$('#race-class').change(function() {
			var data={
				'action' : 'race_filter',
				'class' : $(this).val()
			};

			$.post(ajaxurl,data,function(response) {
				$('.row.data').html(response);
			});
	});

});