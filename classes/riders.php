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

		// get results //
		if ($results) :
			$rider->results=uci_results_get_rider_results($rider_id, $race_ids, $results_season);
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
			$rider->stats=new UCIRiderStats($rider_id);

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
				$rider->stats=new UCIRiderStats($rider_id);

			$riders[]=$rider;
		endforeach;

		return $riders;
	}

	/**
	 * rider_last_race_result function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function rider_last_race_result($rider_id=0) {
	    // get race ids via meta //
		$results_args_meta = array(
			'posts_per_page' => 1,
			'post_type' => 'races',
			'orderby' => 'meta_value',
			'meta_key' => '_race_date',
			'meta_query' => array(
			    array(
			        'key' => '_rider_'.$rider_id,
			    )
			),
			'fields' => 'ids'
		);
		$race_ids=get_posts($results_args_meta);
		
		return uci_results_get_rider_results($rider_id, $race_ids);
	}

	public function get_rider_id($name='') {
		global $wpdb;

		$id=$wpdb->get_var("SELECT id FROM $wpdb->uci_results_riders WHERE name='{$name}'");

		return $id;
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
		$current_season_rank=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_rider_rankings WHERE season='$current_season->name' AND rider_id=$rider_id ORDER BY week DESC LIMIT 1");

		// check for current rank, else get prev season //
		if (null!==$current_season_rank) :
			$season=$current_season->name;
			$rank=$current_season_rank;
		else :
			$season=$prev_season->name;
			$rank=$wpdb->get_row("SELECT * FROM $wpdb->uci_results_rider_rankings WHERE season='$prev_season->name' AND rider_id=$rider_id ORDER BY week DESC LIMIT 1");
		endif;

		if (empty($rank))
			return $this->blank_rank($season);

		// get prev week rank //
		$prev_week=(int) $rank->week - 1;

		$prev_week_rank=uci_results_get_rider_rank($rider_id, $season, $prev_week);

		// set icon based on change //
		if ($prev_week_rank === NULL) :
			$rank->status='';
		elseif ($prev_week_rank==$rank->rank) :
			$rank->status='same';
		elseif ($prev_week_rank < $rank->rank) :
			$rank->status='down';
		elseif ($prev_week_rank > $rank->rank) :
			$rank->status='up';
		else :
			$rank->status='';
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

	/**
	 * get_twitter function.
	 * 
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @return void
	 */
	public function get_twitter($rider_id=0) {
		return get_post_meta($rider_id, '_rider_twitter', true);
	}

}

$uci_riders=new UCIRiders();
?>