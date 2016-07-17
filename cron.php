<?php

function uci_results_add_races() {
	global $wpdb, $uci_results_add_races;

	$road_test_url='http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=102&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=490&StartDateSort=20151228&EndDateSort=20161023&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8';

// get_race_data($season=false, $limit=false, $raw=false, $url=false)
$race_data=$uci_results_add_races->get_race_data(false, false, true, $road_test_url);

echo '<pre>';
print_r($race_data);
echo '</pre>';

// ajax_get_race_data_non_db  -- gets an output of races via the season selected

/*
prepare_add_races_to_db - ajax_prepare_add_races_to_db
add_race_to_db - ajax_add_race_to_db
update_rider_rankings
 - ajax_update_rider_weekly_points
 - ajax_update_rider_weekly_rank
*/
	return;
}
add_action('uci_results_add_races', 'uci_results_add_races');
?>