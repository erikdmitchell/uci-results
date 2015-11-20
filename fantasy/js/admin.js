jQuery(document).ready(function($) {

	$('.run-fake-teams #race').change(function() {
		$('.run-fake-teams #race-id').val($(this).val());
	});

	$('.add-start-list #race').change(function() {
		$('#race-id').val($(this).val());
		getStartList($(this).val());
	});

	$('#setup-races .date').datepicker({
		changeMonth: true,
		changeYear: true
	});

	// append db data to results form //
	$('#setup-races #race').change(function() {
		var id=$(this).val();

		for (var i in FCAdminWPOptions.FCRaces) {
			if (FCAdminWPOptions.FCRaces[i].id==id) {
				$('#setup-races #name').val(FCAdminWPOptions.FCRaces[i].name);
				$('#setup-races #season').val(FCAdminWPOptions.FCRaces[i].season);
				$('#setup-races #type').val(FCAdminWPOptions.FCRaces[i].type);
				$('#setup-races #date').val(FCAdminWPOptions.FCRaces[i].race_start);
				$('#setup-races #series').val(FCAdminWPOptions.FCRaces[i].series);
				$('#setup-races #codes-from-db').val(FCAdminWPOptions.FCRaces[i].code);
			}
		}
	});

	// race search //
	$("input#fantasy-race-search").live("keyup", function(e) {
		// Set Search String
		var search_string = $(this).val();

		// Do Search
		if (search_string !== '' && search_string.length>=3) {
			var data={
				'action' : 'fantasy_race_search',
				'search' : search_string
			};

			$.post(ajaxurl,data,function(response) {
				$('#fantasy-search-results-text').show();
				$('#fantasy-search-results-text').html(response);
			});
		}

		return false;
	});

	// clear search //
	$('#clear-race-search').click(function() {
		$('#fantasy-search-results-text').html('').hide();
	});

	// search/code onclick //
	$('#fantasy-search-results-text .race .name a').live('click', function(e) {
		e.preventDefault();
		$('#fantasy-race-search').val($(this).attr('href'));
		$('#fantasy-search-results-text').html('').hide();
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
		} else {
			jQuery(this).prop('checked',false);
		}
	});

	// append addons //
	for (var key in startList) {
		var cb='<input type="checkbox" name="riders[]" class="sl-riders" value="'+startList[key]+'" checked="checked" /> '+startList[key]+'<br />';
		$startList.find('.last-col').append(cb);
	}

}