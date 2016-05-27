<?php
/**
 * UCIcURLAdmin class.
 *
 * @since Version 2.0.0
 */
class UCIcURLAdmin {
	public $config=array();

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param array $config (default: array())
	 * @return void
	 */
	public function __construct($config=array()) {
		global $wpdb;

		add_action('admin_menu',array($this,'admin_page'));
		add_action('admin_enqueue_scripts',array($this,'admin_scripts_styles'));

		add_action('wp_ajax_get_race_data_non_db',array($this,'ajax_get_race_data_non_db'));
		add_action('wp_ajax_prepare_add_races_to_db',array($this,'ajax_prepare_add_races_to_db'));
		add_action('wp_ajax_add_race_to_db', array($this, 'ajax_add_race_to_db'));
		add_action('wp_ajax_update_rider_rankings', array($this, 'ajax_update_rider_rankings'));

		$this->setup_config($config);
	}

	/**
	 * admin_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_page() {
		add_menu_page('UCI cURL', 'UCI cURL', 'manage_options', 'uci-curl', array($this, 'display_admin_page'), 'dashicons-sos');
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
			'uci-curl' => 'UCI cURL',
			'races' => 'Races',
			'riders' => 'Riders',
			'rider-rankings' => 'Rider Rankings'
		);
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'uci-curl';

		$html.='<div class="wrap">';
			$html.='<h1>UCI cURL</h1>';

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
				default:
					$html.=ucicurl_get_admin_page('curl');
					break;
			endswitch;

		$html.='</div><!-- /.wrap -->';

		echo $html;
	}

	/**
	 * ajax_get_race_data_non_db function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_get_race_data_non_db() {
		if (empty($_POST['url']))
			return false;

		$this->config->url=$_POST['url']; // set url
		$limit=false;

		// set limit if passed //
		if (!empty($_POST['limit']))
			$limit=$_POST['limit'];

		echo $this->get_race_data($limit);

		wp_die();
	}

	/**
	 * get_race_data function.
	 *
	 * this function does all the magic as far as parseing results and adding them to the db
	 *
	 * @access public
	 * @param bool $limit (default: false)
	 * @return void
	 */
	public function get_race_data($limit=false) {
		if (!isset($this->config->url))
			return false;

		set_time_limit(0); // mex ececution time
		$races=array();
		$races_class_name='datatable';
		$races_obj=new stdClass();
		$arr=array();
		$timeout = 5;
		$row_count=0;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

		$html=curl_exec($ch);

		curl_close($ch);

		// Create a DOM parser object
		$dom = new DOMDocument();

		// Parse HTML - The @ before the method call suppresses any warnings that loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);

		//discard white space
		@$dom->preserveWhiteSpace = false;

		$finder = new DomXPath($dom);

		$nodes = $finder->query("//*[contains(@class, '$races_class_name')]");

		if ($nodes->length==0)
			return '<div class="error">No rows (nodes) found. Check the url and the "races class name" ('.$races_class_name.').</div>';

		$rows=$nodes->item(0)->getElementsByTagName('tr'); //get all rows from the table

		// loop over the table rows
		foreach ($rows as $row) :
		  if ($row_count!=0) :
		  	$races[$row_count]=new stdClass();

		  	// get links //
		  	$links = $row->getElementsByTagName('a');
				foreach ($links as $tag) :
					$link=$this->alter_race_link($tag->getAttribute('href'));
				endforeach;

		  	$cols = $row->getElementsByTagName('td'); 	// get each column by tag name
			  foreach ($cols as $key => $col) :
					if ($key==0) {
						$races[$row_count]->date=$this->reformat_date($col->nodeValue);
					} else if ($key==1) {
						$races[$row_count]->event=$col->nodeValue;
					} else if ($key==2) {
						$races[$row_count]->nat=$col->nodeValue;
					} else if ($key==3) {
						$races[$row_count]->class=$col->nodeValue;
					} else if ($key==4) {
						$races[$row_count]->winner=$col->nodeValue;
					}
				endforeach;

				$races[$row_count]->link=$link;
				$races[$row_count]->season=$this->get_season_from_date($races[$row_count]->date);

				// check for code in db, only get results if not in db //
				$code=$this->build_race_code($races[$row_count]);
			endif;

			$row_count++;

			if ($limit && $row_count>$limit)
				break;

		endforeach;

		// setup as object //
		foreach ($races as $key => $value) :
			$races_obj->$key=$value;
		endforeach;

		return $this->build_default_race_table($races_obj);
	}

