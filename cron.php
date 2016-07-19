<?php

function uci_results_add_races() {
	global $wpdb, $uci_results_add_races;

	$road_test_url='http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=102&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=490&StartDateSort=20151228&EndDateSort=20161023&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8';
	$races=$uci_results_add_races->get_race_data(false, 10, true, $road_test_url); // gets an output of races via the url

	// add race(s) to db //
	foreach ($races as $race) :
		$uci_results_add_races->add_race_to_db($race);
	endforeach;


/*
update_rider_rankings
 - ajax_update_rider_weekly_points
 - ajax_update_rider_weekly_rank
*/
	return;
}
add_action('uci_results_add_races', 'uci_results_add_races');
?>