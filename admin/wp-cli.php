<?php
// takes our migration to the next level using wp cli

if (!defined('WP_CLI') || !WP_CLI)
	return false;

/**
 * Implements a series of useful WP-CLI commands.
 */
class UCIResultsCLI extends WP_CLI_Command {

	/**
	 * Add races (and results) to our db from a season
	 *
	 * ## OPTIONS
	 *
	 * [--season=<season>]
	 * : the season
	 *
	 * ## EXAMPLES
	 *
	 * wp uciresults add-races 2015/2016
	 *
	 * @subcommand add-races
	*/
/*
	public function add_races($args, $assoc_args) {
		global $uci_results_automation;

		$season=uci_results_get_current_season();

		// set our season //
		if (isset($args[0]) || isset($assoc_args['season'])) :
			if (isset($args[0])) :
				$season=$args[0];
			elseif (isset($assoc_args['season'])) :
				$season=$assoc_args['season'];
			endif;
		endif;

		if (!$season || $season=='')
			WP_CLI::error('No season found.');

		$uci_results_automation->add_races($season, 'wpcli');
	
		WP_CLI::success("All done!");
	}
*/

	/**
	 * Update rider rankings in a season
	 *
	 * ## OPTIONS
	 *
	 * <season>
	 * : the season
	 *
	 * ## EXAMPLES
	 *
	 * wp uciresults update-rider-rankings 2014/2015
	 *
	 * @subcommand update-rider-rankings
	*/
/*
	public function update_rider_rankings($args, $assoc_args) {
		global $uci_results_automation;

		$season=0;

		// set our season //
		if (isset($args[0]) || isset($assoc_args['season'])) :
			if (isset($args[0])) :
				$season=$args[0];
			elseif (isset($assoc_args['season'])) :
				$season=$assoc_args['season'];
			endif;
		endif;

		if (!$season || $season=='')
			WP_CLI::error('No season found.');

		$uci_results_automation->update_rider_rankings($season, 'wpcli');

		WP_CLI::success('Update rider rankings complete');
	}
*/
	
	/**
	 * Update series overall rankings
	 *
	 * ## OPTIONS
	 *
	 * <series_id>
	 * : series id
	 *
	 * <season>
	 * : the season
	 *
	 * ## EXAMPLES
	 *
	 * wp uciresults series-overall 4 2015/2016
	 *
	 * @subcommand series-overall
	*/
	public function update_series_overall($args, $assoc_args) {
		global $wpdb, $uci_results_rider_rankings;

		$series_id=0;
		$season='';

		// set our series_id //
		if (isset($args[0]) || isset($assoc_args['series_id'])) :
			if (isset($args[0])) :
				$series_id=$args[0];
			elseif (isset($assoc_args['series_id'])) :
				$series_id=$assoc_args['series_id'];
			endif;
		endif;

		// set our season //
		if (isset($args[1]) || isset($assoc_args['season'])) :
			if (isset($args[1])) :
				$season=$args[1];
			elseif (isset($assoc_args['season'])) :
				$season=$assoc_args['season'];
			endif;
		endif;

		if (!$series_id || $series_id=='')
			WP_CLI::error('No series id found.');

		if (!$season || empty($season) || $season=='')
			WP_CLI::error('No season found.');

		// clear db //
		$wpdb->delete($wpdb->uci_results_series_overall, array('series_id' => $series_id, 'season' => $season));

		WP_CLI::success('Series overall table for '.$series_id.' - '.$season.' cleared.');

		// build vars and sql //
		$sql="
			SELECT
			  results.rider_id,
			  SUM(results.pcr) AS points
			FROM {$wpdb->uci_results_results} AS results
			INNER JOIN {$wpdb->uci_results_races} AS races ON results.race_id = races.id
			WHERE races.series_id = {$series_id}
			AND season='{$season}'
			GROUP BY results.rider_id
			ORDER BY points DESC
		";
		$riders=$wpdb->get_results($sql);
		$rank=1;

		// weekly points progress bar //
		$rider_count=count($riders);
		$progress_bar = \WP_CLI\Utils\make_progress_bar( 'Updating weekly points', $rider_count );

		// input into db //
		foreach ($riders as $rider) :
			$data=array(
				'rider_id' => $rider->rider_id,
				'points' => $rider->points,
				'series_id' => $series_id,
				'season' => $season,
				'rank' => $rank
			);

			$rank++;

			$wpdb->insert($wpdb->uci_results_series_overall, $data);

			$progress_bar->tick();
		endforeach;

		$progress_bar->finish();

		WP_CLI::success('Update series overall complete');
	}