	/**
	 * ajax_prepare_add_races_to_db function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_prepare_add_races_to_db() {
		if (empty($_POST['races']))
			return false;

		$races=array();
		foreach ($_POST['races'] as $race) :
			$races[]=unserialize(base64_decode($race));
		endforeach;

		echo json_encode($races);

		wp_die();
	}

	/**
	 * ajax_add_race_to_db function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_add_race_to_db() {
		if (!$_POST['race'])
			return false;

		$code=$this->build_race_code($_POST['race']);

		// add to db //
		if (!$this->check_for_dups($code)) :
			echo $this->add_race_to_db($_POST['race']);
		else :
			echo '<div class="updated add-race-to-db-message">Already in db. ('.$code.')</div>';
		endif;

		wp_die();
	}

	/**
	 * alter_race_link function.
	 *
	 * @access protected
	 * @param mixed $link
	 * @return void
	 */
	protected function alter_race_link($link) {
		$final_url='http://www.uci.infostradasports.com/asp/lib/TheASP.asp?';
		parse_str($link,$arr);

		$final_url.='PageID=19006';
		$final_url.='&SportID='.$arr['SportID'];
		$final_url.='&CompetitionID='.$arr['CompetitionID'];
		$final_url.='&EditionID='.$arr['EditionID'];
		$final_url.='&SeasonID='.$arr['SeasonID'];
		$final_url.='&ClassID='.$arr['ClassID'];
		$final_url.='&GenderID='.$arr['GenderID'];
		$final_url.='&EventID='.$arr['EventID'];
		$final_url.='&EventPhaseID='.$arr['EventPhaseID'];
		$final_url.='&Phase1ID='.$arr['Phase1ID'];
		$final_url.='&Phase2ID=0';
		$final_url.='&Phase3ID=0';
		$final_url.='&PhaseClassificationID=-1';
		$final_url.='&Detail='.$arr['Detail'];
		//$final_url.='&Ranking='.$arr['Ranking']; -- causes an error
		$final_url.='&All=0';
		$final_url.='&TaalCode=2';
		$final_url.='&StyleID=0';
		$final_url.='&Cache=8';
		$final_url.='&PageNr0=-1';

		return $final_url;
	}

