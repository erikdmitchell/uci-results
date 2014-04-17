jQuery(document).ready(function($) {
	var $modal=$('.loading-modal');
	
	$('#get-race-data').click(function(){
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

	$('.race-table .race-link a').click(function() {
		$modal.show();
		var data={ 
			action:'get-data',
			type:'race',
			link:$(this).data('link')
		};
		$.post(ajaxurl,data, function(response) {
	  	response=$.parseJSON(response);
	  	$('.uci-curl .data-results').html(response);
	  	//console.log(response);
	  	$modal.hide();
		});
	});

	$('.race-table .race-details a').click(function() {
		$modal.show();
		var data={ 
			action:'get-data',
			type:'race-data',
			id:$(this).data('id')
		};	
		$.post(ajaxurl,data, function(response) {		
	  	response=$.parseJSON(response);
	  	$('#race-data-'+data.id).html(response);
	  	console.log(response);
	  	$modal.hide();
		});
	});
	      
});