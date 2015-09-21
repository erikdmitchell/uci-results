<?php
/**
 * RaceStats class.
 *
 * @since Version 1.6.1
 */
class RaceStats {

	public $version='0.1.1';

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
		global $wpdb,$uci_curl;

		//if (!$season)
			//return '<div class="error">Error: No season detected for get season race ranking.</div>';

		$where=null;

		if ($season)
			$where="WHERE season='$season'";

		$html=null;
		$sort_type='race_total';
		$sort='desc';
		$races=$wpdb->get_results("SELECT * FROM ".$uci_curl->table." ".$where);
		//$races=$this->sort_races($sort_type,$sort,$races);

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
				$data=unserialize(base64_decode($race->data));
				$class=null;

				$html.='<div class="'.$class.' row">';
					$html.='<div class="date col-md-2">'.$data->date.'</div>';
					$html.='<div class="event col-md-4">'.$data->event.'</div>';
					$html.='<div class="nat col-md-1">'.$data->nat.'</div>';
					$html.='<div class="class col-md-1">'.$data->class.'</div>';
					$html.='<div class="winner col-md-2">'.$data->winner.'</div>';

					if (isset($data->field_quality))
						$html.='<div class="fq col-md-2">'.$data->field_quality->race_total.'</div>';

				$html.='</div>';
			endforeach;

		$html.='</div>';

		return $html;
	}

	/**
	 * sorts our races db object
	 * only does fq, need our variable to be more robust
	 */
/*
	public function sort_races($field=false,$method=false,$races=false) {
		if (!($field) || !($method) || !($races))
			return array();

		$method=constant('SORT_'.strtoupper($method));

		foreach ($races as $race) :
			$race->data=unserialize(base64_decode($race->data));
		endforeach;

		$dates = array();
		foreach ($races as $race) :
    	$arr[] = $race->data->field_quality->$field;
		endforeach;

		array_multisort($arr,$method,$races);

		foreach ($races as $race) :
			$race->data=base64_encode(serialize($race->data));
		endforeach;

		return $races;
	}
*/

}
?>