	/**
	 * get_race_results function.
	 *
	 * @access public
	 * @param mixed $url
	 * @return void
	 */
	public function get_race_results($url) {
		// Use the Curl extension to query Google and get back a page of results
		$timeout = 5;
		$race_results=array();
		$race_results_obj=new stdClass();
		$results_class_name="datatable";

		// get header so that we can get the charset ? //
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		//$html = curl_exec_utf8($ch); // external function in this file //
		$html=curl_exec($ch);
		curl_close($ch);

		// modify html //
		$html=preg_replace('/<script\b[^>]*>(.*?)<\/script>/is',"",$html); // remove js

		// Create a DOM parser object
		$dom = new DOMDocument();

		// Parse HTML - The @ before the method call suppresses any warnings that loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);

		//discard white space
		$dom->preserveWhiteSpace = false;

		// get our results table //
		$finder = new DomXPath($dom);

		$nodes = $finder->query("//*[contains(@class, '$results_class_name')]");

		// if for some reason we can't find that class, we bail and return an empty object //
		if ($nodes->length==0)
			return new stdClass();

		$rows=$nodes->item(0)->getElementsByTagName('tr'); //get all rows from the table -- this seems to cause an ERROR sometimes

		// loop over the table rows
		$row_count=0;
		foreach ($rows as $row) :
		  if ($row_count!=0) :
		  	$race_results[$row_count]=new stdClass();
		  	$cols = $row->getElementsByTagName('td'); 	// get each column by tag name
			  foreach ($cols as $key => $col) :
					// rank, name, nat, age, result, par, pcr
					switch ($key) :
						case 0:
							$race_results[$row_count]->place=$col->nodeValue;
							break;
						case 1:
							$race_results[$row_count]->name=$col->nodeValue;
							break;
						case 2:
							$race_results[$row_count]->nat=$col->nodeValue;
							break;
						case 3:
							$race_results[$row_count]->age=$col->nodeValue;
							break;
						case 4:
							$race_results[$row_count]->result=$col->nodeValue;
							break;
						case 5:
							$race_results[$row_count]->par=$col->nodeValue;
							break;
						case 6:
							$race_results[$row_count]->pcr=$col->nodeValue;
							break;
					endswitch;
				endforeach;
			endif;
			$row_count++;
		endforeach;

		foreach ($race_results as $key => $value) :
			$race_results_obj->$key=$value;
		endforeach;

		return $race_results_obj;
	}

	/**
	 * add_race_to_db function.
	 *
	 * @access public
	 * @param mixed $race_data
	 * @return void
	 */
	public function add_race_to_db($race_data) {
		global $wpdb;

		$message=null;

		// convert to object //
		if (!is_object($race_data))
			$race_data=json_decode(json_encode($race_data),FALSE);

		// build data array ..
		$data=array(
			'date' => $date = date('Y-m-d', strtotime($race_data->date)),
			'event' => $race_data->event,
			'nat' => $race_data->nat,
			'class' => $race_data->class,
			'winner' => $race_data->winner,
			'season' => $race_data->season,
			'link' => $race_data->link,
			'code' => $this->build_race_code($race_data->event, $race_data->date),
			'week' => $this->get_race_week($race_data->date, $race_data->season),
		);

		if (!$this->check_for_dups($data['code'])) :
			if ($wpdb->insert($wpdb->ucicurl_races, $data)) :
				$message='<div class="updated">Added '.$data['code'].' to database.</div>';
				$this->add_race_results_to_db($wpdb->insert_id, $race_data->link);
			else :
				$message='<div class="error">Unable to insert '.$data['code'].' into the database.</div>';
			endif;
		else :
			$message='<div class="updated">'.$data['code'].' is already in the database</div>';
		endif;

		return $message;
	}

	/**
	 * add_race_results_to_db function.
	 *
	 * @access public
	 * @param bool $race_id (default: 0)
	 * @param bool $link (default: false)
	 * @return void
	 */
	public function add_race_results_to_db($race_id=0, $link=false) {
		global $wpdb;

		if (!$race_id || !$link)
			return false;

		$race_results=$this->get_race_results($link);
		$race=$wpdb->get_row("SELECT * FROM {$wpdb->ucicurl_races} WHERE id={$race_id}");

		foreach ($race_results as $result) :
			$rider_id=$wpdb->get_var("SELECT id FROM {$wpdb->ucicurl_riders} WHERE name=\"{$result->name}\" AND nat='{$result->nat}'");

			// check if we have a rider id, otherwise create one //
			if (!$rider_id) :
				$rider_insert=array(
					'name' => $result->name,
					'nat' => $result->nat,
				);
				$wpdb->insert($wpdb->ucicurl_riders, $rider_insert);
				$rider_id=$wpdb->insert_id;
			endif;

			$insert=array(
				'race_id' => $race_id,
				'place' => $result->place,
				'name' => $result->name,
				'nat' => $result->nat,
				'age' => $result->age,
				'result' => $result->result,
				'par' => $result->par,
				'pcr' => $result->pcr,
				'rider_id' => $rider_id,
			);

			$wpdb->insert($wpdb->ucicurl_results, $insert);

		endforeach;
	}

