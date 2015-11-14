jQuery(document).ready(function($) {

	$('.add-start-list #race').change(function() {
		$('#race-id').val($(this).val());
		getStartList($(this).val());
	});

	$('.add-race .date').datepicker({
		changeMonth: true,
		changeYear: true
	});

	// append db data to results form //
	$('#add-results #race').change(function() {
		var id=$(this).val();

		for (var i in FCAdminWPOptions.FCRaces) {
			if (FCAdminWPOptions.FCRaces[i].id==id) {
				$('#add-results #name').val(FCAdminWPOptions.FCRaces[i].name);
				$('#add-results #season').val(FCAdminWPOptions.FCRaces[i].season);
				$('#add-results #type').val(FCAdminWPOptions.FCRaces[i].type);
				$('#add-results #date').val(FCAdminWPOptions.FCRaces[i].race_start);
				$('#add-results #series').val(FCAdminWPOptions.FCRaces[i].series);
				$('#add-results #code').val(FCAdminWPOptions.FCRaces[i].code);
			}
		}
	});

});

function getStartList(id) {
	var data={
		'action': 'load_start_list',
		'id': id
	};

	jQuery.post(ajaxurl,data,function(race) {
		race=jQuery.parseJSON(race);
		loadStartList(race.start_list);
	});
}

function loadStartList(startList) {
	var $startList=jQuery('#add-start-list .start-list');

	$startList.find('.sl-riders').each(function() {
		if (jQuery.inArray(jQuery(this).val(),startList)!=-1) {
			jQuery(this).prop('checked',true);

			// remove from starts list so we are left with addons //
			for (var key in startList) {
				if (startList[key]==jQuery(this).val()) {
					startList.splice(key,1);
				}
			}

		}
	});

	// append addons //
	for (var key in startList) {
		var cb='<input type="checkbox" name="riders[]" class="sl-riders" value="'+startList[key]+'" checked="checked" /> '+startList[key]+'<br />';
		$startList.find('.last-col').append(cb);
	}

}