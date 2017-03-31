<?php
global $uci_results_automation;
	
class UCIResultsAutomation {
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action('uci_results_add_races', array($this, 'add_races'));
	}

	/**
	 * add_races function.
	 * 
	 * @access public
	 * @param string $season (default: '')
	 * @param string $output (default: 'raw')
	 * @return void
	 */
	public function add_races($season='', $output='raw') {
		global $uci_results_add_races;
		
		$new_results=0;
		
		if (!$season || $season=='')
			$season=uci_results_get_current_season();
	
		$url=$this->get_season_url($season);
	
		if (!$url) :
			$this->admin_output('No url found.', 'error', $output);
			return;
		endif;
	
		$races=$uci_results_add_races->get_race_data($season, false, true, $url); // gets an output of races via the url
	
		if (empty($races)) :
			$this->admin_output('No races found.', 'error', $output);
			return;
		endif;
	
		do_action('before_uci_results_add_races_cron');
		
		$this->admin_output('[DATE: '.date('n/j/Y H:i:s').']', 'log', $output);
			
		foreach ($races as $race) :
			$process_race_output=$this->process_race($race);

			foreach ($process_race_output as $type => $message) :
				$this->admin_output($message, $type, $output);
			endforeach;			
	
			if ($this->process_race_is_success($process_race_output))
				$new_results++;			
		endforeach;
		
		if ($new_results) :
			uci_results_build_season_weeks($season); // update season weeks - CHECK THIS (future)
			
			$this->update_rider_rankings($season, $output); // update rankings

			get_option('uci_results_automation_new_races', $new_results);
		else :
			update_option('uci_results_automation_new_races', 0);
		endif;
			
		do_action('after_uci_results_add_races_cron');
	
		$this->admin_output('The uci_results_add_races cron job finished.', 'log', $output);
	$new_results=0;
	$email_message='';

		return;
	}
	
	/**
	 * admin_output function.
	 * 
	 * @access protected
	 * @param string $message (default: '')
	 * @param string $type (default: '')
	 * @param string $output (default: '')
	 * @return void
	 */
	protected function admin_output($message='', $type='', $output='') {		
		switch ($output) :
			case 'wpcli':			
				$this->wpcli_output_format($type, $message);
				break;
			default:			
				if (get_option('uci_results_enable_cron_log', 0)) :
					if (!empty($type)) :
						uci_results_write_cron_log("$type: $message");
					else :
						uci_results_write_cron_log($message);
					endif;
				endif;
		endswitch;
	}
	
	/**
	 * wpcli_output_format function.
	 * 
	 * @access protected
	 * @param string $type (default: '')
	 * @param string $message (default: '')
	 * @return void
	 */
	protected function wpcli_output_format($type='', $message='') {
		switch ($type) :
			case 'warning':
				WP_CLI::warning($message);
				break;
			case 'success':
				WP_CLI::success($message);
				break;
			default:
				WP_CLI::log($message);
		endswitch;
	}

	/**
	 * update_rider_rankings function.
	 * 
	 * @access public
	 * @param string $season (default: '')
	 * @param string $output (default: 'raw')
	 * @return void
	 */
	public function update_rider_rankings($season='', $output='raw') {
		global $wpdb, $uci_results_rider_rankings, $ucicurl_races;
	
		if (!$season || $season=='')
			$season=uci_results_get_current_season();

		$weeks=$ucicurl_races->weeks($season); // get weeks in season
		$uci_results_rider_rankings->clear_db($season); // clear db for season to prevent dups
		$rider_ids=$wpdb->get_col("SELECT id FROM $wpdb->uci_results_riders"); // get all rider ids
		
		if ($output=='wpcli') :
			WP_CLI::log("Rider rankings table for $season cleared.");
		else :
			echo 'Rider rankings table for '.$season.' cleared.'; // shared
		endif;

		$this->update_rider_weekly_points($rider_ids, $season, $output);
		$this->update_rider_weekly_rank($weeks, $season, $output);
	
		return;
	}

	/**
	 * update_rider_weekly_points function.
	 * 
	 * @access public
	 * @param string $rider_ids (default: '')
	 * @param string $season (default: '')
	 * @param string $output (default: 'raw')
	 * @return void
	 */
	public function update_rider_weekly_points($rider_ids='', $season='', $output='raw') {
		global $uci_results_rider_rankings;
		
		if (empty($rider_ids))
			return false;
			
		if (empty($season))
			$season=uci_results_get_current_season();

		$count=count($rider_ids);

		if ($output=='wpcli') :
			$progress=\WP_CLI\Utils\make_progress_bar('Updating weekly points', $count);
		else :
			$progress='';
		endif;
	
		// update weekly points //
		for ( $i = 0; $i < $count; $i++ ) :
			$result=$uci_results_rider_rankings->update_rider_weekly_points($rider_ids[$i], $season);
			
			if ($output=='wpcli') :
				$progress->tick();
			else :
				$this->admin_output(strip_tags( $result), '', $output);
			endif;
		endfor;
	
		if ($output=='wpcli')
			$progress->finish();	

		return;	
	}

	/**
	 * update_rider_weekly_rank function.
	 * 
	 * @access public
	 * @param string $weeks (default: '')
	 * @param string $season (default: '')
	 * @param string $output (default: 'raw')
	 * @return void
	 */
	public function update_rider_weekly_rank($weeks='', $season='', $output='raw') {
		global $uci_results_rider_rankings;
		
		if (empty($weeks))
			return false;
			
		if (empty($season))
			$season=uci_results_get_current_season();

		$count=count($weeks);

		if ($output=='wpcli') :
			$progress=\WP_CLI\Utils\make_progress_bar('Updating weekly ranks', $count);
		else :
			$progress='';
		endif;
	
		// update weekly points //
		for ( $i = 0; $i < $count; $i++ ) :
			$result=$uci_results_rider_rankings->update_rider_weekly_rankings($season, $weeks[$i]);
			
			if ($output=='wpcli') :
				$progress->tick();
			else :
				$this->admin_output(strip_tags( $result), '', $output);
			endif;
		endfor;
	
		if ($output=='wpcli')
			$progress->finish();	

		return;		
	}

	/**
	 * get_season_url function.
	 * 
	 * @access public
	 * @param string $season (default: '')
	 * @return void
	 */
	public function get_season_url($season='') {
		global $uci_results_admin_pages;
		
		if (empty($season))
			$season=uci_results_get_current_season();
			
		if (!isset($uci_results_admin_pages->config->urls->$season) || empty($uci_results_admin_pages->config->urls->$season))
			return false;
			
		return $uci_results_admin_pages->config->urls->$season;
	}
	
	/**
	 * process_race function.
	 * 
	 * @access public
	 * @param string $race (default: '')
	 * @return void
	 */
	public function process_race($race='') {
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
	
	/**
	 * format_process_race_output function.
	 * 
	 * @access public
	 * @param array $arr (default: array())
	 * @return void
	 */
	public function format_process_race_output($arr=array()) {
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
	
	/**
	 * process_race_is_success function.
	 * 
	 * @access public
	 * @param array $arr (default: array())
	 * @return void
	 */
	public function process_race_is_success($arr=array()) {
		if (empty($arr))
			return false;
		
		foreach ($arr as $type => $message) :
			if ($type=='success')
				return true;
		endforeach;	
		
		return false;
	}	
}

$uci_results_automation=new UCIResultsAutomation();

/**
 * uci_results_add_races function.
 * 
 * @access public
 * @return void
 */
function uci_results_add_races() {
	global $uci_results_automation;
	
	$uci_results_automation->add_races();
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

/**
 * uci_results_schedule_event function.
 * 
 * @access public
 * @param mixed $timestamp
 * @param mixed $recurrence
 * @param mixed $hook
 * @param array $args (default: array())
 * @return void
 */
function uci_results_schedule_event($timestamp, $recurrence, $hook, $args = array()) {
	$next = wp_next_scheduled($hook, $args);

	if (empty($next)) :
		return wp_schedule_event($timestamp, $recurrence, $hook, $args);
	else :
		return false;
	endif;
}
?>
