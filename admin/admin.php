<?php
global $uci_results_admin;

class UCIResultsAdmin {
	
	public $config=array();
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param array $config (default: array())
	 * @return void
	 */
	public function __construct($config=array()) {
		add_action('admin_menu', array($this, 'register_menu_page'));
		add_action('admin_enqueue_scripts', array($this, 'uci_results_api_admin_scripts_styles'));
		add_action('admin_init', array($this, 'save_settings'));
		add_action('admin_init', array($this, 'include_migration_files'));
		add_action('wp_ajax_uci_results_empty_db', array($this, 'ajax_empty_db'));
		add_action('wp_ajax_uci_results_remove_db', array($this, 'ajax_remove_db'));
		add_action('wp_ajax_uci_results_rider_rankings_dropdown', array($this, 'ajax_rider_rankings_dropdown'));

		$this->setup_config($config);		
	}

	/**
	 * uci_results_api_admin_scripts_styles function.
	 * 
	 * @access public
	 * @return void
	 */
	public function uci_results_api_admin_scripts_styles($hook) {
		global $wp_scripts;
		
		$jquery_ui_version=$wp_scripts->registered['jquery-ui-core']->ver;

		wp_enqueue_script('uci-results-admin', UCI_RESULTS_ADMIN_URL.'/js/admin.js', array('jquery'), '0.1.0',true);

		wp_enqueue_style('uci-results-api-admin-styles', UCI_RESULTS_ADMIN_URL.'css/admin.css', '0.1.0');	
		
		if ($hook=='toplevel_page_uci-results' && isset($_GET['subpage']) && $_GET['subpage']=='migration') :
			if (isset($_GET['version'])) :					
				switch ($_GET['version']) :
					case '0_2_0' :
						wp_enqueue_script('jquery-ui-progressbar');
						wp_enqueue_script('uci-results-migration-0_2_0-script', UCI_RESULTS_ADMIN_URL.'migration/v0-2-0/script.js', array('jquery-ui-progressbar'), '0.1.0', true);
						
						wp_enqueue_style('uci-results-jquery-ui-css', "http://ajax.googleapis.com/ajax/libs/jqueryui/$jquery_ui_version/themes/ui-lightness/jquery-ui.min.css");
						
						break;
				endswitch;
			endif;
		endif;
	}

	/**
	 * register_menu_page function.
	 * 
	 * @access public
	 * @return void
	 */
	public function register_menu_page() {
		$parent_slug='uci-results';
		$manage_options_cap='manage_options';
		
	    add_menu_page(__('UCI Results', 'uci-results'), 'UCI Results', $manage_options_cap, $parent_slug, array($this, 'admin_page'), 'dashicons-media-spreadsheet', 80);
	    add_submenu_page($parent_slug, 'Riders', 'Riders', $manage_options_cap, 'edit.php?post_type=riders');
	    add_submenu_page($parent_slug, 'Races', 'Races', $manage_options_cap, 'edit.php?post_type=races');
	    add_submenu_page($parent_slug, 'Countries', 'Countries', $manage_options_cap, 'edit-tags.php?taxonomy=country&post_type=races');
	    add_submenu_page($parent_slug, 'Class', 'Class', $manage_options_cap, 'edit-tags.php?taxonomy=race_class&post_type=races');
	    add_submenu_page($parent_slug, 'Series', 'Series', $manage_options_cap, 'edit-tags.php?taxonomy=series&post_type=races');
	    add_submenu_page($parent_slug, 'Season', 'Season', $manage_options_cap, 'edit-tags.php?taxonomy=season&post_type=races');
	    add_submenu_page($parent_slug, 'Settings', 'Settings', $manage_options_cap, $parent_slug);
	    add_submenu_page($parent_slug, 'Add Results', 'Add Results', $manage_options_cap, 'admin.php?page='.$parent_slug.'&subpage=results');
	    add_submenu_page($parent_slug, 'Rider Rankings', 'Rider Rankings', $manage_options_cap, 'admin.php?page='.$parent_slug.'&subpage=rider-rankings');
	    add_submenu_page($parent_slug, 'API', 'API', $manage_options_cap, 'admin.php?page='.$parent_slug.'&subpage=api');
	}
	
	/**
	 * admin_page function.
	 * 
	 * @access public
	 * @return void
	 */
	public function admin_page() {
		$html=null;	
		$subpage=isset($_GET['subpage']) ? $_GET['subpage'] : 'settings';	

		$html.='<div class="wrap uci-results">';
			$html.='<h1>UCI Results</h1>';

			switch ($subpage) :
				case 'rider-rankings' :
					$html.=uci_results_get_admin_page('rider-rankings');
					break;
				case 'settings':
					$html.=uci_results_get_admin_page('settings');
					break;
				case 'results':
					$html.=uci_results_get_admin_page('results');
					break;
				case 'api':
					$html.=uci_results_get_admin_page('api');
					break;
				case 'migration':
					if (isset($_GET['version'])) :					
						switch ($_GET['version']) :
							case '0_2_0' :
								$html.=uci_results_get_admin_page('migration-0_2_0');
								break;
						endswitch;
					else :
						$html.=uci_results_get_admin_page('settings');
					endif;
					break;
				default:
					$html.=uci_results_get_admin_page('settings');
			endswitch;

		$html.='</div><!-- /.wrap -->';
			
		echo $html;
	}

