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

	/**
	 * this function is used in ViewDB class
	 */
	$('.race-table .race-link a').click(function(e) {
		e.preventDefault();
		var id=$(this).data('id');

		if ($('.race-results-full-table#'+id).is(':visible')) {
			$('.race-results-full-table#'+id).hide();
		} else {
			$('.race-results-full-table#'+id).show();
		}		
	});

	/**
	 * this function is used in ViewDB class
	 */
	$('.race-table .race-details a').click(function(e) {
		e.preventDefault();	
		var id=$(this).data('id');

		if ($('.race-fq#'+id).is(':visible')) {
			$('.race-fq#'+id).hide();
		} else {
			$('.race-fq#'+id).show();
		}
	});
	
	/**
	 * on our curl page, this runs our select all checkbox functionality
	 */
  $('#selectall').click(function(event) {
  	if(this.checked) { // check select status
    	$('.race-checkbox').each(function() { //loop through each checkbox
      	this.checked = true;  //select all checkboxes with class "checkbox1"               
      });
    }else{
    	$('.race-checkbox').each(function() { //loop through each checkbox
      	this.checked = false; //deselect all checkboxes with class "checkbox1"                       
      });         
    }
  });
    

	      
});