	/**
	 * ajax_update_rider_rankings function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_update_rider_rankings() {
		if (!isset($_POST['season']) || $_POST['season']=='')
			return false;

		global $wpdb, $ucicurl_races;

		$season=$_POST['season']; // set season
		$wpdb->delete($wpdb->ucicurl_rider_rankings, array('season' => $season)); // remove ranks from season to prevent dups
		$rider_ids=$wpdb->get_col("SELECT id FROM {$wpdb->ucicurl_riders}"); // get all rider ids

		// loop through riders and get results //
		foreach ($rider_ids as $rider_id) :
			$sql="
				SELECT
					results.race_id,
					results.par AS points,
					results.rider_id,
					races.week
				FROM wp_uci_curl_results AS results
				LEFT JOIN wp_uci_curl_races AS races
				ON results.race_id = races.id
				WHERE races.season='{$season}'
				AND rider_id={$rider_id}
				ORDER BY week ASC
			";
			$rider_results=$wpdb->get_results($sql);

			// go through rider results and update rider rankings //
			foreach ($rider_results as $result) :
				$this->update_rider_rankings($rider_id, $result->points, $season, $result->week);
			endforeach;
		endforeach;

		// then update rider rankings rank //
		$weeks=$ucicurl_races->weeks($season);

		foreach ($weeks as $week) :
			$this->update_rider_rankings_rank($season, $week);
		endforeach;

		wp_die();
	}

	/**
	 * update_rider_rankings function.
	 *
	 * @access public
	 * @param int $rider_id (default: 0)
	 * @param int $points (default: 0)
	 * @param string $season (default: '')
	 * @param int $week (default: 0)
	 * @return void
	 */
	public function update_rider_rankings($rider_id=0, $points=0, $season='', $week=0) {
		global $wpdb;

		$prev_points=0;
		$db_points=0;
		$prev_week=absint($week)-1;
		$ranking_id=$wpdb->get_var("SELECT id FROM {$wpdb->ucicurl_rider_rankings} WHERE rider_id={$rider_id} AND week={$week}");

		if ($ranking_id) :
			$db_points=$wpdb->get_var("SELECT points FROM {$wpdb->ucicurl_rider_rankings} WHERE id={$ranking_id}");
			$points=$db_points + $points;
			$data=array(
				'points' => $points
			);
			$where=array(
				'id' => $ranking_id
			);

			$wpdb->update($wpdb->ucicurl_rider_rankings, $data, $where);
		else :
			$prev_points=$wpdb->get_var("SELECT SUM(points) FROM {$wpdb->ucicurl_rider_rankings} WHERE rider_id={$rider_id} AND week={$prev_week}");
			$points=$points + $prev_points;
			$data=array(
				'rider_id' => $rider_id,
				'points' => $points,
				'season' => $season,
				'week' => $week,
			);

			$wpdb->insert($wpdb->ucicurl_rider_rankings, $data);
			$ranking_id=$wpdb->insert_id; // not utalized, except for log
		endif;

		$log=array(
			'ranking_id' => $ranking_id,
			'rider_id' => $rider_id,
			'points' => $points,
			'week' => $week,
		);

	}

	/**
	 * update_rider_rankings_rank function.
	 *
	 * @access public
	 * @param string $season (default: '')
	 * @param int $week (default: 0)
	 * @return void
	 */
	public function update_rider_rankings_rank($season='', $week=0) {
		global $wpdb;

		$this->add_previous_weeks_riders_to_rank($season, $week);

		$sql="
			SELECT id
			FROM {$wpdb->ucicurl_rider_rankings}
			WHERE season='{$season}'
				AND week={$week}
			ORDER BY points DESC
		";
		$ids=$wpdb->get_col($sql);
		$rank=1;

		// we now just update our rank via id //
		foreach ($ids as $id) :
			$data=array(
				'rank' => $rank
			);
			$where=array(
				'id' => $id
			);
			$wpdb->update($wpdb->ucicurl_rider_rankings, $data, $where);
			$rank++;
		endforeach;
	}

