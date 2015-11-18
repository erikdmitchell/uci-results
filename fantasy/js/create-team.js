jQuery(document).ready(function($) {

	var $selects = $('.fc-riders-dd');

	// prevents dup user by diabiling name on other drop downs //
	$selects.on('change', function() {

	    // enable all options
	    $selects.find('option').prop('disabled', false);

	    // loop over each select, use its value to
	    // disable the options in the other selects
	    $selects.each(function() {
	       $selects.not(this)
	               .find('option[value="' + this.value + '"]')
	               .prop('disabled', true);
	    });

	});

	// populate roster (edit) //
	if (createTeamOptions.roster) {
		var rosterArr=createTeamOptions.roster.split('|');

		$selects.each(function(i) {
			$(this).val(rosterArr[i]);
		});
	}

});