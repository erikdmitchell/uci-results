<?php
/**
 * RaceStats class.
 *
 * @since Version 1.6.1
 */
class RaceStats {

	public $version='0.1.2';

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
				$html.='<div class="fq col-md-2">Field Quality</div>';
			$html.='</div>';

			foreach ($races as $race) :
				$html.='<div class="row">';
					$html.='<div class="date col-md-2">'.$race->date.'</div>';
					$html.='<div class="event col-md-4">'.$race->event.'</div>';
					$html.='<div class="nat col-md-1">'.$race->nat.'</div>';
					$html.='<div class="class col-md-1">'.$race->class.'</div>';
					$html.='<div class="winner col-md-2">'.$race->winner.'</div>';

					if (isset($race->field_quality))
						$html.='<div class="fq col-md-2">'.$race->field_quality->race_total.'</div>';

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
		global $wpdb,$uci_curl;

		$where=null;
		$default_args=array(
			'sort' => true,
			'season' => false,
		);

		$args=array_merge($default_args,$args);

		extract($args);

		if ($season)
			$where="WHERE season='$season'";

		$races=$wpdb->get_results("SELECT * FROM ".$uci_curl->table.$where);

		if ($sort)
			$races=$this->sort_races($races);

		return $races;
	}

	/**
	 * sort_races function.
	 *
	 * DOES NOTHING @since 0.1.2
	 *
	 * @access public
	 * @param bool $races (default: false)
	 * @param array $args (default: array())
	 * @return void
	 */
	public function sort_races($races=false,$args=array()) {
		if (!$races)
			return false;

		return $races;
	}

	/**
	 * get_race_results_from_db function.
	 *
	 * @access public
	 * @param bool $code (default: false)
	 * @return void
	 */
	public function get_race_results_from_db($code=false) {
		global $wpdb,$uci_curl;

		if (!$code)
			return false;

		$results=$wpdb->get_results("SELECT * FROM $uci_curl->results_table WHERE code='$code'");

		return $results;
	}

}

$RaceStats = new RaceStats();
?>