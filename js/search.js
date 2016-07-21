jQuery(document).ready(function($) {

	var $loader=$('.uci-results-search #search-loader');

	$('a.search-icon-go').click(function(e) {
		e.preventDefault();

		$loader.show();

		var data={
			'action' : 'uci_results_search',
			'search' : $('#uci-results-search').val()
		};

		$.post(searchAJAXObject.ajax_url, data, function(response) {
			response=$.parseJSON(response);

			$('#search-details').html(response.details); // append search details
			$('#uci-results-search-results').html(response.content); // append search results

			$loader.hide();
		});
	});

});