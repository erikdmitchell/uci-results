<?php
global $uci_results_admin;

class UCIResultsAdmin {

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action('admin_menu', array($this, 'register_menu_page'));
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts_styles'));
		add_action('admin_init', array($this, 'save_settings'));
		add_action('admin_init', array($this, 'include_migration_files'));
		add_action('save_post', array($this, 'assign_parent_terms'), 10, 2);
		
		add_action('wp_ajax_uci_results_remove_data', array($this, 'ajax_remove_data'));
		add_action('wp_ajax_uci_results_rider_rankings_dropdown', array($this, 'ajax_rider_rankings_dropdown'));
		add_action('wp_ajax_uci_remove_related_race', array($this, 'ajax_remove_related_race'));
		add_action('wp_ajax_show_related_races_box', array($this, 'ajax_show_related_races_box'));
		add_action('wp_ajax_search_related_races', array($this, 'ajax_search_related_races'));
		add_action('wp_ajax_add_related_races_to_race', array($this, 'ajax_add_related_races_to_race'));
		add_action('wp_ajax_race_id_search', array($this, 'ajax_race_id_search'));

		$this->setup_config();		
	}

	/**
	 * admin_scripts_styles function.
	 * 
	 * @access public
	 * @return void
	 */
	public function admin_scripts_styles($hook) {
		global $wp_scripts;
		
		$jquery_ui_version=$wp_scripts->registered['jquery-ui-core']->ver;

		wp_enqueue_script('uci-results-admin', UCI_RESULTS_ADMIN_URL.'/js/admin.js', array('jquery'), '0.1.0', true);

		wp_enqueue_style('uci-results-api-admin-styles', UCI_RESULTS_ADMIN_URL.'css/admin.css', '0.1.0');	
		
		if ($hook=='toplevel_page_uci-results' && isset($_GET['subpage']) && $_GET['subpage']=='migration') :
			if (isset($_GET['version'])) :					
				switch ($_GET['version']) :
					case '1_0_0' :
						wp_enqueue_script('jquery-ui-progressbar');
						wp_enqueue_script('uci-results-migration-0_2_0-script', UCI_RESULTS_ADMIN_URL.'migration/v1-0-0/script.js', array('jquery-ui-progressbar'), '0.1.0', true);
						
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
	    add_submenu_page($parent_slug, 'UCI Rankings', 'UCI Rankings', $manage_options_cap, 'admin.php?page='.$parent_slug.'&subpage=uci-rankings');
	    add_submenu_page($parent_slug, 'API', 'API', $manage_options_cap, 'admin.php?page='.$parent_slug.'&subpage=api');
	    add_submenu_page($parent_slug, 'WP CLI', 'WP CLI', $manage_options_cap, 'admin.php?page='.$parent_slug.'&subpage=wpcli');	        
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
					$html.=$this->get_admin_page('rider-rankings');
					break;
				case 'settings':
					$html.=$this->get_admin_page('settings');
					break;
				case 'results':
					if (isset($_GET['action']) && $_GET['action']=='add-csv') :
						$html.=$this->get_admin_page('results-csv');
					else :
						$html.=$this->get_admin_page('results');
					endif;
					break;
				case 'api':
					$html.=$this->get_admin_page('api');
					break;
				case 'migration':
					if (isset($_GET['version'])) :					
						switch ($_GET['version']) :
							case '1_0_0' :
								$html.=$this->get_admin_page('migration-1_0_0');
								break;
						endswitch;
					else :
						$html.=$this->get_admin_page('settings');
					endif;
					break;
				case 'uci-rankings':
					$html.=$this->get_admin_page('uci-rankings');
					break;	
				case 'wpcli':
					$html.=$this->get_admin_page('wpcli');
					break;	
				default:
					$html.=$this->get_admin_page('settings');
			endswitch;

		$html.='</div><!-- /.wrap -->';
			
		echo $html;
	}
	
	/**
	 * get_admin_page function.
	 * 
	 * @access public
	 * @param bool $template_name (default: false)
	 * @return void
	 */
	public function get_admin_page($template_name=false) {
		$html=null;
		
		if (!$template_name)
			return false;
	
		ob_start();
	
		if (file_exists(UCI_RESULTS_PATH."adminpages/$template_name.php"))
			include_once(UCI_RESULTS_PATH."adminpages/$template_name.php");
	
		$html=ob_get_contents();
	
		ob_end_clean();
	
		return $html;
	}

	/**
	 * setup_config function.
	 * 
	 * @access public
	 * @return void
	 */
	public function setup_config() {
		$config['urls']=array(
			'cross' => array(
				'20162017' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=-1&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
				'20152016' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=489&StartDateSort=20150830&EndDateSort=20160301&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
				'20142015' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=487&StartDateSort=20140830&EndDateSort=20150809&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
				'20132014' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=485&StartDateSort=20130907&EndDateSort=20140223&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
				'20122013' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=483&StartDateSort=20120908&EndDateSort=20130224&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
				'20112012' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=481&StartDateSort=20110910&EndDateSort=20120708&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
				'20102011' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=479&StartDateSort=20100911&EndDateSort=20110220&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
				'20092010' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=477&StartDateSort=20090913&EndDateSort=20100221&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
				'20082009' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=475&StartDateSort=20080914&EndDateSort=20090222&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',
			),
			'road' => array(
				'2017' => 'http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=102&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=492&StartDateSort=20161022&EndDateSort=20171024&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8',	
			),
		);

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

		if (isset($_POST['riders_page_id'])) :
			update_option('riders_page_id', $_POST['riders_page_id']);
		else :
			delete_option('riders_page_id');
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
		uci_results_init(); // updated pages
	}

	/**
	 * ajax_remove_data function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_remove_data() {
		global $wpdb;
		
		if (!check_ajax_referer('uci-results-remove-data-nonce', 'security', false))
			return;

		$post_types=array(
			'riders',
			'races',
		);
		$taxonoimes=array(
			'series',
			'country',
			'race_class',
			'season',	
		);

		// remove post types //
		foreach ($post_types as $post_type) :
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = '$post_type'");
		endforeach;

		// remove taxonomies //
		foreach ($taxonoimes as $taxonomy) :
			$terms = get_terms( array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
			) );
			
			foreach ($terms as $term) :
				wp_delete_term($term->term_id, $taxonomy);
			endforeach;
		endforeach;
		
		// remove db tables //
		$wpdb->query("DROP TABLE $wpdb->uci_results_rider_rankings");
		$wpdb->query("DROP TABLE $wpdb->uci_results_related_races");
		$wpdb->query("DROP TABLE $wpdb->uci_results_series_overall");
	
		echo '<div class="updated">Data removed.</div>';

		wp_die();
	}

	/**
	 * ajax_rider_rankings_dropdown function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_rider_rankings_dropdown() {
		echo uci_get_season_weeks($_POST['season']);

		wp_die();
	}

	/**
	 * include_migration_files function.
	 * 
	 * @access public
	 * @return void
	 */
	public function include_migration_files() {
		include_once(UCI_RESULTS_ADMIN_PATH.'/migration/v1-0-0/ajax.php');	
		
		if (isset($_GET['subpage']) && $_GET['subpage']=='migration') :
			if (isset($_GET['version'])) :					
				switch ($_GET['version']) :
					case '1_0_0' :
						include_once(UCI_RESULTS_ADMIN_PATH.'/migration/v1-0-0/ajax.php');	
						break;
				endswitch;
			endif;
		endif;
	}
	
	/**
	 * ajax_remove_related_race function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_remove_related_race() {
	    global $wpdb;
	    
	    $wpdb->delete($wpdb->uci_results_related_races, array('race_id' => $_POST['id'], 'related_race_id' => $_POST['rrid']));

	    echo true;
	    
	    wp_die();
    }
    
    /**
     * ajax_show_related_races_box function.
     * 
     * @access public
     * @return void
     */
    public function ajax_show_related_races_box() {
		echo $this->get_admin_page('add-related-races');

		wp_die();	    
    }
    
	/**
	 * ajax_search_related_races function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_search_related_races() {
		global $wpdb;

		$html=null;
		$query=$_POST['query'];
		$races=$wpdb->get_results("
			SELECT * 
			FROM $wpdb->posts 
			WHERE post_title LIKE '%$query%' AND post_type = 'races'
		");
		$related_races_ids=uci_get_related_races_ids($_POST['id']);

		// build out html //
		foreach ($races as $race) :
			if ($race->ID==$_POST['id'] || in_array($race->ID, $related_races_ids))
				continue; // skip if current race or already linked
				
			$country=array_pop(wp_get_post_terms($race->ID, 'country'));
			$class=array_pop(wp_get_post_terms($race->ID, 'race_class'));			
			$season=array_pop(wp_get_post_terms($race->ID, 'season'));

			$html.='<tr>';
				$html.='<th scope="row" class="check-column"><input id="cb-select-'.$race->ID.'" type="checkbox" name="races[]" value="'.$race->ID.'"></th>';
				$html.='<td class="race-date">'.date(get_option('date_format'), strtotime(get_post_meta($race->ID, '_race_date', true))).'</td>';
				$html.='<td class="race-name">'.$race->post_title.'</td>';
				$html.='<td class="race-nat">'.$country->name.'</td>';
				$html.='<td class="race-class">'.$class->name.'</td>';
				$html.='<td class="race-season">'.$season->name.'</td>';
			$html.='</tr>';
		endforeach;

		echo $html;

		wp_die();
	} 
	
	/**
	 * ajax_add_related_races_to_race function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_add_related_races_to_race() {
		global $wpdb;
		
		parse_str($_POST['form'], $form);
		
		$html=null;
		$races=$form['races'];
		$related_race_id=uci_get_related_race_id($_POST['id']);
		$last_related_race_id=$wpdb->get_var("SELECT MAX(related_race_id) FROM $wpdb->uci_results_related_races");
		
		// if no rr id - increase last by 1 //
		if (!$related_race_id) :
			$related_race_id=$last_related_race_id+1;
			update_post_meta($_POST['id'], '_race_related', $related_race_id);
		endif;

		foreach ($races as $race_id) :
			$data=array(
				'race_id' => $race_id,
				'related_race_id' => $related_race_id
			);
			$wpdb->insert($wpdb->uci_results_related_races, $data);		
		endforeach;
		
		// get races information //
		foreach ($races as $race_id) :
			$html.='<div id="race-'.$race_id.'" class="row">';
				$html.='<div class="race-name">'.get_the_title($race_id).'</div>';
				$html.='<div class="race-date">'.date(get_option('date_format'), strtotime(get_post_meta($race_id, '_race_date', true))).'</div>';
				$html.='<div class="action-icons"><a href="#" class="remove-related-race" data-id="'.$race_id.'" data-rrid="'.$related_race_id.'"><span class="dashicons dashicons-dismiss"></span></a></div>';
			$html.='</div>';
		endforeach;
		
		$return=array(
			'related_race_id' => $related_race_id,
			'html' => $html	
		);
		
		echo json_encode($return);
		
		wp_die();
	} 
	
	/**
	 * assign_parent_terms function.
	 * 
	 * @access public
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public function assign_parent_terms($post_id, $post) {
		if ($post->post_type != 'races')
        	return $post_id;
        	
        // terms with parents //
        $terms_with_parents=array('race_class', 'series', 'season');

	    // get all assigned terms in race_class and update parent // 
		foreach ($terms_with_parents as $term_cat) :
		    $terms=wp_get_post_terms($post_id, $term_cat);
		    
		    foreach($terms as $term) :
		        while ($term->parent != 0 && !has_term($term->parent, $term_cat, $post )) :
		            // move upward until we get to 0 level terms
		            wp_set_post_terms($post_id, array($term->parent), $term_cat, true);
		            $term = get_term($term->parent, $term_cat);
		        endwhile;
		    endforeach;
	    endforeach;
	    
	    
	}
	
	/**
	 * ajax_race_id_search function.
	 * 
	 * @access public
	 * @return void
	 */
	public function ajax_race_id_search() {
		$html='';
		$posts=get_posts(array(
			'posts_per_page' => -1,
			'post_type' => 'races',
			's' => $_POST['string']
		));
		
		$html.='<select multiple id="races-list" name="race_search_id" size=20 style="height: 100%;">';
			foreach ($posts as $post) :
				$html.='<option value="'.$post->ID.'">'.$post->post_title.' ('.get_post_meta($post->ID, '_race_date', true).')</option>';
			endforeach;
		$html.='</select>';
		
		echo $html;
		
		wp_die();
	}
	
	/**
	 * race_urls_dropdown function.
	 * 
	 * @access public
	 * @param string $name (default: 'season')
	 * @param string $selected (default: '')
	 * @return void
	 */
	public function race_urls_dropdown($name='season', $selected='') {
		$html='';
		
		$html.='<select class="url-dd" id="'.$name.'" name="'.$name.'">';
			$html.='<option value="0">Select Year</option>';
			
			foreach ($this->config->urls as $discipline => $discipline_urls) :
				$html.='<option>'.$discipline.'</option>';
				
				foreach ($discipline_urls as $season => $url) :
					$html.='<option value="'.$season.'" data-url="'.$url.'" data-season="'.$season.'" data-discipline="'.$discipline.'">&nbsp;&nbsp;&nbsp;'.$season.'</option>';
				endforeach;
				
			endforeach;
		$html.='</select>';
						
		echo $html;
	}
	
}

$uci_results_admin = new UCIResultsAdmin();
?>