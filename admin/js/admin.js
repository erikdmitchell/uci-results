jQuery(document).ready(function($) {
	var $modal=$('.loading-modal');


	/**
	 * select all
	 */
	$('#select-all').live('click',function(event) {
	  event.preventDefault();

	  if ($(this).hasClass('unselect')) {
		 	$(this).removeClass('unselect');
    	$('.check-column > input').each(function() {
      	this.checked = false;  //select all checkboxes
      });
		} else {
			$(this).addClass('unselect');
    	$('.check-column > input').each(function() {
	    	if (!$(this).is(':disabled')) {
	      	this.checked = true; //deselect all checkboxes
	      }
      });
		}
	});

	/**
	 * Remove Data button (settings) - remove all data from db
	 */
	$('#uci-results-remove-data').click(function(e) {
		e.preventDefault();
				
		var data={
			'action' : 'uci_results_remove_data',
			'security' : $('#uci-results-remove-data-nonce').val()
		}

		$.post(ajaxurl, data, function(response) {
			$('#uci-results-actions-message').append(response);
		});
	});

	$('#rankings-filter #season').change(function(e) {
		showLoader('#wpcontent');

		e.preventDefault();

		var data={
			'action' : 'uci_results_rider_rankings_dropdown',
			'season' : $(this).val()
		};

		$.post(ajaxurl, data, function(response) {
			$('#rankings-filter #week').html(response); //add our new weeks
			$('table.riders').html(''); // clear table

			hideLoader();
		});
	});
	
	/* admin menu mod */
	if ($('body.post-type-riders').length || $('body.post-type-races').length) {
		$('#toplevel_page_uci-results').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');	
	}

});

// create/display loader //
function showLoader(self) {
	var loaderContainer = jQuery( '<div/>', {
		'class': 'loader-image-container'
	}).appendTo( self ).show();

	var loader = jQuery( '<img/>', {
		src: '/wp-admin/images/wpspin_light-2x.gif',
		'class': 'loader-image'
	}).appendTo( loaderContainer );
}

// remove loader //
function hideLoader() {
	jQuery('.loader-image-container').remove();
}