	/**
	 * setup_config function.
	 *
	 * @access public
	 * @param array $args (default: array())
	 * @return void
	 */
	public function setup_config($args=array()) {
		$default_config_urls=array(
			'2016/2017' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=-1&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
			'2015/2016' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=489&StartDateSort=20150830&EndDateSort=20160301&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
			'2014/2015' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=487&StartDateSort=20140830&EndDateSort=20150809&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
			'2013/2014' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=485&StartDateSort=20130907&EndDateSort=20140223&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
			'2012/2013' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=483&StartDateSort=20120908&EndDateSort=20130224&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
			'2011/2012' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=481&StartDateSort=20110910&EndDateSort=20120708&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
			'2010/2011' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=479&StartDateSort=20100911&EndDateSort=20110220&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
			'2009/2010' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=477&StartDateSort=20090913&EndDateSort=20100221&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
			'2008/2009' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=475&StartDateSort=20080914&EndDateSort=20090222&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
		);

		if (isset($args['urls'])) :
			$config['urls']=array_merge($default_config_urls,$args['urls']);
		else :
			$config['urls']=$default_config_urls;
		endif;

		// order urls by key //
		krsort($config['urls']);

		$this->config=json_decode(json_encode($config), FALSE); // convert to object and store
	}

	/**
	 * save_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function save_settings() {
		if (!isset($_POST['save_settings']) || $_POST['save_settings']!=1)
			return false;

		if (isset($_POST['single_rider_page_id'])) :
			update_option('single_rider_page_id', $_POST['single_rider_page_id']);
		else :
			delete_option('single_rider_page_id');
		endif;

		if (isset($_POST['single_race_page_id'])) :
			update_option('single_race_page_id', $_POST['single_race_page_id']);
		else :
			delete_option('single_race_page_id');
		endif;

		if (isset($_POST['country_page_id'])) :
			update_option('country_page_id', $_POST['country_page_id']);
		else :
			delete_option('country_page_id');
		endif;

		if (isset($_POST['rider_rankings_page_id'])) :
			update_option('rider_rankings_page_id', $_POST['rider_rankings_page_id']);
		else :
			delete_option('rider_rankings_page_id');
		endif;

		if (isset($_POST['races_page_id'])) :
			update_option('races_page_id', $_POST['races_page_id']);
		else :
			delete_option('races_page_id');
		endif;

		if (isset($_POST['uci_results_search_page_id'])) :
			update_option('uci_results_search_page_id', $_POST['uci_results_search_page_id']);
		else :
			delete_option('uci_results_search_page_id');
		endif;

		if (isset($_POST['current_season']) && $_POST['current_season']!='') :
			update_option('uci_results_current_season', $_POST['current_season']);
		else :
			delete_option('uci_results_current_season');
		endif;

		if (isset($_POST['twitter_consumer_key']) && $_POST['twitter_consumer_key']!='') :
			update_option('uci_results_twitter_consumer_key', $_POST['twitter_consumer_key']);
		else :
			delete_option('uci_results_twitter_consumer_key');
		endif;

		if (isset($_POST['twitter_consumer_secret']) && $_POST['twitter_consumer_secret']!='') :
			update_option('uci_results_twitter_consumer_secret', $_POST['twitter_consumer_secret']);
		else :
			delete_option('uci_results_twitter_consumer_secret');
		endif;

		if (isset($_POST['twitter_access_token']) && $_POST['twitter_access_token']!='') :
			update_option('uci_results_twitter_access_token', $_POST['twitter_access_token']);
		else :
			delete_option('uci_results_twitter_access_token');
		endif;

		if (isset($_POST['twitter_access_token_secret']) && $_POST['twitter_access_token_secret']!='') :
			update_option('uci_results_twitter_access_token_secret', $_POST['twitter_access_token_secret']);
		else :
			delete_option('uci_results_twitter_access_token_secret');
		endif;

		if (isset($_POST['post_results_to_twitter']) && $_POST['post_results_to_twitter']!='') :
			update_option('uci_results_post_results_to_twitter', $_POST['post_results_to_twitter']);
		else :
			delete_option('uci_results_post_results_to_twitter');
		endif;

		if (isset($_POST['post_rankings_to_twitter']) && $_POST['post_rankings_to_twitter']!='') :
			update_option('uci_results_post_rankings_to_twitter', $_POST['post_rankings_to_twitter']);
		else :
			delete_option('uci_results_post_rankings_to_twitter');
		endif;

		echo '<div class="updated">Settings updated!</div>';

		//flush_rewrite_rules(); // this may not be the best place for it - doesnt seem to work
	}

	/**
	 * ajax_empty_db function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_empty_db() {
		if (!check_ajax_referer('uci-results-empty-db-nonce', 'security', false))
			return;

		uci_results_empty_database_tables();

		echo '<div class="updated">Database tables are empty.</div>';

		wp_die();
	}

	/**
	 * ajax_remove_db function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_remove_db() {
		if (!check_ajax_referer('uci-results-remove-db-nonce', 'security', false))
			return;

		uci_results_remove_database_tables();

		echo '<div class="updated">Database tables removed.</div>';

		wp_die();
	}

	/**
	 * ajax_rider_rankings_dropdown function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_rider_rankings_dropdown() {
		global $ucicurl_races;

		$html=null;

		$html.='<option value="0">-- Select Week --</option>';

		foreach ($ucicurl_races->weeks($_POST['season']) as $week) :
			$html.='<option value="'.$week.'">'.$week.'</option>';
		endforeach;

		echo $html;

		wp_die();
	}

	public function include_migration_files() {
		include_once(UCI_RESULTS_ADMIN_PATH.'/migration/v0-2-0/ajax.php');	
		
		if (isset($_GET['subpage']) && $_GET['subpage']=='migration') :
			if (isset($_GET['version'])) :					
				switch ($_GET['version']) :
					case '0_2_0' :
						include_once(UCI_RESULTS_ADMIN_PATH.'/migration/v0-2-0/ajax.php');	
						break;
				endswitch;
			endif;
		endif;
	}
}

$uci_results_admin = new UCIResultsAdmin();
?>