	/**
	 * Displays a list of seasons with results in db
	 *
	 *
	 * @subcommand list-seasons-db
	*/
	public function list_seasons_in_db() {
		global $wpdb;

		$terms=get_terms(array(
			'taxonomy' => 'season',
			'hide_empty' => false,
		));

		$fields=array('term_id', 'name', 'slug', 'count');
		
		WP_CLI\Utils\format_items( 'table', $terms, $fields);
	}

	/**
	 * Displays a list of seasons with results in db
	 *
	 *
	 * @subcommand list-seasons
	*/
	public function list_seasons_with_urls() {
		global $uci_results_admin;

		foreach ($uci_results_admin->config->urls as $season => $url) :
			$seasons[]=array('season' => $season);
		endforeach;

		//WP_CLI\Utils\format_items( 'table', $seasons, array( 'season' ) );
		WP_CLI\Utils\format_items( 'table', $seasons, array( 'season' ) );
	}

	/**
	 * Get the raw data from a seasons races and/or results
	 *
	 * ## OPTIONS
	 *
	 * <season>
	 * : the season
	 *
	 * [--race_id=<race_id>]
	 * : Get a specific race via id. DOES NOT WORK
	 *
	 * [--limit=<limit>]
	 * : Limit races returned.
	 *
	 * [--output=<output>]
	 * : Type of output.
	 * ---
	 * default: raw
	 * options:
	 *   - raw
	 *   - table
	 *   - yaml
	 *   - json
	 *	 - csv
	 *	 - php
	 * ---	 
	 *
	 * ## EXAMPLES
	 *
	 * wp uciresults get-race-data 2015/2016
	 *
	 * @subcommand get-race-data
	*/
/*
	public function get_race_data($args, $assoc_args) {
		global $wpdb, $uci_results_add_races, $uci_results_admin;

		$season=0;
		$race_id=0;
		$limit=-1;
		$output='raw';
		$formatted=false;

		// set our season //
		if (isset($args[0]) || isset($assoc_args['season'])) :
			if (isset($args[0])) :
				$season=$args[0];
			elseif (isset($assoc_args['season'])) :
				$season=$assoc_args['season'];
			endif;
		endif;

		// set our race id (optional) //
		if (isset($assoc_args['race_id']))
			$race_id=$assoc_args['race_id'];

		// set our limit (optional) //
		if (isset($assoc_args['limit']))
			$limit=$assoc_args['limit'];

		// set our output (optional) //
		if (isset($assoc_args['output']))
			$output=$assoc_args['output'];

		if (!$season || $season=='')
			WP_CLI::error('No season found.');
			
		if ($race_id)
			WP_CLI::log("Race ID: $race_id");

		// find our urls //
		if (!isset($uci_results_admin->config->urls->$season) || empty($uci_results_admin->config->urls->$season)) :
			WP_CLI::error('No url found.');
		else :
			$url=$uci_results_admin->config->urls->$season;
		endif;

		$races=$uci_results_add_races->get_race_data($season, $limit, true, $url);

		if (empty($races))
			WP_CLI::error('No races found.');

		if ($output!='php' && $output!='raw')
			$formatted=true;

		// process our races //
		foreach ($races as $race) :
			$race_data=$uci_results_add_races->get_add_race_to_db($race);
			$results_data=$uci_results_add_races->get_add_race_to_db_results($race_data['link'], $formatted, $race_id);

			if ($output=='php') :
				$stdout="\n\n";
				$stdout.='$race_data='.var_export($race_data, true).';';
				$stdout.="\n";
				
				if (!$formatted) :
					$stdout.='$results_data='.var_export(serialize($results_data), true).';';
				else :
					$stdout.='$results_data='.var_export($results_data, true).';';
				endif;
				
				$stdout.="\n\n";
				WP_CLI::log($stdout);
			elseif ($output!='raw') :
				$race_data=array($race_data);
				$fields=array('date', 'event', 'nat', 'class', 'winner', 'season', 'link', 'code', 'week');
				
				WP_CLI\Utils\format_items($output, $race_data, $fields); // race
				
				// results //
				$fields=array('place', 'name', 'nat', 'age', 'result', 'par', 'pcr', 'rider_id');
				
				WP_CLI\Utils\format_items($output, $results_data, $fields);			
			else :
				print_r($race_data);
				print_r($results_data);
			endif;
		endforeach;

		WP_CLI::success("All done!");
	}
*/

