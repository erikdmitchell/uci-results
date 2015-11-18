jQuery(document).ready(function($) {

	// prevents dup user by diabiling name on other drop downs //
	var $selects = $('.fc-riders-dd');

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

});