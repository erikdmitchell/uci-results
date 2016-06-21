<?php

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

		$this->setup_config($config);
	}

	/**
	 * admin_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_page() {
		add_menu_page('UCI Results', 'UCI Results', 'manage_options', 'uci-curl', array($this, 'display_admin_page'), 'dashicons-sos');
	}

	/**
	 * admin_scripts_styles function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_scripts_styles() {
		wp_enqueue_script('uci-curl-admin',UCICURL_URL.'/js/admin.js',array('jquery'), '0.1.0',true);

		wp_enqueue_style('uci-curl-admin',UCICURL_URL.'/css/admin.css',array(), '0.1.0');
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
			'uci-curl' => 'Results',
			'races' => 'Races',
			'riders' => 'Riders',
			'rider-rankings' => 'Rider Rankings',
			'settings' => 'Settings'
		);
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'uci-curl';

		$html.='<div class="wrap">';
			$html.='<h1>UCI Results</h1>';

			$html.='<h2 class="nav-tab-wrapper">';
				foreach ($tabs as $key => $name) :
					if ($active_tab==$key) :
						$class='nav-tab-active';
					else :
						$class=null;
					endif;

					$html.='<a href="?page=uci-curl&tab='.$key.'" class="nav-tab '.$class.'">'.$name.'</a>';
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
				default:
					if (isset($_GET['action']) && $_GET['action']=='update-rankings') :
						$html.=ucicurl_get_admin_page('update-rankings');
					else :
						$html.=ucicurl_get_admin_page('curl');
					endif;

					break;
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

		flush_rewrite_rules(); // this may not be the best place for it
	}

}

$uci_results_admin_pages=new UCIResultsAdminPages();
?>