	/**
	 * Add race results to db
	 *
	 * ## OPTIONS
	 *
	 * <discipline>
	 * : the discipline
	 *
	 * <season>
	 * : the season
	 *
	 * [--limit=<limt>]
	 * : Number of races
	 *
	 * ## EXAMPLES
	 *
	 * wp uciresults add-races-results road 2017 --limit=20
	 *
	 * @subcommand add-races-results
	*/
	public function add_races_results($args, $assoc_args) {
		global $uci_results_add_races;

		$discipline='';
		$season='';
		$limit=-1;

		// set our discipline //
		if (isset($args[0]) || isset($assoc_args['discipline'])) :
			if (isset($args[0])) :
				$discipline=$args[0];
			elseif (isset($assoc_args['discipline'])) :
				$discipline=$assoc_args['discipline'];
			endif;
		endif;

		// set season //
		if (isset($args[1]) || isset($assoc_args['season'])) :
			if (isset($args[1])) :
				$season=$args[1];
			elseif (isset($assoc_args['season'])) :
				$season=$assoc_args['season'];
			endif;
		endif;

		// set our limit (optional) //
		if (isset($assoc_args['limit']))
			$limit=$assoc_args['limit'];
			
		if (!$discipline || $discipline=='')
			WP_CLI::error('No discipline found.');

		if (!$season || $season=='')
			WP_CLI::error('No season found.');			
			
		$races=$uci_results_add_races->get_race_data(array(
			'season' => $season,
			'limit' => $limit,
			'raw' => true,
			'discipline' => $discipline,	
		));

		foreach ($races as $race) :
			$code=$uci_results_add_races->build_race_code($race);
			$race_encoded=json_encode($race);

			if ($uci_results_add_races->check_for_dups($code) && $race->single) :
				//echo '<div class="updated add-race-to-db-message">Already in db. ('.$code.')</div>';			
				echo "in db - disp message\n";		
			else :
				echo "add to db via other command (wp cli)\n";
				echo $race->event."\n";
				echo json_encode($race)."\n";	
				//echo $uci_results_add_races->add_race_to_db($_POST['race']);	
			endif;
		endforeach;
		
		WP_CLI::success('Add race results complete.');
	}

	/**
	 * Add race results to db
	 *
	 * ## OPTIONS
	 *
	 * <race>
	 * : race in json format
	 *
	 * ## EXAMPLES
	 *
	 * wp uciresults add-race-results '{"date":"10 Jun-18 Jun 2017","url":"http:\/\/www.uci.infostradasports.com\/asp\/redirect\/uci.asp?Page=resultoverview&SportID=102&CompetitionID=20583&CompetitionCodeInv=1&EditionID=1628203&SeasonID=492&EventID=12146&EventPhaseID=1628237&GenderID=1&ClassID=1&Phase1ID=-1&Detail=1&DerivedEventPhaseID=-1&Ranking=0","event":"Tour de Suisse","nat":"SUI","class":"UWT","winner":"\u0160PILAK (SLO)","single":0,"start":"10 Jun 2017","end":"18 Jun 2017","stages":[{"date":"10 Jun 2017","name":"Stage 1 (ITT)","winner":"DENNIS (AUS)"},{"date":"11 Jun 2017","name":"Stage 2","winner":"GILBERT (BEL)"},{"date":"12 Jun 2017","name":"Stage 3","winner":"MATTHEWS (AUS)"},{"date":"13 Jun 2017","name":"Stage 4","winner":"WARBASSE (USA)"},{"date":"14 Jun 2017","name":"Stage 5","winner":"SAGAN (SVK)"},{"date":"15 Jun 2017","name":"Stage 6","winner":"POZZOVIVO (ITA)"},{"date":"16 Jun 2017","name":"Stage 7","winner":"\u0160PILAK (SLO)"},{"date":"17 Jun 2017","name":"Stage 8","winner":"SAGAN (SVK)"},{"date":"18 Jun 2017","name":"Stage 9 (ITT)","winner":"DENNIS (AUS)"}],"discipline":"road","season":"2017"}'
	 *
	 * @subcommand add-race-results
	*/
	public function add_race_results($args, $assoc_args) {
		global $uci_results_add_races;
		
		$value = WP_CLI::get_value_from_arg_or_stdin($args, 0);
		$value = WP_CLI::read_value($value, $assoc_args);
		$race=json_decode($value);

		$uci_results_add_races->add_race_to_db($race);
		
		WP_CLI::success('add_race_results complete.');
	}
		
}

WP_CLI::add_command('uciresults', 'UCIResultsCLI');
