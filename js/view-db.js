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

});