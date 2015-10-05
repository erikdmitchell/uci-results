jQuery(document).ready(function($) {

	$('#previous-rider-seasons,#previous-race-seasons').change(function() {
		var url='';
		if ($(this).hasClass('riders')) {
			url='/uci-cross-rankings/rider-rankings/?season='+$(this).val();
		} else if ($(this).hasClass('races')) {
			url='/uci-cross-rankings/race-rankings/?season='+$(this).val();
		}
		window.location=url;
	});

});