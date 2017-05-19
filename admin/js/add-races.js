jQuery(document).ready(function($) {
	/**
	 * gets an output of races via the season selected
	 */
	$('#get-races-curl').click(function(e) {
		e.preventDefault();
		
		showLoader('#wpcontent');
		
		$('#get-race-data').html('');
		
		var data={
			'action' : 'get_race_data_non_db',
			'form' : $('form.get-races').serialize()
		};

		$.post(ajaxurl,data,function(response) {
			$('#get-race-data').html(response);
			hideLoader();
		});
	});
	
	/**
	 * gets the checked races and adds them to db, returning a success (or error) message
	 */
	$('#add-races-curl-to-db').live('click',function(e) {
		e.preventDefault();

		var selected = [];
		var season=$('.url-dd#season option:selected').text();

		$('#add-races-to-db input:checked').each(function() {
			if ($(this).attr('id')!='selectall')
	    	selected.push($(this).val());
		});

		var data={
			'action' : 'prepare_add_races_to_db',
			'races' : selected
		};

		$.post(ajaxurl,data,function(response) {
			$('#get-race-data').html('');

			var races=$.parseJSON(response);
			var counter=0;

			$('#get-race-data').append('<div id="counter"><span class="ctr">'+counter+'</span> out of '+races.length+' proccessed.');

			for (var i in races) {
				var data={
					'action' : 'add_race_to_db',
					'race' : races[i]
				};

				$.post(ajaxurl,data,function(response) {
					counter++;

					$('#get-race-data').append(response);
					$('#get-race-data').find('#counter span.ctr').text(counter);
				});
			}
		});
	});
	
	// upload startlist button - opens media uploader, runs ajax on insert into post //
	$('.button#add-file').click(function(e) {
		e.preventDefault();
	
		var _custom_media = true;
	    	var _orig_send_attachment = wp.media.editor.send.attachment;
	    	var send_attachment_bkp = wp.media.editor.send.attachment;
	    	var button = $(this);
	
	   	 _custom_media = true;
	
	   	 wp.media.editor.send.attachment = function(props, attachment) {
	      		if (_custom_media) {
		    		$('input#file').val(attachment.url);				
	      		} else {
					return _orig_send_attachment.apply( this, [props, attachment] );
	      		}
	    	}
	
	    	wp.media.editor.open(button);
	
	    	return false;
	});	
	
	// ajax load results csv //
	$('.process-results #process-results').on('click', function(e) {
		e.preventDefault();
		
		var raceID=$('form.process-results #race-id').val();
		var raceSearchID=$('form.process-results #races-list').val();		

		var data={
			'action' : 'process_csv_results',
			'form' : $('form.process-results').serializeArray()
		};
		
		if (raceID == '') {
			raceID = raceSearchID[0];
		}

		$.post(ajaxurl, data, function(response) {
			$('form#csv-data #race_id').val(raceID);
			
			$('span#csv-data-form-table').html(response);
			
			$('.uci-results .button#add-results').show();
		});		
	});
	
	// race id search //
	$('input#race-search').live('keyup', function() {
		if (this.value.length < 3)
			return;
			
		var data={
			'action' :	'race_id_search',
			'string' : this.value	
		};

		$.post(ajaxurl, data, function(response) {
			$('.process-results #race-search-list').html(''); // clear
			$('.process-results #race-search-list').html(response); // add data
		});
	});
		
});