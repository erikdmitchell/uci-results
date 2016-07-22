jQuery(document).ready(function($) {

	$('.uci-results-pagination a').click(function(e) {
		if ($('.uci-results-pagination').data('ajax'))
			e.preventDefault();

console.log('a');
console.log($('.uci-results-pagination').data());
	});

});