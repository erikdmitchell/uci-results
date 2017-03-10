<?php
global $uci_riders;

/**
 * UCIRiders class.
 *
 * @since 0.1.0
 *
 */
class UCIRiders {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		
	}

	public function get_rider($args='') {
		global $wpdb;

		$default_args=array(
			'rider_id' => 0,
			'results' => false,
			'last_result' => false,
			'race_ids' => '',
			'results_season' => '',
			'ranking' => false,
			'stats' => false
		);
		$args=wp_parse_args($args, $default_args);

		extract($args);

		// if not an int, it's a slug //
		if (!is_numeric($rider_id))
			$rider_id=uci_get_rider_id($rider_id);

		// last rider id check //
		if (!$rider_id)
			return false;
			
		$rider=$wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = $rider_id");	
		$rider->results='';
		$rider->last_result='';
		$rider->ranking='';
		$rider->stats='';
		
		
		//$race_ids_sql='';
		//$race_ids='';
		

		// get results //
		if ($results) :
			$rider->results=$this->get_rider_results($rider_id, $race_ids, $results_season);
			$rider->last_result=$this->rider_last_race_result($rider_id);
		endif;

		// if no results, but last result //
		if (!$results && $last_result)
			$rider->last_result=$this->rider_last_race_result($rider_id);

		// get ranking //
		if ($ranking)
			$rider->rank=$this->get_rider_rank($rider_id);

		// get stats //
		if ($stats)
			$rider->stats=$this->get_stats($rider_id);

		$rider->twitter=$this->get_twitter($rider_id);

		return $rider;
	}

	public function get_riders($args='') {
		global $wpdb;

		$default_args=array(
			'rider_ids' => '',
			'results' => false,
			'last_result' => false,
			'results_season' => '',
			'ranking' => false,
			'stats' => false,
		);
		$args=wp_parse_args($args, $default_args);
		$riders=array();

		extract($args);

		// setup rider ids //
		if (!empty($rider_ids)) :
			if (is_array($rider_ids))
				$rider_ids=implode(',', $rider_ids);
		endif;

		// build our sql query //
		$rider_ids=$wpdb->get_col("SELECT id FROM $wpdb->uci_results_riders WHERE id IN($rider_ids)");

		foreach ($rider_ids as $rider_id) :
			$rider=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_riders WHERE id=$rider_id");
			$rider->results='';
			$rider->last_result='';
			$rider->ranking='';
			$rider->stats='';

			// get results //
			if ($results)
				$rider->results=$this->get_rider_results($rider_id, $race_ids, $results_season);

			// last result //
			if (!$results && $last_result)
				$rider->last_result=$this->rider_last_race_result($rider_id);

			// get ranking //
			if ($ranking)
				$rider->rank=$this->get_rider_rank($rider_id);

			// get stats //
			if ($stats)
				$rider->stats=$this->get_stats($rider_id);

			$riders[]=$rider;
		endforeach;

		return $riders;
	}

	public function get_rider_results($rider_id=0, $race_ids='', $season='') {
		return uci_results_get_rider_results($rider_id, $race_ids, $season);
	}

	public function rider_last_race_result($rider_id=0, $season='') {
		global $wpdb;

		$where="WHERE results.rider_id = $rider_id";

		if (!empty($season))
			$where = " AND races.season = '$season'";

		$results_sql="
			SELECT
				results.race_id,
				results.place,
				results.par,
				races.date,
				races.event,
				races.class,
				races.season,
				races.code,
				races.series_id
			FROM $wpdb->uci_results_results AS results
			LEFT JOIN $wpdb->uci_results_races AS races	ON results.race_id = races.id
			$where
			ORDER BY date DESC
			LIMIT 1
		";

		$results=$wpdb->get_results($results_sql);

		if (!count($results))
			return false;

		return $results[0];
	}

	public function get_rider_id($name='') {
		global $wpdb;

		$id=$wpdb->get_var("SELECT id FROM $wpdb->uci_results_riders WHERE name='{$name}'");

		return $id;
	}

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

		// get actual rank amount //
		$rank->prev_rank=$prev_week_rank;

		// get max rank //
		$rank->max=$wpdb->get_var("SELECT MAX(rank) FROM $wpdb->uci_results_rider_rankings WHERE season='$season' AND week=$rank->week");

		return $rank;
	}

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

	public function get_stats($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$stats=new stdClass();
		$stats->final_rankings=$this->get_rider_final_rankings($rider_id);
		$stats->wins=$this->get_rider_wins($rider_id);
		$stats->podiums=$this->get_rider_podiums($rider_id);
		$stats->world_champs=$this->world_championships($rider_id);
		$stats->world_cup_wins=$this->world_cup_wins($rider_id);
		$stats->superprestige_wins=$this->superprestige_wins($rider_id);
		$stats->gva_bpost_bank_wins=$this->gva_bpost_bank_wins($rider_id);
		$stats->world_cup_titles=$this->world_cup_titles($rider_id);
		$stats->superprestige_titles=$this->superprestige_titles($rider_id);
		$stats->gva_bpost_bank_titles=$this->gva_bpost_bank_titles($rider_id);

		return $stats;
	}

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

	public function get_rider_wins($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$wins=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_results WHERE rider_id=$rider_id AND place=1 ORDER BY race_id");

		if (!count($wins))
			return false;

		return $wins;
	}

	public function get_rider_podiums($rider_id=0) {
		global $wpdb;

		if (!$rider_id)
			return false;

		$podiums=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_results WHERE rider_id=$rider_id AND (place=1 OR place=2 OR place=3) ORDER BY race_id");

		if (!count($podiums))
			return false;

		return $podiums;
	}

	public function world_championships($rider_id=0) {
		global $wpdb;

		$sql="
			SELECT
				*
			FROM $wpdb->uci_results_results AS results
			INNER JOIN $wpdb->uci_results_races AS races ON results.race_id = races.id
			WHERE rider_id = $rider_id
				AND races.class = 'CM'
				AND place = 1
		";

		$wc=$wpdb->get_results($sql);

		if (!count($wc))
			return false;

		return $wc;
	}

	public function world_cup_wins($rider_id=0) {
		global $wpdb;

		$sql="
			SELECT
				*
			FROM $wpdb->uci_results_results AS results
			INNER JOIN $wpdb->uci_results_races AS races ON results.race_id = races.id
			WHERE rider_id = $rider_id
				AND races.class = 'CDM'
				AND place = 1
		";

		$wc=$wpdb->get_results($sql);

		if (!count($wc))
			return false;

		return $wc;
	}

	public function world_cup_titles($rider_id=0) {
		global $wpdb;

		$wc_series_id=4;
		$seasons=$wpdb->get_col("SELECT	DISTINCT season FROM $wpdb->uci_results_series_overall WHERE series_id = $wc_series_id");
		$seasons_string=implode("','", $seasons);
		$sql="
			SELECT
				rank,
				points,
				season
			FROM $wpdb->uci_results_series_overall
			WHERE rider_id = $rider_id
				AND season IN ('$seasons_string')
				AND series_id = $wc_series_id
				AND rank = 1
		";

		$titles=$wpdb->get_results($sql);

		if (!count($titles))
			return false;

		return $titles;
	}

	public function superprestige_wins($rider_id=0) {
		global $wpdb;

		$sql="
			SELECT
				*
			FROM $wpdb->uci_results_results AS results
			INNER JOIN $wpdb->uci_results_races AS races ON results.race_id = races.id
			WHERE rider_id = $rider_id
				AND races.series_id = 1
				AND place = 1
		";

		$wins=$wpdb->get_results($sql);

		if (!count($wins))
			return false;

		return $wins;
	}

	public function superprestige_titles($rider_id=0) {
		global $wpdb;

		$series_id=1;
		$seasons=$wpdb->get_col("SELECT	DISTINCT season FROM $wpdb->uci_results_series_overall WHERE series_id = $series_id");
		$seasons_string=implode("','", $seasons);
		$sql="
			SELECT
				rank,
				points,
				season
			FROM $wpdb->uci_results_series_overall
			WHERE rider_id = $rider_id
				AND season IN ('$seasons_string')
				AND series_id = $series_id
				AND rank = 1
		";

		$titles=$wpdb->get_results($sql);

		if (!count($titles))
			return false;

		return $titles;
	}

	public function gva_bpost_bank_wins($rider_id=0) {
		global $wpdb;

		$sql="
			SELECT
				*
			FROM $wpdb->uci_results_results AS results
			INNER JOIN $wpdb->uci_results_races AS races ON results.race_id = races.id
			WHERE rider_id = $rider_id
				AND races.series_id IN (2,3)
				AND place = 1
		";

		$wins=$wpdb->get_results($sql);

		if (!count($wins))
			return false;

		return $wins;
	}

	public function gva_bpost_bank_titles($rider_id=0) {
		global $wpdb;

		$series_ids='2,3';
		$seasons=$wpdb->get_col("SELECT	DISTINCT season FROM $wpdb->uci_results_series_overall WHERE series_id IN ($series_ids)");
		$seasons_string=implode("','", $seasons);
		$sql="
			SELECT
				rank,
				points,
				season
			FROM $wpdb->uci_results_series_overall
			WHERE rider_id = $rider_id
				AND season IN ('$seasons_string')
				AND series_id IN ($series_ids)
				AND rank = 1
		";

		$titles=$wpdb->get_results($sql);

		if (!count($titles))
			return false;

		return $titles;
	}

	public function get_twitter($rider_id=0) {
		global $wpdb;

		$twitter=$wpdb->get_var("SELECT twitter FROM $wpdb->uci_results_riders WHERE id = $rider_id");

		return $twitter;
	}

}

$uci_riders=new UCIRiders();
?>