jQuery(document).ready(function($) {
	
	// set progress bar //
	$("#uci-results-progressbar").progressbar({
		value: 0
	});
	
	// begin button click //
	$('#uci-results-start-migration').click(function(e) {
		e.preventDefault();
		
		run_migration();

	});
});

function run_migration() {
	migrate_series().then(migrate_related_races).then(migrate_riders).then(function() {
    	console.log("successful");
	});		
}

/**
 * migrate_series function.
 * 
 * @access public
 * @return void
 */
function migrate_series() {
	return runMigrationAJAX('migrate_series');
}

/**
 * migrate_related_races function.
 * 
 * @access public
 * @return void
 */
function migrate_related_races() {
	return runMigrationAJAX('migrate_related_races');
}

function migrate_riders() {
	return runMigrationAJAX('migrate_riders');
}

function migrate_races() {
console.log('d');	
}

function update_series_overall_table() {
console.log('e');	
}

function update_rider_rankings_table() {
console.log('f');	
}

function runMigrationAJAX(action) {
	var data={
		'action' : action
	};
		
	return jQuery.post(ajaxurl, data).then(
		function(response) {
			var data=jQuery.parseJSON(response);			
			jQuery('#step-' + data.step).addClass('strike');	
			jQuery("#uci-results-progressbar").progressbar({
				value: data.percent
			});			
		}
	);
}