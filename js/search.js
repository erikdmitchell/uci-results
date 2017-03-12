var $loader='';

jQuery(document).ready(function($) {

	$loader=jQuery('.uci-results-search #search-loader');

	// main search button click //
	$('a.search-icon-go').click(function(e) {
		e.preventDefault();

		// we need this from the type filter //
		var types=[];

		$('.uci-results-search .type').each(function() {
			if ($(this).prop('checked')) {
				types.push($(this).val());
			}
		});

		var data={'types' : types};

		//clearSearchFilters();
		runSearch(data);
	/*	
jQuery.ajax({
        type : 'post',
        url : myAjax.ajaxurl,
        data : {
            action : 'load_search_results',
            query : query
        },
        beforeSend: function() {
            $input.prop('disabled', true);
            $content.addClass('loading');
        },
        success : function( response ) {
            $input.prop('disabled', false);
            $content.removeClass('loading');
            $content.html( response );
        }
    });*/		
		
	});

	// type search filter (checkbox) //
	$('.uci-results-search .type').change(function() {
		var types=[];

		$('.uci-results-search .type').each(function() {
			if ($(this).prop('checked')) {
				types.push($(this).val());
			}
		});

		// hide all extra filters //
		$('#races-search-filters').hide();
		$('#riders-search-filters').hide();

		// if we have one type, show that extra filters, two, show all fliters //
		if (types.length==1) {
			if (types[0]=='races') {
				$('#races-search-filters').show();
			} else {
				$('#riders-search-filters').show();
			}
		} else if (types.length>1) {
			$('#races-search-filters').show();
		}

		var data={'types' : types};

		runSearch(data);
	});

	// search filter select change //
	$('.search-filters select').change(function() {
		// we need this from the type filter //
		var types=[];

		$('.uci-results-search .type').each(function() {
			if ($(this).prop('checked')) {
				types.push($(this).val());
			}
		});

		var name=$(this).attr('name');
		var data={
			'types' : types,
			name : $(this).val()
		};

		runSearch(data);
	});

});

function runSearch(searchData) {
	$loader.show();

	var data={
		'action' : 'uci_results_search',
		'search' : jQuery('#uci-results-search').val(),
		'search_data' : searchData
	};

	jQuery.post(searchAJAXObject.ajax_url, data, function(response) {
		response=jQuery.parseJSON(response);
console.log(response);		
		jQuery('#uci-results-search-results').html(response); // append search results

		$loader.hide();
	});
}

function clearSearchFilters() {
	jQuery('.uci-results-search .search-filters').find('input:text, input:password, input:file, textarea').val('');
	jQuery('.uci-results-search .search-filters').find('select').val(0);
	jQuery('.uci-results-search .search-filters').find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');

	resetFilterSection();
}

function resetFilterSection() {
	jQuery('#races-search-filters').hide();
	jQuery('#riders-search-filters').hide();
}