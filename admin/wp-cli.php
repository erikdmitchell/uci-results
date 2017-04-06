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
			
}

WP_CLI::add_command('uciresults', 'UCIResultsCLI');
