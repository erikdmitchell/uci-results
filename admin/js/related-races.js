jQuery(document).ready(function($) {
	
	// remove related race //
	$('.remove-related-race').on('click', function(e) {
		e.preventDefault();
		
		var raceID=$(this).data('id');
		
		var data={
			'action' : 'uci_remove_related_race',
			'id' : raceID,
			'rrid' : $(this).data('rrid')
		};	
		
		$.post(ajaxurl, data, function(response) {			
			if (response==1) {				
				// remove row //
				$('.uci-results-metabox.related-races #race-' + raceID).remove();
			}			
		});
	});
	
	// add related race //
	$('#add-related-race').on('click', function(e) {
		e.preventDefault();

		tb_show('Add Related Race', "#TB_inline?width=700&height=650");
		
		showLoader('#TB_ajaxContent');
		
		var raceID=$(this).data('id');
		var data={
			'action' : 'show_related_races_box'	
		};
		
		$.post(ajaxurl, data, function(response) {
			$('#TB_ajaxContent').append(response);
			
			$('#TB_ajaxContent #race_id').val(raceID);
			
			hideLoader();			
		});
		
        return false;	
	});
	
	/**
	 * related races ajax search
	 */
	$("#search-related-races").live("keyup", function(e) {
		// Set Search String
		var search_string = $(this).val();

		// Do Search
		if (search_string!=='' && search_string.length>=3) {
			$.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					action : 'search_related_races',
					query : search_string,
					id : $('#race_id').val()
				},
				success: function(response){
					$('#related-races-search-results').html(response);
				}
			});
		}

	  return false;
	});
	
	// truly add related race //
	$('body').on('click', '.add-related-race #add', function(e) {
		e.preventDefault();
		
		showLoader('#TB_ajaxContent');
		
		var data={
			'action' : 'add_related_races_to_race',
			'form' : $('form#add-races').serialize(),
			id : $('#race_id').val()
		};
		
		$.post(ajaxurl, data, function(response) {
			hideLoader();
			
			$(response).insertBefore('.row.add-race');
			
			tb_remove();		
		});		
	});
	
});