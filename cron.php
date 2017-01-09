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
	
	$season=uci_results_get_current_season();
	$new_results=0;
	
	if (!$season || $season=='') :
		echo 'No season found.';
		
		return;
	endif;

	$url=uci_results_get_season_url($season);

	if (!$url) :
		echo 'No url found';
	
		return;
	endif;

	$races=$uci_results_add_races->get_race_data($season, false, true, $url); // gets an output of races via the url

	if (empty($races)) :
		echo  'No races found.';
		
		return;
	endif;

	do_action('before_uci_results_add_races_cron');
	
	//write_cron_log('[DATE: '.date('n/j/Y H:i:s').']');
	echo '[DATE: '.date('n/j/Y H:i:s').']<br>';
		
	foreach ($races as $race) :
		$process_race_output=uci_results_process_race($race);
		$output=uci_results_format_process_race_output($process_race_output);
		
		//write_cron_log(strip_tags($result['message']));
		echo "$output<br>";

		//$email_message.=strip_tags($result['message'])."\n";		
	endforeach;

/*
	$default_args=array(
		'weekly_points' => true,
		'weekly_ranks' => true,
	);
	$args=wp_parse_args($args, $default_args);
	
	$email_message='';

	

	// only do this if we have new results //
	if ($new_results) :
		uci_results_build_season_weeks($season); // update season weeks

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
	*/
	do_action('after_uci_results_add_races_cron');

	//write_cron_log('The uci_results_add_races cron job finished.');

	return;
}
add_action('uci_results_add_races', 'uci_results_add_races');


function uci_results_get_season_url($season='') {
	global $uci_results_admin_pages;
	
	if (empty($season))
		$season=uci_results_get_current_season();
		
	if (!isset($uci_results_admin_pages->config->urls->$season) || empty($uci_results_admin_pages->config->urls->$season))
		return false;
		
	return $uci_results_admin_pages->config->urls->$season;
}

function uci_results_process_race($race='') {
	global $uci_results_add_races;
	
	if (empty($race))
		return array('warning' => 'No race passed');

	$code=$uci_results_add_races->build_race_code(array('event' => $race->event, 'date' => $race->date));

	if (!$code)
		return array('warning' => "Code for $race->event not created!");

	// add to db //
	if (!$uci_results_add_races->check_for_dups($code)) :
		$formatted_result=$uci_results_add_races->add_race_to_db($race);
		$result=strip_tags($formatted_result);
		
		return array('success' => $result);
	else :
		return array('warning' => "Already in db. ($code)");
	endif;
	
	return;
}

function uci_results_format_process_race_output($arr=array()) {
	if (empty($arr))
		return;
	
	foreach ($arr as $type => $message) :
		switch ($type) :
			case 'warning':
				return "Warning: $message";
				break;
			case 'success':
				return "Success: $message";
				break;
			default:
				return "$message";
		endswitch;
	endforeach;
}

function uci_results_process_race_is_success($arr=array()) {
	if (empty($arr))
		return false;
	
	foreach ($arr as $type => $message) :
		if ($type=='success')
			return true;
	endforeach;	
	
	return false;
}



/**
 * uci_results_cron_job_email function.
 *
 * @access public
 * @param string $subject (default: '')
 * @param string $message (default: '')
 * @return void
 */
function uci_results_cron_job_email($subject='', $message='') {
	$to=get_option('admin_email');

	wp_mail($to, $subject, $message);
}

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
	$rider_ids=$wpdb->get_col("SELECT id FROM $wpdb->uci_results_riders"); // get all rider ids
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