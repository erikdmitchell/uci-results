jQuery(document).ready(function($) {

	var fpConfig={
		enableTime : true
	};

	// setup date picker and enable time //
	$('.fc-datetimepicker').flatpickr(fpConfig);
	
	// add additional race dates //
	$('#fc-race-stages #add-race-date').click(function(e) {
		e.preventDefault();
		
		var count=$('#fc-race-stages .date').length;
		var $last=$('#fc-race-stages .date:last');
		var $clone=$last.clone();
		var prevDate=$last.find('.fc-datetimepicker').val();

		// add default stage values //
		if (count == 1) {
			$last.find('.stage').val('Stage ' + count);
		}
		
		// update stage //
		var stage=count + 1;
		$clone.find('.stage').val('Stage ' + stage);
		
		// if date set val plus one day, else clear //
		if (prevDate != '') {
			newDate=new Date(prevDate).fp_incr(1);			
		} else {
			newDate='';	
		}
		
		fpConfig.defaultDate=newDate; // update config
		
		$clone.find('.fc-datetimepicker').val('').flatpickr(fpConfig); // clear value and setup flatpickr
		$last.after($clone);	
	});
	
});

