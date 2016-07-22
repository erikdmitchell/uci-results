jQuery(document).ready(function($) {

	$('.uci-results-pagination a').click(function(e) {
		if ($('.uci-results-pagination').data('ajax'))
			e.preventDefault();

		var data={
			'action' : 'uci_results_pagination',
			'data' : $('.uci-results-pagination').data('details')
		};

		$.post(paginationOptions.ajax_url, data, function(response) {
console.log(response);
		});
	});

});