	/**
	 * add_previous_weeks_riders_to_rank function.
	 *
	 * @access public
	 * @param string $season (default: '')
	 * @param int $week (default: 0)
	 * @return void
	 */
	public function add_previous_weeks_riders_to_rank($season='', $week=0) {
		global $wpdb;

		// no prev week, we out //
		if ($week<=1)
			return;

		$prev_week=$week-1;
		$prev_week_ranking_ids=$wpdb->get_col("SELECT rider_id FROM {$wpdb->ucicurl_rider_rankings}	WHERE season='{$season}' AND week={$prev_week}");
		$this_week_ranking_ids=$wpdb->get_col("SELECT rider_id FROM {$wpdb->ucicurl_rider_rankings}	WHERE season='{$season}' AND week={$week}");

		// if missing from this week, use last weeks points //
		foreach ($prev_week_ranking_ids as $rider_id) :
			if (!in_array($rider_id, $this_week_ranking_ids)) :
				$prev_points=$wpdb->get_var("SELECT points FROM {$wpdb->ucicurl_rider_rankings}	WHERE rider_id='{$rider_id}' AND week={$prev_week}");

				$insert_data=array(
					'rider_id' => $rider_id,
					'points' => $prev_points,
					'season' => $season,
					'week' => $week,
				);
				$wpdb->insert($wpdb->ucicurl_rider_rankings, $insert_data);
			endif;
		endforeach;
	}

	/**
	 * build_race_code function.
	 *
	 * takes the race name and date to build a string which becomes our "code" to prevent dups
	 *
	 * @access public
	 * @param bool $name (default: false)
	 * @param bool $date (default: false)
	 * @return void
	 */
	public function build_race_code($name=false, $date=false) {
		if (!$date || !$name)
			return false;

		$code=$name.$date; // combine name and date
		$code=sanitize_title_with_dashes($code); // https://codex.wordpress.org/Function_Reference/sanitize_title_with_dashes

		return $code;
	}

	/**
	 * get_season_from_date function.
	 *
	 * @access protected
	 * @param mixed $date
	 * @return void
	 */
	protected function get_season_from_date($date) {
		$season_arr=$this->build_year_arr();

		foreach ($season_arr as $key => $season) :
			if (strtotime($date) >= strtotime($season['start'])  && strtotime($date) <= strtotime($season['end']))
				return $key;
		endforeach;

		return false;
	}

	/**
	 * build_year_arr function.
	 *
	 * @access public
	 * @return void
	 */
	public function build_year_arr() {
		$year=date('Y');
		$counter_year=$year-20;
		$max_year=$year+1;
		$season_start='Aug 30';
		$season_end='Mar 1';
		$season_arr=array();

		while ($counter_year<=$max_year) :
			$year=$counter_year;
			$next_year=$counter_year+1;
			$season=$year.'/'.$next_year;

			$season_arr[$season]=array(
				'start' => $season_start.' '.$year,
				'end' => $season_end.' '.$next_year
			);

			$counter_year++;
		endwhile;

		return $season_arr;
	}

	/**
	 * check_for_dups function.
	 *
	 * @access protected
	 * @param mixed $code
	 * @return void
	 */
	protected function check_for_dups($code) {
		global $wpdb;

		$races_in_db=$wpdb->get_results("SELECT code FROM $wpdb->ucicurl_races");

		if (count($races_in_db)!=0) :
			foreach ($races_in_db as $race) :
				if ($race->code==$code)
					return true;
			endforeach;
		endif;

		return false;
	}

