var counter=0;
var totalRaces=0;
var races=[];

jQuery(document).ready(function($) {
	
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
	
	$('#csv-data #add-results').on('click', function(e) {
		e.preventDefault();	
		
		showLoader('#wpcontent');
		
		var data = {
			'action' : 'csv_add_results',
			'data' : $('#csv-data').serialize()
		};

		$.post(ajaxurl, data, function(url) {
			hideLoader();
				
			window.location.replace(url);
		});	
	});
		
});

function addRace(race) {
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {action: 'add_race_to_db', race: race},
		success: function(response) {
			processRaceResponse(response);

			if (races.length) {
				addRace(races.shift());
			}
			else {
				finishProgressBar();
			}
		},
		error: function(response) {
			processRaceResponse(response);

			if (races.length) {
				addRace(races.shift());
			}
			else {
				finishProgressBar();
			}
		}
	});
	
}

function processRaceResponse(response) {
	jQuery('#get-race-data').append(response);

	counter++;
	updateProgressBar(counter, totalRaces);	
}	

function updateProgressBar(counter, total) {
	jQuery('#get-race-data').find('#counter span.ctr').text(counter);	
}

function finishProgressBar() {	
}