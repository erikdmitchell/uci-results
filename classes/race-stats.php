<?php
/**
 * RaceStats class.
 *
 * @since Version 1.0.1
 */
class RaceStats {

	public $max_rows=0;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * get_season_race_rankings function.
	 *
	 * Admin Side (may not be used)
	 *
	 * @access public
	 * @param bool $season (default: false)
	 * @return void
	 */
	public function get_season_race_rankings($season=false) {
		$html=null;
		$races=$this->get_races();

		$html.='<h3>'.$season.' Race Rankings</h3>';

		$html.='<div id="season-race-rankings" class="season-race-rankings">';
			$html.='<div class="header row">';
				$html.='<div class="date col-md-2">Date</div>';
				$html.='<div class="event col-md-4">Event</div>';
				$html.='<div class="nat col-md-1">Nat.</div>';
				$html.='<div class="class col-md-1">Class</div>';
				$html.='<div class="winner col-md-2">Winner</div>';
				$html.='<div class="fq col-md-2">FQ</div>';
			$html.='</div>';

			foreach ($races as $race) :
				$html.='<div class="row">';
					$html.='<div class="date col-md-2">'.$race->date.'</div>';
					$html.='<div class="event col-md-4">'.$race->event.'</div>';
					$html.='<div class="nat col-md-1">'.$race->nat.'</div>';
					$html.='<div class="class col-md-1">'.$race->class.'</div>';
					$html.='<div class="winner col-md-3">'.$race->winner.'</div>';

					if (isset($race->field_quality))
						$html.='<div class="fq col-md-1">'.$race->field_quality->race_total.'</div>';

				$html.='</div>';
			endforeach;

		$html.='</div>';

		return $html;
	}

	/**
	 * get_races function.
	 *
	 * @access public
	 * @param array $args (default: array())
	 * @return void
	 */
	public function get_races($args=array()) {
		global $wpdb,$uci_curl,$wp_query;

		$limit=null;
		$default_args=array(
			'season' => '2015/2016',
			'pagination' => true,
			'paged' => 1,
			'per_page' => 15
		);
		$args=array_merge($default_args,$args);

		extract($args);

		if ($pagination) :
			if ($paged==0) :
				$start=0;
			else :
				$start=$per_page*($paged-1);
			endif;
			$end=$per_page;
			$limit="LIMIT $start,$end";
			$rank=$start+1;
		endif;

		$sql="
			SELECT
				code,
				date,
				event AS name,
				nat,
				class,
				fq
			FROM $uci_curl->table
			WHERE season='$season'
			ORDER BY date
			$limit
		";

		$races=$wpdb->get_results($sql);

		$max_races=$wpdb->get_results("SELECT DISTINCT(code) FROM $uci_curl->table WHERE season='$season'");
		$wp_query->uci_curl_max_pages=$wpdb->num_rows; // set max

		return $races;
	}

	/**
	 * get_race function.
	 *
	 * @access public
	 * @param bool $code (default: false)
	 * @return void
	 */
	public function get_race($code=false) {
		global $wpdb,$uci_curl;

		if (!$code)
			return false;

		$race=new stdClass();
		$sql="
			SELECT
				results.name AS rider,
				results.place,
				results.nat,
				results.age,
				results.time,
				CASE WHEN results.par IS NULL OR results.par='' THEN 0 ELSE results.par END AS points
			FROM $uci_curl->results_table AS results
			LEFT JOIN $uci_curl->table AS races
			ON results.code=races.code
			WHERE results.code='$code'
			ORDER BY results.place
		";
		$race_sql="
			SELECT
				event AS race,
				date,
				class,
				season
			FROM $uci_curl->table
			WHERE code='$code'
		";

		$race->results=$wpdb->get_results($sql);
		$race->details=$wpdb->get_row($race_sql);

		return $race;
	}

	// race not used yet
	public function get_rank_seasons($race=false) {
		global $wpdb,$uci_curl;

		$sql="
			SELECT
				season
			FROM $uci_curl->table
			WHERE season!=false
			GROUP BY season
			ORDER BY season ASC
		";

		$seasons=$wpdb->get_col($sql);

		return $seasons;
	}

}

$RaceStats = new RaceStats();
?>