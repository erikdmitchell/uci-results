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
			$('#uci-results-search-results').html(response);

			$loader.hide();
		});
	});

});