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
		add_action('wp_ajax_get_weeks_in_season', array($this, 'ajax_get_weeks_in_season'));
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
					FROM $wpdb->uci_results_results AS s_results
					LEFT JOIN $wpdb->uci_results_races AS s_races
					ON s_results.race_id = s_races.id
					WHERE s_races.season = '$season'
						AND s_results.rider_id = $rider_id
						AND s_races.week <= races.week
				) AS points
			FROM $wpdb->uci_results_results AS results
			LEFT JOIN $wpdb->uci_results_races AS races
			ON results.race_id = races.id
			WHERE races.season = '$season'
				AND results.rider_id = $rider_id
			GROUP BY races.week
			ORDER BY races.week
		";

		$weekly_points=$wpdb->get_results($sql);

		foreach ($weekly_points as $arr) :
			// skip if no points //
			if (empty($arr->points) || $arr->points==0)
				continue;

			$data=array(
				'rider_id' => $rider_id,
				'points' => $arr->points,
				'season' => $season,
				'week' => $arr->week,
			);

			$wpdb->insert($wpdb->uci_results_rider_rankings, $data);
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
			FROM $wpdb->uci_results_rider_rankings
			WHERE season = '$season'
				AND week = $week
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
			$wpdb->update($wpdb->uci_results_rider_rankings, $data, $where);
			$rank++;
		endforeach;

		$message='<div class="updated">Week '.$week.' updated!</div>';

		return $message;
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
		$prev_week_ranking_ids=$wpdb->get_col("SELECT rider_id FROM $wpdb->uci_results_rider_rankings WHERE season = '$season' AND week = $prev_week");	
		$this_week_ranking_ids=$wpdb->get_col("SELECT rider_id FROM $wpdb->uci_results_rider_rankings WHERE season = '$season' AND week = $week");

		// if missing from this week, use last weeks points //
		foreach ($prev_week_ranking_ids as $rider_id) :
			if (!in_array($rider_id, $this_week_ranking_ids)) :
				$prev_points=$wpdb->get_var("SELECT points FROM $wpdb->uci_results_rider_rankings WHERE rider_id = $rider_id AND season = '$season' AND week = $prev_week");

				$insert_data=array(
					'rider_id' => $rider_id,
					'points' => $prev_points,
					'season' => $season,
					'week' => $week,
				);
				$wpdb->insert($wpdb->uci_results_rider_rankings, $insert_data);
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

	/**
	 * clear_db function.
	 *
	 * @access public
	 * @param string $season (default: '')
	 * @return void
	 */
	public function clear_db($season='') {
		global $wpdb;

		if (empty($season))
			return false;

		$wpdb->delete($wpdb->uci_results_rider_rankings, array('season' => $season)); // remove ranks from season to prevent dups

		return true;
	}

	/**
	 * update_twitter function.
	 *
	 * @access public
	 * @return void
	 */
	public function update_twitter() {
		global $uci_results_pages, $uci_results_twitter, $uci_riders;

		if (!uci_results_post_rankings_updates_to_twitter())
			return false;

		$rider_info='';
		$riders=new UCI_Results_Query(array(
			'per_page' => 5,
			'type' => 'riders',
			'rankings' => true
		));

		// get our leader info //
		if (isset($riders->posts[0])) :
			$rider=$riders->posts[0];

			// use twitter if we have it //
			$twitter=$uci_riders->get_twitter($rider->id);

			if (!empty($twitter)) :
				$name='@'.$twitter;
			else :
				$name=$rider->name;
			endif;

			$rider_info=$name.' ('.$rider->nat.') leads the rankings with '.$rider->points.' points.';
		endif;

		$url=get_permalink($uci_results_pages['rider_rankings']);
		$status=$rider_info.' '.$url;
		$uci_results_twitter->update_status($status);
	}

	/**
	 * update_series_overall function.
	 *
	 * @access public
	 * @param int $series_id (default: 0)
	 * @param string $season (default: '')
	 * @return void
	 */
	public function update_series_overall($series_id=0, $season='') {
		global $wpdb;

		if (!$series_id || empty($season))
			return false;

		// clear db //
		$wpdb->delete($wpdb->uci_results_series_overall, array('series_id' => $series_id, 'season' => $season));

		// build vars and sql //
		$sql="
			SELECT
			  results.rider_id,
			  SUM(results.pcr) AS points
			FROM $wpdb->uci_results_results AS results
			INNER JOIN $wpdb->uci_results_races AS races ON results.race_id = races.id
			WHERE races.series_id = $series_id
			AND season='$season'
			GROUP BY results.rider_id
			ORDER BY points DESC
		";
		$riders=$wpdb->get_results($sql);
		$rank=1;

		// input into db //
		foreach ($riders as $rider) :
			$data=array(
				'rider_id' => $rider->rider_id,
				'points' => $rider->points,
				'series_id' => $series_id,
				'season' => $season,
				'rank' => $rank
			);

			$rank++;

			$wpdb->insert($wpdb->uci_results_series_overall, $data);
		endforeach;

		return;
	}

}

$uci_results_rider_rankings=new UCIResultsRiderRankings();
?>