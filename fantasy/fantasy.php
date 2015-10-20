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
		include_once(plugin_dir_path(__FILE__).'admin.php');
		include_once(plugin_dir_path(__FILE__).'functions.php');
		include_once(plugin_dir_path(__FILE__).'shortcodes.php');

		if ($this->version>get_option($this->wp_option_version)) :
			$this->add_pages();
			update_option($this->wp_option_version,$this->version);
		endif;

		add_action('init',array($this,'add_cpt'));
		add_action('init',array($this,'add_taxonomies'));
		add_action('wp_enqueue_scripts',array($this,'scripts_styles'));
	}

	public function scripts_styles() {
		wp_enqueue_style('fantasy-cycling-style',plugins_url('/css/style.css',__FILE__));
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

	/**
	 * add_categories function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_cpt() {
		register_post_type('fantasy-cycling',
			array(
				'labels' => array(
					'name' => __('Fantasy Cycling'),
					'singular_name' => __('Fantasy Cycling')
				),
				'public' => true,
				'has_archive' => true,
			)
		);
	}

	/**
	 * add_taxonomies function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_taxonomies() {
		register_taxonomy(
			'posttype',
			'fantasy-cycling',
			array(
				'label' => __( 'Post Type' ),
				//'rewrite' => array( 'slug' => 'person' ),
				'hierarchical' => true,
			)
		);
	}

}

new FantasyCycling();
?>