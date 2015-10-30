<?php
	/**
 * CrossSeasons class.
 *
 * @since Version 1.1.0
 */
class CrossSeasons {

	public $table;
	public $seasons=array();

	public function __construct() {
		global $wpdb;

		$this->table=$wpdb->prefix.'uci_races';
		$this->seasons=$wpdb->get_col("SELECT season FROM $this->table WHERE season!='false' GROUP BY season");
	}

	/**
	 * get_weeks function.
	 *
	 * @access public
	 * @param bool $season (default: false)
	 * @return void
	 */
	public function get_weeks($season=false) {
		if (!$season)
			return false;

		// split season into start year (0) and end year (1) //
		$season_arr=explode('/',$season);
		$start_date='01-09-'.$season_arr[0]; // sep 1 start date
		$end_date='01-03-'.$season_arr[1]; // march 1 end date
		$start_week=date('W',strtotime($start_date))-1; // -1 for a buffer
		$end_week=date('W',strtotime($end_date))+1; // +1 for buffer
		$last_week_number_in_year=date('W',strtotime('31-12-'.$season_arr[0]));
		$weeks=array();

		if ($last_week_number_in_year==01)
			$last_week_number_in_year=52;

		// compile first/last date of all weeks in a season
		for ($week=$start_week;$week<=$last_week_number_in_year;$week++) :
			$weeks[]=$this->get_start_and_end_date_of_week($week,$season_arr[0]);
		endfor;
		for ($week=1;$week<=$end_week;$week++) :
			$weeks[]=$this->get_start_and_end_date_of_week($week,$season_arr[1]);
		endfor;

		return $weeks;
	}

	/**
	 * get_start_and_end_date_of_week function.
	 *
	 * @access public
	 * @param mixed $week
	 * @param mixed $year
	 * @return void
	 */
	public function get_start_and_end_date_of_week($week,$year) {
		$return=array();
    $time = strtotime("1 January $year", time());
    $day = date('w', $time);
    $time += ((7*$week)+1-$day)*24*3600;
    $return[0] = date('Y-m-d', $time);
    $time += 6*24*3600;
    $return[1] = date('Y-m-d', $time);

    return $return;
	}

	public function get_races_in_season_by_week($season=false) {
		if (!$season)
			return false;

		$races=array();
		$weeks=$this->get_weeks($season);

		foreach ($weeks as $week) :
			$races[]=$this->get_races_in_dates($week[0],$week[1]);
		endforeach;

		return $races;
	}

	public function get_races_in_dates($start_date=false,$end_date=false) {
		global $wpdb;
		if (!$start_date || !$end_date)
			return false;

		$sql="
			SELECT
				date,
				code,
				event
			FROM $this->table
			WHERE (STR_TO_DATE(date,'%e %M %Y') BETWEEN '{$start_date}' AND '{$end_date}')
		";
		$races=$wpdb->get_results($sql);

		return $races;
	}

}
?>