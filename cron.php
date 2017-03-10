<?php

/**
 * uci_results_add_races function.
 *
 * @access public
 * @param string $args (default: '')
 * @return void
 */
function uci_results_add_races($args='') {
	global $wpdb, $uci_results_add_races, $uci_results_admin_pages;

	$uci_results_urls=get_object_vars($uci_results_admin_pages->config->urls);
	$season=uci_results_get_current_season();
	$url=$uci_results_urls[$season];
	$races=$uci_results_add_races->get_race_data($season, false, true, $url); // gets an output of races via the url
	$default_args=array(
		'weekly_points' => true,
		'weekly_ranks' => true,
	);
	$args=wp_parse_args($args, $default_args);
	$new_results=0;
	$email_message='';

	extract($args);

	do_action('before_uci_results_add_races_cron');

	// add race(s) to db //
	write_cron_log('[DATE: '.date('n/j/Y H:i:s').']');

	foreach ($races as $race) :
		$result=$uci_results_add_races->add_race_to_db($race, true);
		write_cron_log(strip_tags($result['message']));

		$email_message.=strip_tags($result['message'])."\n";

		if ($result['new_result'])
			$new_results++;
	endforeach;

	// only do this if we have new results //
	if ($new_results) :
		// run weekly points if need be //
		if ($weekly_points)
			uci_results_update_rider_weekly_points();

		// run weekly ranks if need be //
		if ($weekly_ranks)
			uci_results_update_rider_weekly_rank();

		// alert admin //
		$message="The uci_results_add_races cron job finished. There were $new_results new results \n";
		$message.=$email_message;
		uci_results_cron_job_email('Cron Job: UCI Results Add Races', $message);

		do_action('uci_results_add_races_cron_new_results');
	endif;

	do_action('after_uci_results_add_races_cron');

	write_cron_log('The uci_results_add_races cron job finished.');



	return;
}
add_action('uci_results_add_races', 'uci_results_add_races');

/**
 * uci_results_cron_job_email function.
 *
 * @access public
 * @param string $subject (default: '')
 * @param string $message (default: '')
 * @return void
 */
function uci_results_cron_job_email($subject='', $message='') {
	$to=	get_option('admin_email');

	wp_mail($to, $subject, $message);
}

/**
 * uci_results_update_rider_weekly_points function.
 *
 * @access public
 * @return void
 */
function uci_results_update_rider_weekly_points() {
	global $wpdb, $uci_results_rider_rankings;

	$season=uci_results_get_current_season();

	// update rider rankings //
	$rider_ids=$wpdb->get_col("SELECT id FROM $wpdb->uci_results_riders"); // get all rider ids
	$uci_results_rider_rankings->clear_db($season); // clear db for season to prevent dups

	// update rider weekly points //
	foreach ($rider_ids as $rider_id) :
		$result=$uci_results_rider_rankings->update_rider_weekly_points($rider_id, $season);
		write_cron_log(strip_tags($result));
	endforeach;

	write_cron_log('The uci_results_update_rider_weekly_points cron job finished.');

	// alert admin //
	$message="The uci_results_update_rider_weekly_points cron job finished.";
	wp_mail(get_option('admin_email'), 'Cron Job: Updated Rider Weekly Points', $message);

	return;
}
//add_action('uci_results_update_rider_weekly_points', 'uci_results_update_rider_weekly_points');

/**
 * uci_results_update_rider_weekly_rank function.
 *
 * @access public
 * @return void
 */
function uci_results_update_rider_weekly_rank() {
	global $ucicurl_races, $uci_results_rider_rankings;

	$season=uci_results_get_current_season();
	$weeks=$ucicurl_races->weeks($season);

	// update rider weekly rank //
	foreach ($weeks as $week) :
		$result=$uci_results_rider_rankings->update_rider_weekly_rankings($season, $week);
		write_cron_log(strip_tags($result));
	endforeach;

	uci_results_store_rider_rankings(); // updates our stored option

	$uci_results_rider_rankings->update_twitter();

	write_cron_log ('The uci_results_update_rider_weekly_rank cron job finished.');

	// alert admin //
	$message="The uci_results_update_rider_weekly_rank cron job finished.";
	wp_mail(get_option('admin_email'), 'Cron Job: Updated Rider Weekly Rank', $message);

	return;
}
//add_action('uci_results_update_rider_weekly_rank', 'uci_results_update_rider_weekly_rank');

// write to custom cron log function //
if ( ! function_exists('write_cron_log')) {
   function write_cron_log($log)  {
      if ( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true )."\n", 3, UCI_RESULTS_PATH.'cron.log' );
      } else {
        error_log( "$log\n", 3, UCI_RESULTS_PATH.'cron.log' );
      }
   }
}

/**
 * uci_results_cron_schedules function.
 *
 * @access public
 * @param mixed $schedules
 * @return void
 */
function uci_results_cron_schedules($schedules) {
	if (!isset($schedules['threehours'])) {
		$schedules['threehours'] = array(
			'interval' => 180*60,
			'display' => __('Once Every 3 Hours')
		);
	}

	return $schedules;
}
add_filter('cron_schedules', 'uci_results_cron_schedules');
?>