	/**
	 * has_results function.
	 *
	 * @access public
	 * @param mixed $code
	 * @return void
	 */
	public function has_results($code) {
		global $wpdb;

		$race_id=$wpdb->get_var("SELECT id FROM {$wpdb->ucicurl_races} WHERE code=\"{$code}\"");

		// no race id, we out //
		if (!$race_id)
			return false;

		$results=$wpdb->get_results("SELECT id FROM {$wpdb->ucicurl_results} WHERE race_id={$race_id}");

		// do we have results? //
		if (count($results))
			return true;

		return false;
	}

	/**
 	 * @param string $url - results url
	 * the date comes in with hidden &nbsp; this gets a 'clean' date from the results page

	**/
	function get_race_date($url) {
		// Use the Curl extension to query Google and get back a page of results
		$timeout = 5;
		$race_results=array();
		$race_results_obj=new stdClass();
		$results_class_name="datatable";

		// get header so that we can get the charset ? //
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		//$html = curl_exec_utf8($ch); // external function in this file //
		$html=curl_exec($ch);
		curl_close($ch);

		// modify html //
		$html=preg_replace('/<script\b[^>]*>(.*?)<\/script>/is',"",$html); // remove js

		// Create a DOM parser object
		$dom = new DOMDocument();

		// Parse HTML - The @ before the method call suppresses any warnings that loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);

		//discard white space
		$dom->preserveWhiteSpace = false;

		// get our results table //
		$finder = new DomXPath($dom);

		$nodes = $finder->query("//*[contains(@class, 'subtitlered')]");
		$date=explode(':',$nodes->item(0)->nodeValue);

		return trim($date[0]);
	}

	/**
	 * reformat_date function.
	 *
	 * @access public
	 * @param mixed $date
	 * @return void
	 */
	public function reformat_date($date) {
		$date=utf8_encode($date);
		$date_arr=explode('Â',$date);

		foreach ($date_arr as $key => $value) :
			switch ($key) :
				case 0:
					$new_value=substr($value,0,-2);
					break;
				case 1:
					$new_value=null;
					$new_value=substr($value,1,-2);
					break;
				case 2:
					$new_value=substr($value,1);
					break;
			endswitch;
			$date_arr[$key]=$new_value;
		endforeach;

		if (count($date_arr)<3)
			return false;

		$date=$date_arr[0].' '.$date_arr[1].' '.$date_arr[2];

		return $date;
	}

	/**
	 * build_default_race_table function.
	 *
	 * @access public
	 * @param mixed $obj
	 * @return void
	 */
	public function build_default_race_table($obj) {
		$html=null;

		$html.='<form name="add-races-to-db" id="add-races-to-db" method="post">';
			$html.='<table class="wp-list-table widefat fixed striped pages">';
				$html.='<thead>';
					$html.='<tr>';
						$html.='<td id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="select-all"></td>';
						$html.='<th scope="col" class="race-date">Date</th>';
						$html.='<th scope="col" class="race-name">Event</th>';
						$html.='<th scope="col" class="race-nat">Nat.</th>';
						$html.='<th scope="col" class="race-class">Class</th>';
					$html.='</tr>';
				$html.='</thead>';
				$html.='<tbody id="the-list">';

					foreach ($obj as $result) :
						$disabled='';
						$date=$result->date;
						$code=$this->build_race_code($result->event, $result->date);

						// check we have event //
						if (!isset($result->event)) :
							$result->event=false;
						else :
							$event=$result->event;
						endif;

						// disable if no event (data error) //
						if (!$result->date || !$result->event)
							$disabled='disabled';

						if (!$result->date)
							$date='No Date';

						if (!$result->event)
							$event='No Event';

						// if we already have results, bail. there are other check later, but this is a good helper //
						if ($this->has_results($code))
							$disabled='disabled';

						$html.='<tr>';
							$html.='<th scope="row" class="check-column"><input type="checkbox" name="races[]" value="'.base64_encode(serialize($result)).'" '.$disabled.'></th>';
							$html.='<td class="race-date">'.$date.'</td>';
							$html.='<td class="race-name">'.$event.'</td>';

							if ($result->event) :
								$html.='<td class="race-nat">'.$result->nat.'</td>';
								$html.='<td class="race-class">'.$result->class.'</td>';
							endif;

						$html.='</tr>';
					endforeach;

				$html.='</tbody>';
			$html.='</table>';

			$html.='<p class="submit">';
				$html.='<input type="button" name="button" id="add-races-curl-to-db" class="button button-primary" value="Add to DB" />';
			$html.='</p>';
		$html.='</form>';

		return $html;
	}

