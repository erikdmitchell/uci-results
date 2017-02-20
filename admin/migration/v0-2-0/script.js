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

function migrate_series() {
	return runMigrationAJAX('migrate_series');
}

function migrate_related_races() {
console.log('b');
	return 'b';
}

function migrate_riders() {
console.log('c');
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