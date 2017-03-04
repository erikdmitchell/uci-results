<?php
	
function admin_season_scripts_styles() {
	global $wp_scripts;

	// get registered script object for jquery-ui
	$ui = $wp_scripts->query('jquery-ui-core');
		
	if (isset($_GET['taxonomy']) && $_GET['taxonomy']=='season') :
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('uci-results-admin-season', UCI_RESULTS_ADMIN_URL.'js/seasons.js', array('jquery-ui-datepicker'), '0.1.0', true);

		wp_enqueue_style('jquery-ui-smoothness', "https://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css");
	endif;
}
add_action('admin_enqueue_scripts', 'admin_season_scripts_styles');


function season_edit_success($term_id) {
	update_term_meta($term_id, '_season_start', $_POST['term_meta']['season_start']);
	update_term_meta($term_id, '_season_end', $_POST['term_meta']['season_end']);
}
add_action('edited_season', 'season_edit_success');
add_action('created_season', 'season_edit_success');

function season_add_new_meta_fields() {
	$html=null;
	
	$html.='<div class="form-field">';
		$html.='<label for="term_meta[season_start]">Start of the Season</label>';
		$html.='<input type="text" name="term_meta[season_start]" class="uci-season-dp" id="term_meta[season_start]" value="">';
		$html.='<p class="description">Select the first week of the season.</p>';
	$html.='</div>';

	$html.='<div class="form-field">';
		$html.='<label for="term_meta[season_end]">Start of the Season</label>';
		$html.='<input type="text" name="term_meta[season_end]" class="uci-season-dp" id="term_meta[season_end]" value="">';
		$html.='<p class="description">Select the first week of the season.</p>';
	$html.='</div>';

	echo $html;
}
add_action('season_add_form_fields', 'season_add_new_meta_fields');

function season_edit_meta_field($term) {
	$season_start=get_term_meta($term->term_id, '_season_start', true);
	$season_end=get_term_meta($term->term_id, '_season_end', true);
		
	$html=null;
			
	$html.='<tr class="form-field">';
		$html.='<th scope="row" valign="top"><label for="term_meta[season_start]">Start of the Season</label></th>';
		$html.='<td>';
			$html.='<input type="text" name="term_meta[season_start]" class="uci-season-dp regular-text" id="term_meta[season_start]" value="'.esc_attr($season_start).'">';
			$html.='<p class="description">Select the first week of the season.</p>';
		$html.='</td>';
	$html.='</tr>';

	$html.='<tr class="form-field">';
		$html.='<th scope="row" valign="top"><label for="term_meta[season_end]">End of the Season</label></th>';
		$html.='<td>';
			$html.='<input type="text" name="term_meta[season_end]" class="uci-season-dp regular-text" id="term_meta[season_end]" value="'.esc_attr($season_end).'">';
			$html.='<p class="description">Select the last week of the season.</p>';
		$html.='</td>';
	$html.='</tr>';

	echo $html;
}
add_action('season_edit_form_fields', 'season_edit_meta_field', 10, 1);

/**
 * CrossSeasons class.
 *
 * @since Version 1.1.0
 */
class CrossSeasons {

	public function __construct() {

	}

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

		$arr=array();
		$weeks=$this->get_weeks($season);
		$counter=1;

		foreach ($weeks as $week) :
			$races=$this->get_races_in_dates($week[0],$week[1]);
			$arr[]=array(
				'week' => $counter,
				'start_date' => $week[0],
				'end_date' => $week[1],
				'races' => $races
			);
			$counter++;
		endforeach;

		$object=json_decode(json_encode($arr),FALSE); // convert to object

		return $object;
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

	public function get_week_from_date($date=false, $season=false) {
		if (!$date || !$season)
			return false;

		$weeks=$this->get_weeks($season);
		$week_num=1;

		foreach ($weeks as $week) :
			if ((strtotime($date)>=strtotime($week[0])) && (strtotime($date)<=strtotime($week[1])))
				return $week_num;

			$week_num++;
		endforeach;

		return false;
	}

}

/*
function uci_results_build_season_weeks($season='') {
	global $uci_results_admin_pages;

	$season_weeks=get_option('uci_results_season_weeks', array());

	// check for a specific season //
	if (!empty($season)) :
		foreach ($season_weeks as $key => $value) :
			if ($value['season']==$season)
				$season_weeks[$key]=uci_results_set_season_weeks($season);
		endforeach;
	else :
		$season_weeks=[]; // clear all

		foreach ($uci_results_admin_pages->config->urls as $season => $url) :
			$season_weeks[]=uci_results_set_season_weeks($season);
		endforeach;
	endif;

	update_option('uci_results_season_weeks', $season_weeks);
}
*/

/*
function uci_results_set_season_weeks($season='') {
	global $uci_results_admin_pages, $uci_results_add_races;

	$uci_results_urls=get_object_vars($uci_results_admin_pages->config->urls);

	// see if the season is set in our admin pages (config) //
	if (!isset($uci_results_urls[$season]))
		return false;

	$season_url=$uci_results_urls[$season];
	$season_races=$uci_results_add_races->get_race_data($season, false, true, $season_url);

	$first_race=end($season_races);
	$last_race=reset($season_races);

	$dates=array(
		'season' => $season,
		'start' => date('M j Y', strtotime($first_race->date)),
		'end' => date('M j Y', strtotime($last_race->date)),
	);
	$season_data=uci_results_add_weeks_to_season($dates);

	return $season_data;
}
*/

/*
function uci_results_add_weeks_to_season($season='') {
	if (empty($season))
		return false;

	// start (first) week //
	$start_date_arr=explode(' ', $season['start']); // get start day
	$next_monday=strtotime('monday', mktime(0, 0, 0, date('n', strtotime($season['start'])), $start_date_arr[1], $start_date_arr[2])); // get next monday
	$next_sunday=strtotime('sunday', $next_monday); // get next sunday
	$first_monday=strtotime('-1 week', $next_monday); // prev monday is start
	$first_sunday=strtotime('-1 week', $next_sunday); // prev sunday is start

	// end (last) week //
	$end_date_arr=explode(' ', $season['end']); // get end day
	$last_monday=strtotime('monday', mktime(0, 0, 0, date('n', strtotime($season['end'])), $end_date_arr[1], $end_date_arr[2])); // get next monday
	$last_sunday=strtotime('sunday', $last_monday); // get next sunday
	$final_monday=strtotime('-1 week', $last_monday); // prev monday is start
	$final_sunday=strtotime('-1 week', $last_sunday); // prev sunday is start

	// build out all our weeks //
	$monday=$first_monday;
	$sunday=$first_sunday;

	while ($monday != $final_monday) :
    $season['weeks'][]=array(
    	'start' => date('c', $monday),
    	'end' => date('c', $sunday)
    );

    $monday=strtotime('+1 week', $monday);
    $sunday=strtotime('+1 week', $sunday);
	endwhile;

	// append final week //
	$season['weeks'][]=array(
		'start' => date('c', $final_monday),
		'end' => date('c', $final_sunday)
	);

	return $season;
}
*/

?>