<?php
global $uci_results_rider_rankings;

/**
 * UCIResultsRiderRankings class.
 */
class UCIResultsRiderRankings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts_styles'));
		add_action('wp_ajax_update_rider_rankings_get_rider_ids', array($this, 'ajax_update_rider_rankings_get_rider_ids'));
		add_action('wp_ajax_update_rider_rankings', array($this, 'ajax_update_rider_weekly_points'));
		add_action('wp_ajax_update_rider_weekly_rank', array($this, 'ajax_update_rider_weekly_rank'));
		add_action('wp_ajax_get_weeks_in_season', array($this, 'ajax_get_weeks_in_season'));
	}

	/**
	 * admin_scripts_styles function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_scripts_styles() {
		wp_enqueue_script('uci-curl-update-rankings',UCICURL_URL.'/js/update-rankings.js',array('jquery'), '0.1.0',true);
	}

	/**
	 * ajax_update_rider_rankings_get_rider_ids function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_update_rider_rankings_get_rider_ids() {
		if (!isset($_POST['season']) || $_POST['season']=='') :
			echo 'No season found';
			return false;
		endif;

		global $wpdb;

		$season=$_POST['season']; // set season
		$wpdb->delete($wpdb->ucicurl_rider_rankings, array('season' => $season)); // remove ranks from season to prevent dups
		$rider_ids=$wpdb->get_col("SELECT id FROM {$wpdb->ucicurl_riders}"); // get all rider ids

		wp_send_json($rider_ids);
	}

	/**
	 * ajax_update_rider_rankings function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_update_rider_weekly_points() {
		global $wpdb;

		extract($_POST);

		if (!$rider_id || !$season)
			return;

		$message=$this->update_rider_weekly_points($rider_id, $season);

		echo $message;

		wp_die();
	}

	/**
	 * ajax_update_rider_weekly_rank function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_update_rider_weekly_rank() {
		if (!$_POST['season'] || !$_POST['week'])
			return false;

		$this->update_rider_weekly_rankings($_POST['season'], $_POST['week']);

		echo '<div class="updated">Week '.$_POST['week'].' updated!</div>';

		wp_die();
	}

	/**
	 * update_rider_weekly_points function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @param string $season (default: '')
	 * @return void
	 */
	public function update_rider_weekly_points($rider_id=0, $season='') {
		global $wpdb;

		// check we have the required data //
		if (!$rider_id || empty($season))
			return false;

		$sql="
			SELECT
				races.week AS week,
				(
					SELECT IFNULL(SUM(s_results.par), 0)
					FROM {$wpdb->ucicurl_results} AS s_results
					LEFT JOIN {$wpdb->ucicurl_races} AS s_races
					ON s_results.race_id = s_races.id
					WHERE s_races.season='{$season}'
						AND s_results.rider_id={$rider_id}
						AND s_races.week<=races.week
				) AS points
			FROM {$wpdb->ucicurl_results} AS results
			LEFT JOIN {$wpdb->ucicurl_races} AS races
			ON results.race_id = races.id
			WHERE races.season='{$season}'
				AND results.rider_id={$rider_id}
			GROUP BY races.week
			ORDER BY races.week
		";
		$weekly_points=$wpdb->get_results($sql);

		foreach ($weekly_points as $arr) :
				$data=array(
					'rider_id' => $rider_id,
					'points' => $arr->points,
					'season' => $season,
					'week' => $arr->week,
				);

				$wpdb->insert($wpdb->ucicurl_rider_rankings, $data);
		endforeach;

		$message='<div class="updated">Rider ID '.$rider_id.' rankings have been updated!</div>';

		return $message;
	}

	/**
	 * update_rider_weekly_rankings function.
	 *
	 * @access public
	 * @param string $season (default: '')
	 * @param int $week (default: 0)
	 * @return void
	 */
	public function update_rider_weekly_rankings($season='', $week=0) {
		global $wpdb;

		$this->add_previous_weeks_riders_to_rank($season, $week);

		$sql="
			SELECT id
			FROM {$wpdb->ucicurl_rider_rankings}
			WHERE season='{$season}'
				AND week={$week}
			ORDER BY points DESC
		";

		$ids=$wpdb->get_col($sql);
		$rank=1;

		// we now just update our rank via id //
		foreach ($ids as $id) :
			$data=array(
				'rank' => $rank
			);
			$where=array(
				'id' => $id
			);
			$wpdb->update($wpdb->ucicurl_rider_rankings, $data, $where);
			$rank++;
		endforeach;
	}

	/**
	 * add_previous_weeks_riders_to_rank function.
	 *
	 * @access public
	 * @param string $season (default: '')
	 * @param int $week (default: 0)
	 * @return void
	 */
	public function add_previous_weeks_riders_to_rank($season='', $week=0) {
		global $wpdb;

		// no prev week, we out //
		if ($week<=1)
			return;

		$prev_week=$week-1;
		$prev_week_ranking_ids=$wpdb->get_col("SELECT rider_id FROM {$wpdb->ucicurl_rider_rankings}	WHERE season='{$season}' AND week={$prev_week}");
		$this_week_ranking_ids=$wpdb->get_col("SELECT rider_id FROM {$wpdb->ucicurl_rider_rankings}	WHERE season='{$season}' AND week={$week}");

		// if missing from this week, use last weeks points //
		foreach ($prev_week_ranking_ids as $rider_id) :
			if (!in_array($rider_id, $this_week_ranking_ids)) :
				$prev_points=$wpdb->get_var("SELECT points FROM {$wpdb->ucicurl_rider_rankings}	WHERE rider_id={$rider_id} AND season='{$season}' AND week={$prev_week}");

				$insert_data=array(
					'rider_id' => $rider_id,
					'points' => $prev_points,
					'season' => $season,
					'week' => $week,
				);
				$wpdb->insert($wpdb->ucicurl_rider_rankings, $insert_data);
			endif;
		endforeach;
	}

	/**
	 * ajax_get_weeks_in_season function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_get_weeks_in_season() {
		global $ucicurl_races;

		$weeks=$ucicurl_races->weeks($_POST['season']);

		wp_send_json($weeks);
	}

}

$uci_results_rider_rankings=new UCIResultsRiderRankings();
?>