<?php

/**
 * uci_results_add_races function.
 *
 * @access public
 * @return void
 */
function uci_results_add_races() {
	global $wpdb, $uci_results_add_races, $uci_results_rider_rankings, $ucicurl_races;

	$road_test_url='http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=102&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=490&StartDateSort=20151228&EndDateSort=20161023&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8';
	$season=2016;
	$races=$uci_results_add_races->get_race_data(false, false, true, $road_test_url); // gets an output of races via the url

	// add race(s) to db //
	foreach ($races as $race) :
		$result=$uci_results_add_races->add_race_to_db($race);
		write_cron_log(strip_tags($result));
	endforeach;

	// alert admin //
	$message="The uci_results_add_races cron job finished. It added races and updated rankings.";
	wp_mail('erikdmitchell@gmail.com', 'Cron Job: UCI Results Add Races', $message);

	return;
}
add_action('uci_results_add_races', 'uci_results_add_races');

function uci_results_update_rider_weekly_points() {
	// update rider rankings //
	$rider_ids=$wpdb->get_col("SELECT id FROM $wpdb->ucicurl_riders"); // get all rider ids
	$weeks=$ucicurl_races->weeks($season);

	// update rider weekly points //
	foreach ($rider_ids as $rider_id) :
		$result=$uci_results_rider_rankings->update_rider_weekly_points($rider_id, $season);
		write_cron_log(strip_tags($result));
	endforeach;
}
add_action('uci_results_update_rider_weekly_points', 'uci_results_update_rider_weekly_points');

function uci_results_update_rider_weekly_rank() {
	// update rider weekly rank //
	foreach ($weeks as $week) :
		$result=$uci_results_rider_rankings->update_rider_weekly_rankings($season, $week);
		write_cron_log(strip_tags($result));
	endforeach;
}
add_action('uci_results_update_rider_weekly_rank', 'uci_results_update_rider_weekly_rank');




if ( ! function_exists('write_cron_log')) {
   function write_cron_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true ), 3, UCICURL_PATH.'cron.log' );
      } else {
        error_log( "$log\n", 3, UCICURL_PATH.'cron.log' );
      }
   }
}
?>