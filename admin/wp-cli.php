<?php
// takes our migration to the next level using wp cli

if (!defined('WP_CLI') || !WP_CLI)
	return false;

/**
 * Implements a series of useful WP-CLI commands.
 */
class UCIResultsCLI extends WP_CLI_Command {

	/**
   * A basic message to know this thing is running.
  */
	public function active() {
		WP_CLI::success('UCI Results CLI is active!');
	}

	/**
	 * Add races (and results) to our db from a season
	 *
	 * ## OPTIONS
	 *
	 * <season>
	 * : the season
	 *
	 * ## EXAMPLES
	 *
	 * wp uciresults add-races 2015/2016
	 *
	 * @subcommand add-races
	*/
	public function add_races($args, $assoc_args) {
		global $wpdb, $uci_results_add_races, $uci_results_admin_pages;

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

		// find our urls //
		if (!isset($uci_results_admin_pages->config->urls->$season) || empty($uci_results_admin_pages->config->urls->$season)) :
			WP_CLI::error('No url found.');
		else :
			$url=$uci_results_admin_pages->config->urls->$season;
		endif;

		$races=$uci_results_add_races->get_race_data($season, false, true, $url);

		if (empty($races))
			WP_CLI::error('No races found.');

		// process our races //
		foreach ($races as $race) :
			$code=$uci_results_add_races->build_race_code(array('event' => $race->event, 'date' => $race->date));

			if (!$code) :
				WP_CLI::warning("Code for $race->event not created!");
				continue;
			endif;

			// add to db //
			if (!$uci_results_add_races->check_for_dups($code)) :
				$formatted_result=$uci_results_add_races->add_race_to_db($race);
				$result=strip_tags($formatted_result);
				WP_CLI::success($result);
			else :
				WP_CLI::warning("Already in db. ($code)");
			endif;

		endforeach;

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
		global $wpdb, $uci_results_rider_rankings, $ucicurl_races;

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

		$weeks=$ucicurl_races->weeks($season); // get weeks in season
		$wpdb->delete($wpdb->uci_results_rider_rankings, array('season' => $season)); // remove ranks from season to prevent dups
		$rider_ids=$wpdb->get_col("SELECT id FROM {$wpdb->uci_results_riders}"); // get all rider ids

		WP_CLI::success('Rider rankings table for '.$season.' cleared.');

		// weekly points progress bar //
		$rider_id_count=count($rider_ids);
		$weekly_points_progress = \WP_CLI\Utils\make_progress_bar( 'Updating weekly points', $rider_id_count );

		// update weekly points //
		for ( $i = 0; $i < $rider_id_count; $i++ ) :
    	$uci_results_rider_rankings->update_rider_weekly_points($rider_ids[$i], $season);
			$weekly_points_progress->tick();
		endfor;

		$weekly_points_progress->finish();

		// weekly ranks progress bar //
		$weeks_count=count($weeks);
		$week_rank_progress = \WP_CLI\Utils\make_progress_bar( 'Updating weekly ranks', $weeks_count );

		// update weekly points //
		for ( $i = 0; $i < $weeks_count; $i++ ) :
    	$uci_results_rider_rankings->update_rider_weekly_rankings($season, $weeks[$i]);
			$week_rank_progress->tick();
		endfor;

		$week_rank_progress->finish();

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

		$seasons=$wpdb->get_results("SELECT season FROM {$wpdb->uci_results_races} GROUP BY season");

		WP_CLI\Utils\format_items( 'table', $seasons, array( 'season' ) );
	}

	/**
	 * Displays a list of seasons with results in db
	 *
	 *
	 * @subcommand list-seasons
	*/
	public function list_seasons_with_urls() {
		global $uci_results_admin_pages;

		foreach ($uci_results_admin_pages->config->urls as $season => $url) :
			$seasons[]=array('season' => $season);
		endforeach;

		//WP_CLI\Utils\format_items( 'table', $seasons, array( 'season' ) );
		WP_CLI\Utils\format_items( 'table', $seasons, array( 'season' ) );
	}

}

WP_CLI::add_command('uciresults', 'UCIResultsCLI');
