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

		$this->setup_config($config);
	}

	/**
	 * admin_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_page() {
		add_menu_page('UCI Cross', 'UCI Cross', 'manage_options', 'uci-cross', array($this, 'display_admin_page'));

		add_submenu_page('uci-cross', 'UCI cURL', 'UCI cURL', 'manage_options', 'uci-curl', array($this, 'display_curl_page'));
		//add_submenu_page('uci-cross','UCI View DB','UCI View DB','administrator','uci-view-db',array(new ViewDB(),'display_view_db_page'));
	}

	/**
	 * admin_scripts_styles function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_scripts_styles() {
		wp_enqueue_script('uci-curl-admin',UCICURL_URL.'/js/admin.js',array('jquery'), '0.1.0',true);

		wp_enqueue_style('uci-curl-bootstrap',UCICURL_URL.'/css/bootstrap.css',array(),'3.3.5');
		wp_enqueue_style('uci-curl-admin',UCICURL_URL.'/css/admin.css',array(), '0.1.0');
	}

	/**
	 * display_admin_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function display_admin_page() {
		$html=null;
		$tabs=array(
			'uci-cross' => 'UCI Cross',
		);
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'uci-cross';

		$html.='<div class="wrap">';
			$html.='<h2>UCI Cross Admin Section</h2>';

			$html.='<h2 class="nav-tab-wrapper">';
				foreach ($tabs as $key => $name) :
					if ($active_tab==$key) :
						$class='nav-tab-active';
					else :
						$class=null;
					endif;

					$html.='<a href="?page=uci-cross&tab='.$key.'" class="nav-tab '.$class.'">'.$name.'</a>';
				endforeach;
			$html.='</h2>';

			switch ($active_tab) :
				case 'uci-cross':
					$html.=ucicurl_get_admin_page('main');
					break;
				default:
					$html.=ucicurl_get_admin_page('main');
					break;
			endswitch;

		$html.='</div><!-- /.wrap -->';

		echo $html;
	}

	public function riders_admin_page() {
		$html=null;
		$rider_stats=new RiderStats();

		$html.='<div class="riders-wrap">';
			$html.='<h3>Riders</h3>';

			$html.=$rider_stats->get_season_rider_rankings();


		$html.='</div>';

		return $html;
	}

	/**
	 * display_curl_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function display_curl_page() {
		echo ucicurl_get_admin_page('curl');
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

		if ($nodes->length==0) :
			if ($this->debug) :
				echo '<pre>';
				print_r($html);
				echo '</pre>';
			endif;

			return '<div class="error">No rows (nodes) found. Check the url and the "races class name" ('.$races_class_name.').</div>';
		endif;

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
		//$final_url.='&Ranking='.$arr['Ranking']; -- causes and error
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

		if (!is_object($race_data))
			$race_data=json_decode(json_encode($race_data),FALSE);

		// build data array ..
		$data=array(
			'date' => $date = date('Y-m-d', strtotime($race_data->date)),
			'event' => $race_data->event,
			'nat' => $race_data->nat,
			'class' => $race_data->class,
			'winner' => $race_data->winner,
			//'season' => $this->get_season_from_date($race_data->date),
			'season' => $race_data->season,
			'link' => $race_data->link,
			'code' => $this->build_race_code($race_data->event, $race_data->date),
		);

		if (!$this->check_for_dups($data['code'])) :
			if ($race_id=$wpdb->insert($wpdb->ucicurl_races, $data)) :
				$message='<div class="updated">Added '.$data['code'].' to database.</div>';
				$this->add_race_results_to_db($race_id, $race_data->link);
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
		//$race_data=$wpdb->get_row("SELECT * FROM $this->table WHERE code=\"$code\"");

		foreach ($race_results as $result) :
			$insert=array(
				'race_id' => $race_id,
				'place' => $result->place,
				'name' => $result->name,
				'nat' => $result->nat,
				'age' => $result->age,
				'result' => $result->result,
				'par' => $result->par,
				'pcr' => $result->pcr,
			);
print_r($insert);
			$wpdb->insert($wpdb->ucicurl_results, $insert);
		endforeach;
	}

	/**
	 * update_rider_rankings function.
	 *
	 * @access public
	 * @param bool $season (default: false)
	 * @return void
	 */
	public function update_rider_rankings($season=false) {
		global $RaceStats,$wpdb;

		$CrossSeasons=new CrossSeasons();

		// setup our season //
		if (!$season || $season=='' || $season=='Select Year')
			$season=end($CrossSeasons->seasons);

		$races_per_week=$CrossSeasons->get_races_in_season_by_week($season); // get races per week

		// get race results and total //
		$codes=array();
		foreach ($races_per_week as $week) :
			// this will stop everything when we get to a week without races //
			if (empty($week->races))
				break;

			// get race codes and append to array //
			foreach ($week->races as $race) :
				$codes[]=$race->code;
			endforeach;

			echo $sql="
				SELECT
					name,
					country,
					SUM(c2) AS c2,
					SUM(c1) AS c1,
					SUM(cn) AS cn,
					SUM(cc) AS cc,
					SUM(wcp_total) AS wcp_total,
					SUM(cm) AS cm,
					SUM(uci_total) AS uci_total,
					SUM(wins/rider_races) AS win_perc,
					SUM(fq_avg/(total_races/rider_races)) AS sos,
					SUM((uci_total+(wins/rider_races)+(fq_avg/(total_races/rider_races)))/3) AS total
				FROM (
					SELECT
						results.name AS name,
						results.nat AS country,
						0 AS c2,
						0 AS c1,
						0 AS cn,
						0 AS cc,
						0 AS cm,
						SUM(results.par) AS uci_total,
						SUM(IF(results.place=1,1,0)) AS wins,
						COUNT(results.code) AS rider_races,
						COALESCE((SELECT SUM(fq_table.fq) / COUNT(fq_table.fq) ),0) AS fq_avg,
						(SELECT COUNT(*) FROM $this->table AS races WHERE races.code IN (\"".implode('","',$codes)."\")) AS total_races,
						0 AS wcp_total
					FROM $this->results_table AS results
					LEFT JOIN $this->table AS races
					ON results.code=races.code
					LEFT JOIN $this->fq_table AS fq_table
					ON fq_table.code=races.code
					WHERE results.code IN (\"".implode('","',$codes)."\")
					GROUP BY name

					UNION

					SELECT
						results.name AS name,
						results.nat AS country,
						0 AS c2,
						0 AS c1,
						0 AS cn,
						0 AS cc,
						0 AS cm,
						0 AS uci_total,
						0 AS wins,
						0 AS rider_races,
						0 AS fq_avg,
						0 AS total_races,
						SUM(results.par) AS wcp_total
					FROM $this->results_table AS results
					LEFT JOIN $this->table AS races
					ON results.code=races.code
					WHERE results.code IN (\"".implode('","',$codes)."\")
					AND races.class='CDM'
					GROUP BY name

					UNION

					SELECT
						results.name AS name,
						results.nat AS country,
						SUM(results.par) AS c2,
						0 AS c1,
						0 AS cn,
						0 AS cc,
						0 AS cm,
						0 AS uci_total,
						0 AS wins,
						0 AS rider_races,
						0 AS fq_avg,
						0 AS total_races,
						0 AS wcp_total
					FROM $this->results_table AS results
					LEFT JOIN $this->table AS races
					ON results.code=races.code
					WHERE results.code IN (\"".implode('","',$codes)."\")
					AND races.class='C2'
					GROUP BY name

					UNION

					SELECT
						results.name AS name,
						results.nat AS country,
						0 AS c2,
						SUM(results.par) AS c1,
						0 AS cn,
						0 AS cc,
						0 AS cm,
						0 AS uci_total,
						0 AS wins,
						0 AS rider_races,
						0 AS fq_avg,
						0 AS total_races,
						0 AS wcp_total
					FROM $this->results_table AS results
					LEFT JOIN $this->table AS races
					ON results.code=races.code
					WHERE results.code IN (\"".implode('","',$codes)."\")
					AND races.class='C1'
					GROUP BY name

					UNION

					SELECT
						results.name AS name,
						results.nat AS country,
						0 AS c2,
						0 AS c1,
						SUM(results.par) AS cn,
						0 AS cc,
						0 AS cm,
						0 AS uci_total,
						0 AS wins,
						0 AS rider_races,
						0 AS fq_avg,
						0 AS total_races,
						0 AS wcp_total
					FROM $this->results_table AS results
					LEFT JOIN $this->table AS races
					ON results.code=races.code
					WHERE results.code IN (\"".implode('","',$codes)."\")
					AND races.class='CN'
					GROUP BY name

					UNION

					SELECT
						results.name AS name,
						results.nat AS country,
						0 AS c2,
						0 AS c1,
						0 AS cn,
						0 AS cc,
						SUM(results.par) AS cm,
						0 AS uci_total,
						0 AS wins,
						0 AS rider_races,
						0 AS fq_avg,
						0 AS total_races,
						0 AS wcp_total
					FROM $this->results_table AS results
					LEFT JOIN $this->table AS races
					ON results.code=races.code
					WHERE results.code IN (\"".implode('","',$codes)."\")
					AND races.class='CM'
					GROUP BY name

				) t
				GROUP BY name
				ORDER BY total DESC

			";
			$rank=1;
			$riders=$wpdb->get_results($sql);

			// build data for each rider aka result //
			foreach ($riders as $rider) :
				$data=array(
					'name' => $rider->name,
					'country' => $rider->country,
					'season' => $season,
					'rank' => $rank,
					'week' => $week->week,
					'c2' => $rider->c2,
					'c1' => $rider->c1,
					'cn' => $rider->cn,
					'cc' => $rider->cc,
					'cm' => $rider->cm,
					'uci_total' => $rider->uci_total,
					'wcp_total' => $rider->wcp_total,
					'win_perc' => $rider->win_perc,
					'sos' => $rider->sos,
					'sos_rank' => 0,
					'total' => $rider->total,
				);

				$rank++;

				// updated if in db, else insert //
				if ($id=$wpdb->get_var("SELECT id FROM $this->uci_rider_rankings WHERE name=\"{$rider->name}\" AND week=$week->week")) :
					$wpdb->update($this->uci_rider_rankings,$data,array('id' => $id));
				else :
					$wpdb->insert($this->uci_rider_rankings,$data);
				endif;

				echo '<div class="updated">'.$rider->name.' has been updated in the rider rankings db for week '.$week->week.'</div>';

			endforeach; // riders
		endforeach; // races per week
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
		$code=str_replace(' ','',$code); // remove spaces
		$code=strtolower($code); // make lowercase

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
		$season_start='Aug 1';
		$season_end='Apr 1';
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
			$html.='<table class="ulm-usac-clubs-table wp-list-table widefat fixed striped pages">';
				$html.='<thead>';
					$html.='<tr>';
						$html.='<td id="cb" class="check-column"><input type="checkbox" id="select-all"></td>';
						$html.='<th scope="col" class="race-date">Date</th>';
						$html.='<th scope="col" class="race-name">Event</th>';
						$html.='<th scope="col" class="race-nat">Nat.</th>';
						$html.='<th scope="col" class="race-class">Class</th>';
					$html.='</tr>';
				$html.='</thead>';
				$html.='<tbody>';

					foreach ($obj as $result) :
						$disabled='';
						$date=$result->date;

						if (!isset($result->event)) :
							$result->event=false;
						else :
							$event=$result->event;
						endif;

						if (!$result->date || !$result->event)
							$disabled='disabled';

						if (!$result->date)
							$date='No Date';

						if (!$result->event)
							$event='No Event';

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
	 * get_years_in_db function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_years_in_db() {
		global $wpdb;

		$years=$wpdb->get_col("SELECT season FROM $this->table WHERE season!='false' GROUP BY season");

		return $years;
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

/**
 * curl_exec_utf8 function.
 *
 * The same as curl_exec except tries its best to convert the output to utf8
 *
 * @access public
 * @param mixed $ch
 * @return void
 */
function curl_exec_utf8($ch) {
    $data = curl_exec($ch);
    if (!is_string($data)) return $data;

    unset($charset);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    /* 1: HTTP Content-Type: header */
    preg_match( '@([\w/+]+)(;\s*charset=(\S+))?@i', $content_type, $matches );
    if ( isset( $matches[3] ) )
        $charset = $matches[3];

    /* 2: <meta> element in the page */
    if (!isset($charset)) {
        preg_match( '@<meta\s+http-equiv="Content-Type"\s+content="([\w/]+)(;\s*charset=([^\s"]+))?@i', $data, $matches );
        if ( isset( $matches[3] ) )
            $charset = $matches[3];
    }

    /* 3: <xml> element in the page */
    if (!isset($charset)) {
        preg_match( '@<\?xml.+encoding="([^\s"]+)@si', $data, $matches );
        if ( isset( $matches[1] ) )
            $charset = $matches[1];
    }

    /* 4: PHP's heuristic detection */
    if (!isset($charset)) {
        $encoding = mb_detect_encoding($data);
        if ($encoding)
            $charset = $encoding;
    }

    /* 5: Default for HTML */
    if (!isset($charset)) {
        if (strstr($content_type, "text/html") === 0)
            $charset = "ISO 8859-1";
    }

    /* Convert it if it is anything but UTF-8 */
    /* You can change "UTF-8"  to "UTF-8//IGNORE" to
       ignore conversion errors and still output something reasonable */
    if (isset($charset) && strtoupper($charset) != "UTF-8")
        $data = iconv($charset, 'UTF-8', $data);

    return $data;
}
?>