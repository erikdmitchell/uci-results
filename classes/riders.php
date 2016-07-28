<?php

/**
 * UCIResultsRiders class.
 *
 * @since 0.1.0
 *
 */
class UCIResultsRiders {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {}

	/**
	 * get_rider function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @param bool $results (default: false)
	 * @param bool $ranking (default: false)
	 * @param bool $stats (default: false)
	 * @return void
	 */
	public function get_rider($rider_id=0, $results='', $ranking=false, $stats=false) {
		global $wpdb;

		// if not an int, it's a slug //
		if (!is_numeric($rider_id)) :
			$rider_id=uci_results_get_rider_id($rider_id);
		endif;

		// last rider id check //
		if (empty($rider_id))
			return false;

		$race_ids_sql='';
		$race_ids='';
		$rider=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_riders WHERE id=$rider_id");
		$rider->results='';
		$rider->ranking='';
		$rider->stats='';

		// get results, $results is either true, an array of race ids or string of race ids //
		if ($results) :
			// check if its something besides true //
			if (is_array($results)) :
				$race_ids=implode(',', $results);
			elseif (is_string($results)) :
				$race_ids=$results;
			endif;

			// build sql if we have specific ids //
			if (!empty($race_ids))
				$race_ids_sql=" AND {$wpdb->uci_results_results}.race_id IN ($race_ids)";

			$results_sql="
				SELECT *
				FROM {$wpdb->uci_results_results}
				LEFT JOIN {$wpdb->uci_results_races}
				ON {$wpdb->uci_results_results}.race_id={$wpdb->uci_results_races}.id
				WHERE rider_id = $rider_id
				$race_ids_sql
				ORDER BY date DESC
			";

			$rider->results=$wpdb->get_results($results_sql);
		endif;

		// get ranking //
		if ($ranking)
			$rider->rank=$this->get_rider_rank($rider_id);

		// get stats //
		if ($stats)
			$rider->stats=$this->get_stats($rider_id);

		return $rider;
	}

	/**
	 * get_rider_id function.
	 *
	 * @access public
	 * @param string $name (default: '')
	 * @return void
	 */
	public function get_rider_id($name='') {
		global $wpdb;

		$id=$wpdb->get_var("SELECT id FROM $wpdb->uci_results_riders WHERE name='{$name}'");

		return $id;
	}

	/**
	 * nats function.
	 *
	 * @access public
	 * @return void
	 */
	public function nats() {
		global $wpdb;

		$nats=$wpdb->get_col("SELECT DISTINCT(nat) FROM $wpdb->uci_results_riders ORDER BY nat ASC");

		return $nats;
	}

	/**
	 * get_rider_rank function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_rider_rank($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$current_season=uci_results_get_current_season();
		$prev_season=uci_results_get_previous_season();
		$current_season_rank=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_rider_rankings WHERE season='$current_season' AND rider_id=$rider_id ORDER BY week DESC LIMIT 1");

		// check for current rank, else get prev season //
		if (null!==$current_season_rank) :
			$season=$current_season;
			$rank=$current_season_rank;
		else :
			$season=$prev_season;
			$rank=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_rider_rankings WHERE season='$prev_season' AND rider_id=$rider_id ORDER BY week DESC LIMIT 1");
		endif;

		if (empty($rank))
			return $this->blank_rank($season);

		// get prev week rank //
		$prev_week=(int) $rank->week - 1;

		$prev_week_rank=uci_results_get_rider_rank($rider_id, $season, $prev_week);

		// set icon based on change //
		if ($prev_week_rank === NULL) :
			$rank->prev_icon='';
		elseif ($prev_week_rank==$rank->rank) :
			$rank->prev_icon='';
		elseif ($prev_week_rank < $rank->rank) :
			$rank->prev_icon='<i class="fa fa-arrow-down" aria-hidden="true"></i>';
		elseif ($prev_week_rank > $rank->rank) :
			$rank->prev_icon='<i class="fa fa-arrow-up" aria-hidden="true"></i>';
		else :
			$rank->prev_icon='';
		endif;

		return $rank;
	}

	/**
	 * blank_rank function.
	 *
	 * @access protected
	 * @param string $season (default: '')
	 * @return void
	 */
	protected function blank_rank($season='') {
		$rank=new stdClass();

		$rank->id=0;
		$rank->points=0;
		$rank->season=$season;
		$rank->rank=0;
		$rank->week=0;
		$rank->prev_icon='';

		return $rank;
	}

	/**
	 * get_stats function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_stats($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$stats=new stdClass();
		$stats->final_rankings=$this->get_rider_final_rankings($rider_id);
		$stats->wins=$this->get_rider_wins($rider_id);
		$stats->podiums=$this->get_rider_podiums($rider_id);

		return $stats;
	}

	/**
	 * get_rider_final_rankings function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_rider_final_rankings($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$current_season=uci_results_get_current_season();
		$rankings=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_rider_rankings WHERE rider_id=$rider_id AND week=(SELECT MAX(week) FROM $wpdb->uci_results_rider_rankings) AND season!='$current_season' GROUP BY season");

		if (!count($rankings))
			return false;

		return $rankings;
	}

	/**
	 * get_rider_wins function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_rider_wins($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$wins=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_results WHERE rider_id=$rider_id AND place=1 ORDER BY race_id");

		if (!count($wins))
			return false;

		return $wins;
	}

	/**
	 * get_rider_podiums function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_rider_podiums($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$podiums=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_results WHERE rider_id=$rider_id AND (place=1 OR place=2 OR place=3) ORDER BY race_id");

		if (!count($podiums))
			return false;

		return $podiums;
	}

}

$ucicurl_riders=new UCIResultsRiders();
?>