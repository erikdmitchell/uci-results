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
	$races=$uci_results_add_races->get_race_data(false, false, true, $url); // gets an output of races via the url
	$default_args=array(
		'weekly_points' => true,
		'weekly_ranks' => true,
	);
	$args=wp_parse_args($args, $default_args);

	extract($args);

	// add race(s) to db //
	write_cron_log('[DATE: '.date('n/j/Y H:i:s').']');

	foreach ($races as $race) :
		$result=$uci_results_add_races->add_race_to_db($race);
		write_cron_log(strip_tags($result));
	endforeach;

	uci_results_build_season_weeks($season); // update season weeks

	// run weekly points if need be //
	if ($weekly_points)
		uci_results_update_rider_weekly_points();

	// run weekly ranks if need be //
	if ($weekly_ranks)
		uci_results_update_rider_weekly_rank();

	write_cron_log('The uci_results_add_races cron job finished.');

	// alert admin //
	$message="The uci_results_add_races cron job finished.";
	wp_mail('erikdmitchell@gmail.com', 'Cron Job: UCI Results Add Races', $message);

	return;
}
add_action('uci_results_add_races', 'uci_results_add_races');

/**
 * uci_results_update_rider_weekly_points function.
 *
 * @access public
 * @return void
 */
function uci_results_update_rider_weekly_points() {
	global $wpdb, $ucicurl_races, $uci_results_rider_rankings;

	$season=uci_results_get_current_season();

	// update rider rankings //
	$rider_ids=$wpdb->get_col("SELECT id FROM $wpdb->ucicurl_riders"); // get all rider ids
	$weeks=$ucicurl_races->weeks($season);
	$uci_results_rider_rankings->clear_db($season); // clear db for season to prevent dups

	// update rider weekly points //
	foreach ($rider_ids as $rider_id) :
		$result=$uci_results_rider_rankings->update_rider_weekly_points($rider_id, $season);
		write_cron_log(strip_tags($result));
	endforeach;

	write_cron_log('The uci_results_update_rider_weekly_points cron job finished.');

	// alert admin //
	$message="The uci_results_update_rider_weekly_points cron job finished.";
	wp_mail('erikdmitchell@gmail.com', 'Cron Job: Updated Rider Weekly Points', $message);

	return;
}
add_action('uci_results_update_rider_weekly_points', 'uci_results_update_rider_weekly_points');

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

	write_cron_log ('The uci_results_update_rider_weekly_rank cron job finished.');

	// alert admin //
	$message="The uci_results_update_rider_weekly_rank cron job finished.";
	wp_mail('erikdmitchell@gmail.com', 'Cron Job: Updated Rider Weekly Rank', $message);

	return;
}
add_action('uci_results_update_rider_weekly_rank', 'uci_results_update_rider_weekly_rank');




if ( ! function_exists('write_cron_log')) {
   function write_cron_log($log)  {
      if ( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true )."\n", 3, UCICURL_PATH.'cron.log' );
      } else {
        error_log( "$log\n", 3, UCICURL_PATH.'cron.log' );
      }
   }
}
?>