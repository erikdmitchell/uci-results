jQuery(document).ready(function($) {

	var $selects = $('.fc-riders-dd');

	// prevents dup user by diabiling name on other drop downs //
	$selects.on('change', function() {
    // enable all options
    $selects.find('option').prop('disabled', false);

    // loop over each select, use its value to disable the options in the other selects
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
			$(this).val(rosterArr[i]); // add val

			// disable on others //
			$selects.each(function() {
		    $selects.not(this)
		  		.find('option[value="' + rosterArr[i] + '"]')
		  		.prop('disabled', true);
		  });
		});

		$('.fantasy-cycling-create-team #submit').val('Edit Team');
	}

	// add/remove rider modal functionalty //
	// set up +/- buttons //
	var riderID=0;

	if (createTeamOptions.roster.length!=0) {
		// we have things to edit
	}
	// enable our + button //
	$('.fc-team-roster .add-remove-rider .add-remove-btn').each(function(i) {
		$(this).find('.add').show();
	});
	// set rider on click before we launch modal //
	$('.fc-team-roster .add-remove-rider .add-remove-btn').click(function(i) {
		riderID=$(this).parents('.add-remove-rider').attr('id');
	});
	// add rider button click inside modal //
	$('#add-rider-modal .rider-list .rider a').click(function(e) {
		e.preventDefault();
console.log(riderID);
console.log($(this).data('name'));
		$('#add-rider-modal').modal('hide'); // hide modal
		$('#'+riderID+' .rider-name').html($(this).data('name'));
	});

});