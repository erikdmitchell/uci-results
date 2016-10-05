<?php
global $uci_results_admin_pages;

/**
 * UCIResultsAdminPages class.
 */
class UCIResultsAdminPages {

	public $config=array();

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($config=array()) {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts_styles'));
		add_action('admin_menu', array($this, 'admin_page'));
		add_action('admin_init', array($this, 'save_settings'));
		add_action('wp_ajax_uci_results_empty_db', array($this, 'ajax_empty_db'));
		add_action('wp_ajax_uci_results_remove_db', array($this, 'ajax_remove_db'));
		add_action('wp_ajax_uci_results_build_season_weeks', array($this, 'ajax_uci_results_build_season_weeks'));

		$this->setup_config($config);
	}

	/**
	 * admin_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_page() {
		add_menu_page('UCI Results', 'UCI Results', 'manage_options', 'uci-results', array($this, 'display_admin_page'), 'dashicons-sos');
	}

	/**
	 * admin_scripts_styles function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_scripts_styles() {
		wp_enqueue_script('uci-results-admin',UCI_RESULTS_URL.'/js/admin.js',array('jquery'), '0.1.0',true);

		wp_enqueue_style('uci-results-admin',UCI_RESULTS_URL.'/css/admin.css',array(), '0.1.0');
	}

	/**
	 * display_admin_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function display_admin_page() {
		global $ucicurl_riders;

		$html=null;
		$tabs=array(
			'settings' => 'Settings',
			'results' => 'Results',
			'races' => 'Races',
			'series' => 'Series',
			'riders' => 'Riders',
			'rider-rankings' => 'Rider Rankings',
			'api' => 'API'
		);
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';

		$html.='<div class="wrap uci-results">';
			$html.='<h1>UCI Results</h1>';

			$html.='<h2 class="nav-tab-wrapper">';
				foreach ($tabs as $key => $name) :
					if ($active_tab==$key) :
						$class='nav-tab-active';
					else :
						$class=null;
					endif;

					$html.='<a href="?page=uci-results&tab='.$key.'" class="nav-tab '.$class.'">'.$name.'</a>';
				endforeach;
			$html.='</h2>';

			switch ($active_tab) :
				case 'races':
					if (isset($_GET['race_id']) && isset($_GET['add_related_race'])) :
						$html.=ucicurl_get_admin_page('add-related-race');
					elseif (isset($_GET['race_id'])) :
						$html.=ucicurl_get_admin_page('single-race');
					else :
						$html.=ucicurl_get_admin_page('races');
					endif;
					break;
				case 'series' :
						if (isset($_GET['action']) && $_GET['action']=='update-series') :
							$html.=ucicurl_get_admin_page('update-series');
						else :
							$html.=ucicurl_get_admin_page('series');
						endif;
					break;
				case 'riders':
					if (isset($_GET['rider']) && $_GET['rider']!='') :
						$atts['rider_id']=$ucicurl_riders->get_rider_id($_GET['rider']);

						$html.=ucicurl_get_admin_page('single-rider', $atts);
					else :
						$html.=ucicurl_get_admin_page('riders');
					endif;
					break;
				case 'rider-rankings' :
						$html.=ucicurl_get_admin_page('rider-rankings');
						break;
				case 'settings':
					$html.=ucicurl_get_admin_page('settings');
					break;
				case 'results':
					$html.=ucicurl_get_admin_page('results');
					break;
				case 'api':
					$html.=ucicurl_get_admin_page('api');
					break;
				default:
						$html.=ucicurl_get_admin_page('settings');
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
	 * ajax_uci_results_build_season_weeks function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_uci_results_build_season_weeks() {
		//ini_set("memory_limit", "-1");
		//set_time_limit(0);

		//uci_results_build_season_weeks();

		echo '<div class="updated">Season weeks updated. THIS DOES NOT WORK</div>';

		wp_die();
	}

}

$uci_results_admin_pages=new UCIResultsAdminPages();
?>