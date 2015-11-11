<?php
/**
 * FieldQuality class.
 *
 * @since Version 1.0.1
 */

class FieldQuality {

	public $field_quality;

	public function __construct($race_code=false) {
		$this->field_quality=$this->generate_field_quality($race_code);
	}

	/**
	 * generate_field_quality function.
	 *
	 * @access public
	 * @param bool $race_code (default: false)
	 * @return void
	 */
	public function generate_field_quality($race_code=false) {
		global $uci_curl,$RaceStats;

		if (!$race_code)
			return 0;

		$CrossSeasons=new CrossSeasons();
		$seasons=$CrossSeasons->seasons;
		$race_data=$RaceStats->get_race($race_code);
		$race_results=$race_data->results;
		$race_details=$race_data->details;

		if (empty($race_data) || empty($race_data->results) || empty($race_data->details))
			return 0;

		$uci_points=$this->get_points_before_race($race_details->season,$race_details->date);
		$wcp_points=$this->get_points_before_race($race_details->season,$race_details->date,'wcp');
		$race_results=$this->append_previous_race_points($race_details->season,$race_details->date,$race_results); // SLOW
		$number_of_finishers=count($race_results);
		$uci_points_in_field=0;
		$wcp_points_in_field=0;
		$uci_multiplier=0;
		$wcp_multiplier=0;
		$previous_season=0;
		$divider=1;
		$fq_obj=new stdClass();

		// get total uci and wcp points //
		foreach ($race_results as $result) :
			$uci_points_in_field=$uci_points_in_field+$result->total_uci_points;
			$wcp_points_in_field=$wcp_points_in_field+$result->total_wcp_points;
		endforeach;

		// get our multipliers //
		if ($uci_points!=0) :
			$uci_multiplier=$uci_points_in_field/$uci_points;
			$divider++;
		endif;

		if ($wcp_points!=0) :
			$wcp_multiplier=$wcp_points_in_field/$wcp_points;
			$divider++;
		endif;

		// race class number // --- NOT USED YET
		switch ($race_details->class) :
			case 'C2':
				$race_class_number=5;
				break;
			case 'C1':
				$race_class_number=4;
				break;
			case 'CN':
				$race_class_number=3;
				break;
			case 'CDM':
				$race_class_number=2;
				break;
			case 'CM':
				$race_class_number=1;
				break;
			default:
				$race_class_number=0;
		endswitch;

		// get previous season //
		foreach ($seasons as $key => $season) :
			if ($season==$race_details->season && isset($seasons[$key-1]))
				$previous_season=$seasons[$key-1];
		endforeach;

		// get finishers multiplier //
		$finishers=$this->get_average_finishers($previous_season,$race_details->class);
		$finishers_multiplier=$number_of_finishers/$finishers[$previous_season][0]->average_finishers;

		if ($finishers_multiplier>1)
			$finishers_multiplier=1;

		// do our fq calcultaions //
		$fq=($wcp_multiplier+$uci_multiplier+$finishers_multiplier)/$divider;
		$final_fq=($fq+$wcp_multiplier+$finishers_multiplier)/$divider;

		// build final object //
		$fq_obj->uci_points_in_field=$uci_points_in_field;
		$fq_obj->wcp_points_in_field=$wcp_points_in_field;
		$fq_obj->uci_multiplier=$uci_multiplier;
		$fq_obj->wcp_multiplier=$wcp_multiplier;
		$fq_obj->race_class_number=$race_class_number;
		$fq_obj->finishers_multiplier=$finishers_multiplier;
		$fq_obj->divider=$divider;
		$fq_obj->math_fq=$fq;
		$fq_obj->fq=$final_fq;

		return $fq_obj;
	}

