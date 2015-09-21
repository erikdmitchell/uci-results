jQuery(document).ready(function($) {
	var $modal=$('.loading-modal');

/*
	$('#get-race-data').click(function(){
console.log('a');
		$modal.show();
		var data={
			action:'get-data' ,
			type:'all'
		};
		$.post(ajaxurl,data, function(response) {
	  	response=$.parseJSON(response);
	  	$('.uci-curl .data-results').html(response);
	  	//console.log(response);
	  	$modal.hide();
		});
	});
*/

	/**
	 * on our curl page, this runs our select all checkbox functionality
	 */
  $('#selectall').live('change',function(event) {
  	if (this.checked) {
    	$('.race-checkbox').each(function() {
      	this.checked = true;  //select all checkboxes
      });
    } else {
    	$('.race-checkbox').each(function() {
      	this.checked = false; //deselect all checkboxes
      });
    }
  });

	/**
	 * populates the url field on the UCI cURL page when a year is selected (years are pre populated)
	 */
	$('#get-race-season').change(function() {
		$('#url').val($(this).val());
	});

	/**
	 * gets an out put of races via the season selected
	 */
	$('#get-races-curl').click(function(e) {
		$modal.show();
		$('#get-race-data').html('');
		e.preventDefault();

		var data={
			'action' : 'get_race_data_non_db',
			'url' : $('form.get-races').find('#url').val(),
			'limit' : $('form.get-races').find('#limit').val()
		};

		$.post(ajaxurl,data,function(response) {
			$('#get-race-data').html(response);
			$modal.hide();
		});
	});

	/**
	 * gets the checked races and adds them to db, returning a success (or error) message
	 */
	$('#add-races-curl-to-db').live('click',function(e) {
		e.preventDefault();

		var selected = [];
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
			var counter=1;

			$('#get-race-data').append('<div id="counter"><span class="ctr">'+counter+'</span> out of '+races.length+' proccessed.');

			for (var i in races) {
				var data={
					'action' : 'add_race_to_db',
					'race' : races[i]
				};

				$.post(ajaxurl,data,function(response) {
					$('#get-race-data').append(response);
					$('#get-race-data').find('#counter span.ctr').text(counter);
					counter++;
				});
			}

		});
	});

	/**
	 * view db page, race results/details
	 */
	$('.race-details a').click(function(e) {
		e.preventDefault();

		if ($(this).hasClass('details')) {
			$('.race-table .race-fq').each(function() {
				$(this).removeClass('active');
			});

			$('#'+$(this).data('id')+'.race-fq').addClass('active');
		} else if ($(this).hasClass('result')) {
			$('.race-table .results').each(function() {
				$(this).removeClass('active');
			});

			$('#'+$(this).data('id')+'.results').addClass('active');
		}
	});

});