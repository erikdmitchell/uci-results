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
		$wpdb->delete($wpdb->ucicurl_rider_rankings, array('season' => $season)); // remove ranks from season to prevent dups
		$rider_ids=$wpdb->get_col("SELECT id FROM {$wpdb->ucicurl_riders}"); // get all rider ids

		WP_CLI::success('Rider rankings table for '.$season.' cleared.');

		// weekly points progress bar //
		$rider_id_count=count($rider_ids);
		$rider_id_counter=0;
		$weekly_points_progress = \WP_CLI\Utils\make_progress_bar( 'Updating weekly points', $rider_id_count );

		// update weekly points //
		for ( $i = 0; $i < $rider_id_count; $i++ ) :
    	$uci_results_rider_rankings->update_rider_weekly_points($rider_ids[$i], $season);
			$weekly_points_progress->tick();
		endfor;

		$weekly_points_progress->finish();

		// weekly ranks progress bar //
		$weeks_count=count($weeks);
		$weeks_counter=0;
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
	 * Displays a list of seasons
	 *
	 *
	 * @subcommand list-seasons
	*/
	public function list_seasons() {
		global $wpdb;

		$seasons=$wpdb->get_results("SELECT season FROM {$wpdb->ucicurl_races} GROUP BY season");

		WP_CLI\Utils\format_items( 'table', $seasons, array( 'season' ) );
	}

	/**
	 * Migrates members from WP eMember to Paid Memberships Pro
	 *
	 * ## OPTIONS
	 *
	 * <ids>
	 * : A comma seperated string of WP eMember - PMPro ids
	 *
	 * [--dry-run]
	 * : SHould we do a dry run and not acutally change anything
	 *
	 * ## EXAMPLES
	 *
	 * wp wpempmpro migrate-members 2-1,3-2,4-3,5-4
	 *
	 * @subcommand migrate-members
	*/
	//public function migrate_members($args, $assoc_args) {
/*
		global $wpem_pmpro_level_map, $wpem_pmpro_migrate_members;

		$member_data_table=array();
		$dry_run=\WP_CLI\Utils\get_flag_value($assoc_args,'dry-run');
		$wpem_members=wpem_pmpro_get_wpem_members(array('member_id'));
		$sucess=0;
		$total=count($wpem_members);
		$dr_progress=\WP_CLI\Utils\make_progress_bar('Building migrating members table', $total);

		if (!wpem_pmpro_has_map())
			WP_CLI::error('A map needs to be setup first. It can be done on the user side.');

		// set our ids //
		if (isset($args[0]) || isset($assoc_args['ids'])) :
			if (isset($args[0])) :
				$ids=explode(',',$args[0]);
			elseif (isset($assoc_args['ids'])) :
				$ids=explode(',',$assoc_args['ids']);
			endif;
		endif;

		// cycle through our wp emembers and migrate //
		foreach ($wpem_members as $wpem_member_id) :
			$member_data=$wpem_pmpro_migrate_members->get_wpem_member($wpem_member_id); // get member data

			// if dry run, we are going to do a table out put //
			if ($dry_run) :
				$member_data_table[]=$member_data;
				$dr_progress->tick(); // update progress bar
			else : // not a dry run - lets do it!
				$user_id=$wpem_pmpro_migrate_members->create_user($member_data); // create user

				// if we have an id (succesful) proceed //
				if ($user_id) :
					$usermeta_output='';
					$pmpro_output='';
					$usermeta=$wpem_pmpro_migrate_members->update_user_meta($user_id, $member_data); // add profile meta
					$pmpro=$wpem_pmpro_migrate_members->setup_pmpro_membership($user_id, $member_data, $wpem_pmpro_level_map); // setup pmpro membership

					if ($usermeta)
						$usermeta_output=__('User profile updated.','wpem-pmpro');

					if ($pmpro)
						$pmpro_output=__('PM Pro membership created.','wpem-pmpro');

					WP_CLI::success('Member (#'.$user_id.') migrated. '.$usermeta_output.' '.$pmpro_output);
					$sucess++;
				else :
					WP_CLI::warning('User not created (WPEM# '.$wpem_member_id.')');
				endif;
			endif;
		endforeach;

		// vary output if dry run //
		if (!$dry_run) :
			WP_CLI::success('Migrate members is complete.');
			WP_CLI::line($sucess.' out of '.$total.' members migrated');
		else :
			$dr_progress->finish(); // finish progress bar
			WP_CLI::line(print_r($member_data_table, true));
			WP_CLI::success('Migrate members dry run is complete.');
		endif;
*/
	//

}

WP_CLI::add_command('uciresults', 'UCIResultsCLI');