	/**
	 * get_points_before_race function.
	 *
	 * @access public
	 * @param bool $season (default: false)
	 * @param bool $race_date (default: false)
	 * @param string $type (default: 'uci')
	 * @param bool $rider_name (default: false)
	 * @return void
	 */
	public function get_points_before_race($season=false,$race_date=false,$type='uci',$rider_name=false) {
		global $wpdb,$uci_curl;

		if (!$season || !$race_date)
			return 0;

		$race_date=date('Y-m-d',strtotime($race_date));
		$where='';

		if ($type=='wcp')
			$where=" AND races.class='cdm'";

		if ($rider_name)
			$where.=" AND results.name=\"{$rider_name}\"";

		$sql="
			SELECT
				IFNULL(SUM(results.par),0) AS points
			FROM $uci_curl->table AS races
			LEFT JOIN $uci_curl->results_table AS results
			ON races.code=results.code
			WHERE races.season='{$season}'
				AND STR_TO_DATE(races.date,'%e %M %Y') < '{$race_date}'
				$where
		";
//echo $sql.'<br>';
		$points=$wpdb->get_var($sql);

		return $points;
	}

	/**
	 * append_previous_race_points function.
	 *
	 * @access public
	 * @param bool $season (default: false)
	 * @param bool $race_date (default: false)
	 * @param bool $results (default: false)
	 * @return void
	 */
	public function append_previous_race_points($season=false,$race_date=false,$results=false) {
		if (!$results)
			return false;

		foreach ($results as $rider) :
			$rider->total_uci_points=$this->get_points_before_race($season,$race_date,'uci',$rider->rider);
			$rider->total_wcp_points=$this->get_points_before_race($season,$race_date,'wcp',$rider->rider);
		endforeach;

		return $results;
	}

	/**
	 * get_average_finishers function.
	 *
	 * @access protected
	 * @param bool $season (default: false)
	 * @param bool $class (default: false)
	 * @return void
	 */
	protected function get_average_finishers($season=false,$class=false) {
		global $wpdb,$uci_curl;

		$CrossSeasons=new CrossSeasons();
		$seasons=$CrossSeasons->seasons;
		$where=array();
		$finishers=array();

		if ($season)
			$where[]="races.season='{$season}'";

		if ($class)
			$where[]="class='{$class}'";

		if (!empty($where))
			$where='WHERE '.implode(' AND ',$where);

		if (!$season) :
			foreach ($seasons as $s) :
				$sql="
					SELECT
						class,
						SUM(finishers) AS total_finishers,
						SUM(total_races) AS total_races
					FROM
					(
						SELECT
							races.class AS class,
							0 AS finishers,
							COUNT(*) AS total_races
						FROM $uci_curl->table AS races
						WHERE season='{$s}'
						GROUP BY class

						UNION

						SELECT
							races.class AS class,
							COUNT(results.name) AS finishers,
							0 AS total_races
						FROM $uci_curl->table AS races
						LEFT JOIN $uci_curl->results_table AS results
						ON races.code=results.code
						WHERE races.season='{$s}'
						GROUP BY class
					) t
					GROUP BY class
				";
				$finishers[$s]=$wpdb->get_results($sql);
			endforeach;
		else :
			$sql="
				SELECT
					class,
					SUM(finishers) AS total_finishers,
					SUM(total_races) AS total_races
				FROM
				(
					SELECT
						races.class AS class,
						0 AS finishers,
						COUNT(*) AS total_races
					FROM $uci_curl->table AS races
					$where
					GROUP BY class

					UNION

					SELECT
						races.class AS class,
						COUNT(results.name) AS finishers,
						0 AS total_races
					FROM $uci_curl->table AS races
					LEFT JOIN $uci_curl->results_table AS results
					ON races.code=results.code
					$where
					GROUP BY class
				) t
				GROUP BY class
			";
			$finishers[$season]=$wpdb->get_results($sql);
		endif;

		// append average finishers //
		foreach ($finishers as $classes) :
			foreach ($classes as $class) :
				$class->average_finishers=ceil($class->total_finishers/$class->total_races);
			endforeach;
		endforeach;

		return $finishers;
	}

}
?>