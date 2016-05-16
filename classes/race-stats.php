<?php
/**
 * UCIcURLRaces class.
 *
 * @since Version 2.0.0
 */
class UCIcURLRaces {

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
		global $wpdb, $wp_query;

		$limit=null;
		$where=array();
		$default_args=array(
			'pagination' => true,
			'paged' => 1,
			'per_page' => 15,
			'order_by' => 'date',
			'order' => 'DESC',
			'class' => false,
			'season' => false,
			'nat' => false,
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

		if ($class)
			$where[]="class='{$class}'";

		if ($season)
			$where[]="season='{$season}'";

		if ($nat)
			$where[]="nat='{$nat}'";

		if (!empty($where)) :
			$where=' WHERE '.implode(' AND ',$where);
		else :
			$where='';
		endif;

		$sql="
			SELECT
				*
			FROM {$wpdb->ucicurl_races} AS races
			{$where}
			ORDER BY {$order_by} {$order}
			{$limit}
		";
		$races=$wpdb->get_results($sql);
		$max_races=$wpdb->get_results("SELECT DISTINCT(id) FROM {$wpdb->ucicurl_races} WHERE season='{$season}'");
		$wp_query->uci_curl_max_pages=$wpdb->num_rows; // set max

		// clean up some misc db slashes and formatting //
		foreach ($races as $race) :
			$race->code=stripslashes($race->code);
			$race->name=stripslashes($race->event);
			$race->date=date(get_option('date_format'), strtotime($race->date));
		endforeach;

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
			WHERE results.code=\"$code\"
			ORDER BY results.place
		";
		$race_sql="
			SELECT
				event AS race,
				date,
				class,
				nat,
				season
			FROM $uci_curl->table
			WHERE code=\"$code\"
		";

		$race->results=$wpdb->get_results($sql);
		$race->details=$wpdb->get_row($race_sql);

		return $race;
	}

	/**
	 * get_race_classes function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_race_classes() {
		global $wpdb,$uci_curl;

		$classes=$wpdb->get_col("SELECT DISTINCT class FROM $uci_curl->table");

		return $classes;
	}

}

$ucicurl_races=new UCIcURLRaces();
?>