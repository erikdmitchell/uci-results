<?php
/**
 * FantasyCycling class.
 *
 * @since Version 0.0.1
 */
class FantasyCycling {

	public $version='0.0.2';
	public $wp_option_version='fantasy_cycling_version';

	public function __construct() {
		include_once(plugin_dir_path(__FILE__).'databases.php');
		include_once(plugin_dir_path(__FILE__).'functions.php');
		include_once(plugin_dir_path(__FILE__).'shortcodes.php');

		if ($this->version>get_option($this->wp_option_version)) :
			$this->add_pages();
			update_option($this->wp_option_version,$this->version);
		endif;
	}

	public function scripts_styles() {

	}

	/**
	 * add_pages function.
	 *
	 * checks and adds neccessary pages for the fantasy stuff to work
	 *
	 * @access public
	 * @return void
	 */
	public function add_pages() {
		if (get_page_by_title('Fantasy')==NULL) :
				$fantasy=array(
					'post_content' => '[fantasy-cycling]',
					'post_title' => 'Fantasy',
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_parent' => 0,
				);
				$fantasy_id=wp_insert_post($fantasy);
		else :
			$page=get_page_by_title('Fantasy');
			$fantasy_id=$page->ID;
		endif;

		if (get_page_by_title('Team')==NULL) :
				$team=array(
					'post_content' => '[fantasy-cycling-team]',
					'post_title' => 'Team',
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_parent' => $fantasy_id,
				);
				wp_insert_post($team);
		endif;

		if (get_page_by_title('Standings')==NULL) :
				$standings=array(
					'post_content' => '[fantasy-cycling-standings]',
					'post_title' => 'Standings',
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_parent' => $fantasy_id,
				);
				wp_insert_post($standings);
		endif;

		/*
		if (get_page_by_title('Rider')==NULL) :
				$rider=array(
					'post_content' => '[fantasy-cycling-rider]',
					'post_title' => 'Rider',
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_parent' => $fantasy_id,
				);
				wp_insert_post($rider);
		endif;
		*/
	}

}

new FantasyCycling();
?>
<!--
Workflow
User Register/Login
Team Page
Lists Roster
Add/Edit Roster
Rider Name, UCI Points, WCP, SOS and overall ranking
Roster Limits
6 riders
Must chose 2 from Top 10 Last Season
1 From top 20
1 From top 30
2 From rest
Ranking Page
Utilized by UCI Points (no limit)
Single Team Page
Rider Click Through to rider page
-->