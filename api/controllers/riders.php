<?php

/**
 * Riders class.
 */
class riders {
	private $_params;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $params
	 * @return void
	 */
	public function __construct($params) {
		$this->_params=$params;
	}

	/**
	 * getID function.
	 *
	 * @access public
	 * @return void
	 */
	public function getID() {
		$id=uci_results_get_rider_id($this->_params['slug']);

		return $id;
	}

	/**
	 * rider function.
	 *
	 * @access public
	 * @return void
	 */
	public function rider() {
		global $ucicurl_riders;

		$rider=$ucicurl_riders->get_rider($this->_params);

		return $rider;
	}

	/**
	 * riderSearch function.
	 *
	 * @access public
	 * @return void
	 */
	public function riderSearch() {
		global $wpdb;

		if (!isset($this->_params['rider']))
			return false;

		if (isset($this->_params['rider_ids']) && !empty($this->_params['rider_ids'])) :
			$rider_ids=' AND riders.id IN ( '.implode(', ', $this->_params['rider_ids']).' )';
		else :
			$rider_ids='';
		endif;

		if (!isset($this->_params['season']) || empty($this->_params['season']))
			$this->_params['season']=uci_results_get_default_rider_ranking_season();

		if (!isset($this->_params['week']) || empty($this->_params['week']))
			$this->_params['week']=uci_results_get_default_rider_ranking_week();

		$sql="
			SELECT
				riders.id,
				riders.name,
				riders.nat,
				rankings.rank,
				rankings.points
			FROM $wpdb->uci_results_riders AS riders
			INNER JOIN $wpdb->uci_results_rider_rankings AS rankings ON riders.id = rankings.rider_id
			WHERE riders.name LIKE '%".$this->_params['rider']."%'
				AND rankings.season = '".$this->_params['season']."'
				AND rankings.week = ".$this->_params['week']."
				$rider_ids
		";
		$results=$wpdb->get_results($sql);

		return $results;
	}

	/**
	 * raceResult function.
	 *
	 * @access public
	 * @return void
	 */
	public function raceResult() {
		global $wpdb;

		if (!isset($this->_params['rider_id']) || !isset($this->_params['race_code']))
			return false;

		$rider_id=$this->_params['rider_id'];
		$code=$this->_params['race_code'];

		$result=$wpdb->get_results("
			SELECT
				races.event,
      	results.place,
      	results.par AS points
			FROM $wpdb->uci_results_races AS races
			INNER JOIN $wpdb->uci_results_results AS results ON (results.rider_id=$rider_id AND races.id=results.race_id)
			WHERE races.code = '$code'
		");

		if (!count($result)) :
			$result = new stdClass();
			$result->event = '';
			$result->place = 0;
			$result->points = 0;
		else :
			$result=$result[0];
		endif;

		return $result;
	}

	/**
	 * currentRank function.
	 *
	 * @access public
	 * @return void
	 */
	public function currentRank() {
		global $wpdb;

		if (!isset($this->_params['season']) || !isset($this->_params['rider_id']))
			return false;

		// if we have week, get specific, else get last week //
		if (isset($this->_params['week'])) :
			$current_rank=uci_results_get_rider_rank($this->_params['rider_id'], $this->_params['season'], $this->_params['week']);
		else :
			$current_rank=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_rider_rankings WHERE season='".$this->_params['season']."' AND rider_id=".$this->_params['rider_id']." ORDER BY week DESC LIMIT 1");
		endif;

		return $current_rank;
	}

	/**
	 * riderRank function.
	 *
	 * @access public
	 * @return void
	 */
	public function riderRank() {
		global $ucicurl_riders;

		$rank=$ucicurl_riders->get_rider_rank($this->_params['rider_id']);

		return $rank;
	}

	/**
	 * recentRankData function.
	 *
	 * @access public
	 * @return void
	 */
	public function recentRankData() {
		global $wpdb;

		$data=array();
		$current_season=$this->_params['current_season'];
		$prev_season=$this->_params['prev_season'];
		$current_season_week=$wpdb->get_var("SELECT MAX(week) AS week FROM $wpdb->uci_results_rider_rankings WHERE season='$current_season' ORDER BY week DESC");

		if ($current_season_week===null) :
			$data['season']=$prev_season;
			$data['week']=$wpdb->get_var("SELECT MAX(week) AS week FROM $wpdb->uci_results_rider_rankings WHERE season='$prev_season' ORDER BY week DESC");
		else :
			$data['season']=$current_season;
			$data['week']=$current_season_week;
		endif;

		return $data;
	}

}
?>