jQuery(document).ready(function($) {



	// main search button click //
	$('a.search-icon-go').click(function(e) {
		e.preventDefault();

		runSearch();
	});

	// type search filter (checkbox) //
	$('.uci-results-search .type').change(function() {
		var types=[];

		$('.uci-results-search .type').each(function() {
			if ($(this).prop('checked')) {
				types.push($(this).val());
			}
		});

		var data={'types' : types};

		runSearch(data);
	});

});

var $loader=jQuery('.uci-results-search #search-loader');

function runSearch(searchData) {
	$loader.show();

	var data={
		'action' : 'uci_results_search',
		'search' : jQuery('#uci-results-search').val(),
		'search_data' : searchData
	};

	jQuery.post(searchAJAXObject.ajax_url, data, function(response) {
		response=jQuery.parseJSON(response);

		jQuery('#search-details').html(response.details); // append search details
		jQuery('#uci-results-search-results').html(response.content); // append search results

		$loader.hide();
	});
}