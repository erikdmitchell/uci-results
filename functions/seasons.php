<?php
/**
 * uci_results_seasons_dropdown function.
 *
 * @access public
 * @param string $name (default: 'seasons')
 * @param string $selected (default: '')
 * @return void
 */
function uci_results_seasons_dropdown($name='seasons', $selected='') {
	wp_dropdown_categories(array(
		'show_option_none'   => 'Select One',
		'orderby'            => 'name',
		'order'              => 'DESC',
		'hide_empty'         => 0,
		'selected'           => $selected,
		'name'               => $name,
		'id'                 => $name,
		'class'              => 'seasons-dropdown',
		'taxonomy'           => 'season',
	));
}

/**
 * uci_get_season_weeks_dropdown function.
 * 
 * @access public
 * @param string $season (default: '')
 * @param string $selected (default: '')
 * @param string $name (default: 'week')
 * @return void
 */
function uci_get_season_weeks_dropdown($season='', $selected='', $name='week') {
	global $uci_cross_seasons;	
	
	$html=null;
	$weeks=$uci_cross_seasons->get_season_weeks($season);
	
	if (empty($weeks))
		return;

	$html.='<select id="'.$name.'" name="'.$name.'" class="'.$name.'">';
		$html.='<option value="0">-- Select Season --</option>';
			foreach ($weeks as $week) :
				$html.='<option value="'.$week->week.'" '.selected($selected, $week->week, false).'>'.$week->week.'</option>';
			endforeach;
	$html.='</select>';
	
	return $html;	
}

/**
 * uci_results_get_season_weeks function.
 *
 * @access public
 * @param string $season (default: '')
 * @return void
 */
function uci_results_get_season_weeks($season='') {
	global $uci_cross_seasons;	
	
	$html=null;
	$weeks=$uci_cross_seasons->get_season_weeks($season);
	
	if (empty($weeks))
		return;
		
	return $weeks;
}

/**
 * uci_results_get_default_rider_ranking_week function.
 *
 * @access public
 * @return void
 */
function uci_results_get_default_rider_ranking_week() {
	global $uci_cross_seasons;	
	
	$html=null;
	$season=uci_results_get_current_season();
	$weeks=$uci_cross_seasons->get_last_season_week($season->slug);
	
	if (empty($weeks))
		return;
		
	return $weeks;
}
?>