	/**
	 * get_race_week function.
	 *
	 * @access public
	 * @param string $date (default: '')
	 * @param mixed $season (default: )
	 * @return void
	 */
	public function get_race_week($date='', $season='') {
		$date=date('Y-m-d', strtotime($date));
		$seasons=$this->build_year_arr();
		$season_data=$this->add_weeks_to_season($seasons[$season]);

		return $this->get_week_of_date($date, $season_data['weeks']);
	}

	/**
	 * get_week_of_date function.
	 *
	 * @access public
	 * @param string $date (default: '')
	 * @param array $weeks (default: array())
	 * @return void
	 */
	public function get_week_of_date($date='', $weeks=array()) {
		$week_counter=1;

		foreach ($weeks as $week) :
			$week_start=strtotime($week['start']);
			$week_end=strtotime($week['end']);
			$date_raw=strtotime($date);

			if ($date_raw>=$week_start && $date_raw<=$week_end)
				break;

			$week_counter++;
		endforeach;

		return $week_counter;
	}

	/**
	 * add_weeks_to_season function.
	 *
	 * @access public
	 * @param string $season (default: '')
	 * @return void
	 */
	public function add_weeks_to_season($season='') {
		// start (first) week //
		$start_date_arr=explode(' ', $season['start']); // get start day
		$next_monday=strtotime('monday', mktime(0, 0, 0, date('n', strtotime($season['start'])), $start_date_arr[1], $start_date_arr[2])); // get next monday
		$next_sunday=strtotime('sunday', $next_monday); // get next sunday
		$first_monday=strtotime('-1 week', $next_monday); // prev monday is start
		$first_sunday=strtotime('-1 week', $next_sunday); // prev sunday is start

		// end (last) week //
		$end_date_arr=explode(' ', $season['end']); // get end day
		$last_monday=strtotime('monday', mktime(0, 0, 0, date('n', strtotime($season['end'])), $end_date_arr[1], $end_date_arr[2])); // get next monday
		$last_sunday=strtotime('sunday', $last_monday); // get next sunday
		$final_monday=strtotime('-1 week', $last_monday); // prev monday is start
		$final_sunday=strtotime('-1 week', $last_sunday); // prev sunday is start

		// build out all our weeks //
		$monday=$first_monday;
		$sunday=$first_sunday;

		while ($monday != $final_monday) :
	    $season['weeks'][]=array(
	    	'start' => date('c', $monday),
	    	'end' => date('c', $sunday)
	    );

	    $monday=strtotime('+1 week', $monday);
	    $sunday=strtotime('+1 week', $sunday);
		endwhile;

		// append final week //
		$season['weeks'][]=array(
			'start' => date('c', $final_monday),
			'end' => date('c', $final_sunday)
		);


		return $season;
	}

	/**
	 * setup_config function.
	 *
	 * @access protected
	 * @param array $args (default: array())
	 * @return void
	 */
	protected function setup_config($args=array()) {
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

		$this->config=json_decode(json_encode($config),FALSE); // convert to object and store
	}

}

$ucicurl_admin=new UCIcURLAdmin();
?>