jQuery(document).ready(function($) {

	var riderID=0;

	// disable normal input click for name, make it add/edit rider
	$('.fc-team-roster .rider-name-input').click(function(e) {
		e.preventDefault();

		riderID=$(this).parents('.add-remove-rider').attr('id');
		$('#add-rider-modal').modal('show'); // show modal
	});

	// edit team //
	if (createTeamOptions.roster.length!=0) {
		var roster=createTeamOptions.roster;

		for (var i in roster) {
			$('.fc-team-roster #rider-'+i+' .add-remove-btn .remove').show(); // show remove button
			$('.fc-team-roster #rider-'+i+' .rider-name-input').val(roster[i].name); // add name
			// add rest of info
			$('.fc-team-roster #rider-'+i+' .last-year-finish').html(roster[i].last_year);
			$('.fc-team-roster #rider-'+i+' .last-week-finish').html(roster[i].last_week);
			$('.fc-team-roster #rider-'+i+' .rank').html(roster[i].rank);
			$('.fc-team-roster #rider-'+i+' .season-points .c2').html(roster[i].points.c2);
			$('.fc-team-roster #rider-'+i+' .season-points .c1').html(roster[i].points.c1);
			$('.fc-team-roster #rider-'+i+' .season-points .cc').html(roster[i].points.cc);
			$('.fc-team-roster #rider-'+i+' .season-points .cn').html(roster[i].points.cn);
			$('.fc-team-roster #rider-'+i+' .season-points .cdm').html(roster[i].points.cdm);
			$('.fc-team-roster #rider-'+i+' .season-points .cm').html(roster[i].points.cm);
		}
	}

	// enable our + button for empty slots //
	$('.fc-team-roster .add-remove-rider .add-remove-btn').each(function(i) {
		if ($(this).parents('.add-remove-rider').find('.rider-name-input').val()=='') {
			$(this).find('.add').show();
		}
	});

	// set rider on click before we launch modal //
	$('.fc-team-roster .add-remove-rider .add-remove-btn').click(function(i) {
		riderID=$(this).parents('.add-remove-rider').attr('id');

		if ($(this).find('.remove').is(':visible')) {
			var $parent=$(this).parents('.add-remove-rider');
			$parent.find('.rider-name-input').val('');
			$parent.find('.last-year-finish').html('');
			$parent.find('.last-week-finish').html('');
			$parent.find('.rank').html('');
			$parent.find('.season-points .sub-col div').each(function() {
				$(this).html('');
			});
			$(this).find('.remove').hide();
			$(this).find('.add').show();
		}
	});

	// add rider button click inside modal //
	$('#add-rider-modal .rider-list .rider a').click(function(e) {
		e.preventDefault();

		var riderData=$(this).data().rider;

		$('#add-rider-modal').modal('hide'); // hide modal
		$('#'+riderID+' .rider-name-input').val(riderData.name);
		$('#'+riderID+' .last-year-finish').html(riderData.last_year);
		$('#'+riderID+' .last-week-finish').html(riderData.last_week);
		$('#'+riderID+' .rank').html(riderData.rank);
		$('#'+riderID+' .season-points .c2').html(riderData.points.c2);
		$('#'+riderID+' .season-points .c1').html(riderData.points.c1);
		$('#'+riderID+' .season-points .cc').html(riderData.points.cc);
		$('#'+riderID+' .season-points .cn').html(riderData.points.cn);
		$('#'+riderID+' .season-points .cdm').html(riderData.points.cdm);
		$('#'+riderID+' .season-points .cm').html(riderData.points.cm);

		$('#'+riderID+' .add-remove-btn .add').hide();
		$('#'+riderID+' .add-remove-btn .remove').show();
	});

});