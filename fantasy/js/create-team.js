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

		var riderData=$(this).data().rider;

		$('#add-rider-modal').modal('hide'); // hide modal
		$('#'+riderID+' .rider-name').html(riderData.name+' ('+riderData.country+')');
		$('#'+riderID+' .last-year-finish').html(riderData.last_year);
		$('#'+riderID+' .last-week-finish').html(riderData.last_week);
		$('#'+riderID+' .rank').html(riderData.rank);
		$('#'+riderID+' .season-points .c2').html(riderData.points.c2);
		$('#'+riderID+' .season-points .c1').html(riderData.points.c1);
		$('#'+riderID+' .season-points .cc').html(riderData.points.cc);
		$('#'+riderID+' .season-points .cn').html(riderData.points.cn);
		$('#'+riderID+' .season-points .cdm').html(riderData.points.cdm);
		$('#'+riderID+' .season-points .cm').html(riderData.points.cm);
	});

});