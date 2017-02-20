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

/**
 * run_migration function.
 * 
 * @access public
 * @return void
 */
function run_migration() {
	migrate_series()
		.then(migrate_related_races)
		.then(migrate_riders)
		.then(migrate_races)
		.then(update_series_overall_table)
		.then(update_rider_rankings_table)
		.then(run_clean_up)		
		.then(finalizeMigration);
}

/**
 * migrate_series function.
 * 
 * @access public
 * @return void
 */
function migrate_series() {
	return runMigrationAJAX('migrate_series', '.uci-results-migration .migration-steps #step-1');
}

/**
 * migrate_related_races function.
 * 
 * @access public
 * @return void
 */
function migrate_related_races() {
	return runMigrationAJAX('migrate_related_races', '.uci-results-migration .migration-steps #step-2');
}

/**
 * migrate_riders function.
 * 
 * @access public
 * @return void
 */
function migrate_riders() {
	return runMigrationAJAX('migrate_riders', '.uci-results-migration .migration-steps #step-3');
}

/**
 * migrate_races function.
 * 
 * @access public
 * @return void
 */
function migrate_races() {
	return runMigrationAJAX('migrate_races', '.uci-results-migration .migration-steps #step-4');	
}

/**
 * update_series_overall_table function.
 * 
 * @access public
 * @return void
 */
function update_series_overall_table() {
	return runMigrationAJAX('update_series_overall_table', '.uci-results-migration .migration-steps #step-5');	
}

/**
 * update_rider_rankings_table function.
 * 
 * @access public
 * @return void
 */
function update_rider_rankings_table() {
	return runMigrationAJAX('update_rider_rankings_table', '.uci-results-migration .migration-steps #step-6');	
}

/**
 * run_clean_up function.
 * 
 * @access public
 * @return void
 */
function run_clean_up() {
	return runMigrationAJAX('run_clean_up', '.uci-results-migration .migration-steps #step-7');	
}

/**
 * finalizeMigration function.
 * 
 * @access public
 * @return void
 */
function finalizeMigration() {
	jQuery('.migration-0_2_0.notice').remove(); // clear admin notice
	jQuery('<div class="notice notice-success"><p><b>Database migration complete!</b> Enjoy the awesomeness.</p></div>').insertAfter(jQuery('.uci-results.wrap h1:first')); // notify we are done
}

/**
 * runMigrationAJAX function.
 * 
 * @access public
 * @param mixed action
 * @param mixed element
 * @return void
 */
function runMigrationAJAX(action, element) {
	spinner('show', element);
	
	var data={
		'action' : action
	};
			
	return jQuery.post(ajaxurl, data).then(
		function(response) {
			console.log(action + ' run');
			
			var data=jQuery.parseJSON(response);			
			jQuery('#step-' + data.step).addClass('strike');	
			jQuery("#uci-results-progressbar").progressbar({
				value: data.percent
			});	
			
			spinner('hide', element);		
		}
	);
}

/**
 * spinner function.
 * 
 * @access public
 * @param mixed action
 * @param mixed element
 * @return void
 */
function spinner(action, element) {
	var spinner=jQuery('<img id="migration-moving-spinner" src="/wp-admin/images/wpspin_light-2x.gif" width="15px" />');

	if (action=='show') {
		jQuery(element).append(spinner);
	} else {
		jQuery('#migration-moving-spinner').remove();
	}
}