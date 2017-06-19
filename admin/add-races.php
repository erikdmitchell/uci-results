<?php
global $uci_results_add_races;

/**
 * UCIResultsAddRaces class.
 */
class UCIResultsAddRaces {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts_styles'));
		add_action('admin_init', array($this, 'add_csv_results_to_race'));
		add_action('wp_ajax_get_race_data_non_db', array($this, 'ajax_get_race_data_non_db'));
		add_action('wp_ajax_prepare_add_races_to_db', array($this, 'ajax_prepare_add_races_to_db'));
		add_action('wp_ajax_add_race_to_db', array($this, 'ajax_add_race_to_db'));
		add_action('wp_ajax_process_csv_results', array($this, 'ajax_process_results_csv'));
	}
	
	public function admin_scripts_styles() {
		wp_enqueue_script('uci-results-add-races-admin-script', UCI_RESULTS_ADMIN_URL.'js/add-races.js', array('uci-results-admin'), '0.1.0', true);
	}

	/**
	 * ajax_get_race_data_non_db function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_get_race_data_non_db() {
		echo $this->get_race_data($_POST['season'], false, false, false, $_POST['discipline']);

		wp_die();
	}

	/**
	 * get_url_page function.
	 *
	 * @access public
	 * @param string $url (default: '')
	 * @param int $timeout (default: 5)
	 * @return void
	 */
	public function get_url_page($url='', $timeout=5) {
		if (empty($url))
			return false;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

		$html=curl_exec($ch);

		curl_close($ch);

		return $html;
	}

	/**
	 * get_html_table_rows function.
	 *
	 * @access public
	 * @param string $html (default: '')
	 * @param string $class_name (default: 'datatable')
	 * @return void
	 */
	public function get_html_table_rows($html='', $class_name='datatable') {
		if (empty($html))
			return false;

		// Create a DOM parser object
		$dom = new DOMDocument();

		// Parse HTML - The @ before the method call suppresses any warnings that loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);

		//discard white space
		@$dom->preserveWhiteSpace = false;

		$finder = new DomXPath($dom);

		$nodes = $finder->query("//*[contains(@class, '$class_name')]");

		if ($nodes->length==0)
			return false;
			//return '<div class="error">No rows (nodes) found. Check the url and the "races class name" ('.$class_name.').</div>';

		$rows=$nodes->item(0)->getElementsByTagName('tr'); //get all rows from the table

		return $rows;
	}

	/**
	 * build_races_object_from_rows function.
	 * 
	 * @access public
	 * @param string $args (default: '')
	 * @return void
	 */
	public function build_races_object_from_rows($args='') {
		$default_args=array(
			'rows' => '',
			'season' => false,
			'limit' => false,
			'discipline' => 'cyclocross'
		);
		$args=wp_parse_args($args, $default_args);
	
		extract($args);
			
		$races=array();
		$races_obj=new stdClass();
		$row_count=0;
		$timeout=5;
		$races_class_name='datatable';

		// bail if no rows //
		if (empty($rows))
			return $races_obj;

		// cycle through rows //
		foreach ($rows as $row) :
			if ($row_count!=0) :
				$races[$row_count]=new stdClass();

				// get columns (td) //
			  $cols=$row->getElementsByTagName('td'); // get each column by tag name

			  // get (results) link //
			  $link=$this->alter_race_link($row->getElementsByTagName('a')->item(0)->getAttribute('href'));

			  // check for multi day event //
			  if ($days=$this->is_multi_day_race($cols->item(0)->nodeValue))
			  	continue;

				// use our cols to build out races object //
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
				$races[$row_count]->season=$season;
				$races[$row_count]->discipline=$discipline;
			endif;

			// increase counter //
			$row_count++;

			// bail if we've reached our limit //
			if ($limit > 0 && $row_count > $limit)
				break;

		endforeach;

		// setup as object //
		foreach ($races as $key => $value) :
			if (empty($value))
				continue;

			$races_obj->$key=$value;
		endforeach;

		return $races_obj;
	}

	/**
	 * get_race_data function.
	 * 
	 * @access public
	 * @param bool $season (default: false)
	 * @param bool $limit (default: false)
	 * @param bool $raw (default: false)
	 * @param bool $url (default: false)
	 * @param string $discipline (default: 'cyclocross')
	 * @return void
	 */
	public function get_race_data($season=false, $limit=false, $raw=false, $url=false, $discipline='cyclocross') {
		global $uci_results_admin;
		
		set_time_limit(0); // mex ececution time

		$races_class_name='datatable';
		$timeout = 5;

		// if no passed url, use config //
		if (!$url && isset($uci_results_admin->config->urls->$discipline->$season)) :		
			$url=$uci_results_admin->config->urls->$discipline->$season;
		elseif (!$url) :
			return false;
		endif;

		// check season //
		if (!$season || empty($season))
			return false;

		$html=$this->get_url_page($url, $timeout); // get the html from the url
		$rows=$this->get_html_table_rows($html, $races_class_name); // grab our rows via dom object
		
		// build our races object from rows
		$races_obj=$this->build_races_object_from_rows(array(
			'rows' => $rows,
			'season' => $season, 
			'limit' => $limit,
			'discipline' => $discipline,
		));

		// return object if $raw is true //
		if ($raw)
			return $races_obj;

		return $this->build_default_race_table($races_obj);
	}

	/**
	 * is_multi_day_race function.
	 *
	 * @access public
	 * @param string $date (default: '')
	 * @return void
	 */
	public function is_multi_day_race($date='') {
		// clean date //
		$date = htmlentities($date, null, 'utf-8');
		$date = str_replace("&nbsp;", "", $date);

		// if we have a '-' then it's a multi day so we return an array of start/end //
		if (strpos($date, '-') !== false) :
			$dates=explode('-', $date);

			// the first date will lack a year //
			$year=substr($dates[1], -4);
			$dates[0].=$year;

			return $dates;
		endif;

		return false;
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
						$code=$this->build_race_code($result);

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
						if (uci_results_race_has_results($code))
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
	 * build_race_code function.
	 *
	 * @access public
	 * @param string $args (default: '')
	 * @return void
	 */
	public function build_race_code($args='') {
		$default=array(
			'event' => '',
			'date' => date('Y-m-d'),
		);
		$args=wp_parse_args($args, $default);

		$code_created=$this->check_for_race_already_created($args);

		if ($code_created)
			return $code_created;

		$code=$args['event'].$args['date']; // combine name and date
		$code=sanitize_title_with_dashes($code); // https://codex.wordpress.org/Function_Reference/sanitize_title_with_dashes

		return $code;
	}

	/**
	 * check_for_race_already_created function.
	 *
	 * @access public
	 * @param string $args (default: '')
	 * @return void
	 */
	public function check_for_race_already_created($args='') {
		global $wpdb;

		$default=array(
			'date' => date('Y-m-d'),
			'nat' => '',
			'class' => '',
			'season' => ''
		);
		$args=wp_parse_args($args, $default);

		$races=get_posts(array(
			'post_type' => 'races',
			'tax_query' => array(
				array(
					'taxonomy' => 'country',
					'field' => 'name',
					'terms' => $args['nat']
				),
				array(
					'taxonomy' => 'race_class',
					'field' => 'name',
					'terms' => $args['class']
				),		
				array(
					'taxonomy' => 'season',
					'field' => 'name',
					'terms' => $args['season']
				)
			),
			'meta_query' => array(
				array(
					'key' => '_race_winner',
					'value' => '',
				),
				array(
					'key' => '_race_date',
					'value' => $args['date'],
				),
			)
		));

		if (count($races))
			return $races[0]->post_slug;

		// we need some sort of search to compare names

		return false;
	}

	/**
	 * is_race_empty function.
	 *
	 * @access public
	 * @param string $id (default: 0)
	 * @return void
	 */
	public function is_race_empty($id=0) {
		global $wpdb;

		$winner=get_post_meta($id, '_race_winner', true);	

		if ($winner !== null || $winner != '')
			return false;

		return true;
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
		parse_str($link, $arr);

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
	 * reformat_date function.
	 *
	 * @access public
	 * @param mixed $date
	 * @return void
	 */
	public function reformat_date($date) {
		$date = htmlentities($date, null, 'utf-8');
		$date = str_replace("&nbsp;", "", $date);

		// if we have a '-' then it's a multi day so we return base date //
		if (strpos($date, '-') !== false)
			return $date;

		// make "readable" to php date //
		$day=substr($date, 0, 2);
		$month=date('m', strtotime(substr($date, 2, 3)));
		$year=substr($date, 5);

		$date="$year-$month-$day";

		return $date;
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
	 * check_for_dups function.
	 *
	 * @access public
	 * @param mixed $code
	 * @return void
	 */
	public function check_for_dups($code='') {
		global $wpdb;

		$race=get_page_by_path($code, OBJECT, 'races');

		// we have race, but make sure it's not empty ie we preloaded the race //
		if ($race !== null) :
			if (!$this->is_race_empty($race->ID)) :
				return true;
			else :
				return false;
			endif;
		endif;

		return false;
	}

	/**
	 * add_race_to_db function.
	 *
	 * @access public
	 * @param string $race_data (default: '')
	 * @param bool $raw_response (default: false)
	 * @return void
	 */
	public function add_race_to_db($race_data='', $raw_response=false) {
		global $wpdb, $uci_results_twitter, $uci_results_pages;

		$message=null;
		$new_results=0;
		
		if (empty($race_data))
			return false;

		// convert to object //
		if (!is_object($race_data))
			$race_data=json_decode(json_encode($race_data),FALSE);

		// build data array .. -- if you change this, please change get_add_race_to_db()
		$data=array(
			'date' => $date = date('Y-m-d', strtotime($race_data->date)),
			'event' => trim($race_data->event),
			'nat' => $race_data->nat,
			'class' => $race_data->class,
			'winner' => $race_data->winner,
			'season' => $race_data->season,
			'link' => $race_data->link,
			'code' => $this->build_race_code($race_data),
			'week' => $this->get_race_week($race_data->date, $race_data->season),
			'discipline' => $race_data->discipline,
		);

		if (!$this->check_for_dups($data['code'])) :		
			if ($race_id=$this->insert_race_into_db($data)) :
				$message='<div class="updated">Added '.$data['code'].' to database.</div>';
				$new_results++;
				$this->add_race_results_to_db($race_id, $race_data->link);

				// update to twitter //
				if (uci_results_post_results_to_twitter()) :
					$url=get_permalink($uci_results_pages['single_race']).$data['code'];

					// use twitter if we have it //
					$twitter=uci_get_race_twitter($race_id);

					if (!empty($twitter))
						$twitter='@'.$twitter;

					$status=$race_data->winner.' wins '.$race_data->event.' ('.$race_data->class.') '.$twitter.' '.$url;
					$uci_results_twitter->update_status($status);
				endif;
			else :
				$message='<div class="error">Unable to insert '.$data['code'].' into the database.</div>';
			endif;

		else :		
			$message='<div class="updated">'.$data['code'].' is already in the database</div>';
		endif;

		if ($raw_response)
			return array('message' => $message, 'new_result' => $new_results);

		return $message;
	}
	
	/**
	 * get_add_race_to_db function.
	 * 
	 * @access public
	 * @param string $race_data (default: '')
	 * @param string $args (default: '')
	 * @return void
	 */
	public function get_add_race_to_db($race_data='', $args='') {
		global $wpdb, $uci_results_twitter, $uci_results_pages, $ucicurl_races;

		$data=array();

		// convert to object //
		if (!is_object($race_data))
			$race_data=json_decode(json_encode($race_data),FALSE);

		// build race data array //
		$data=array(
			'date' => $date = date('Y-m-d', strtotime($race_data->date)),
			'event' => $race_data->event,
			'nat' => $race_data->nat,
			'class' => $race_data->class,
			'winner' => $race_data->winner,
			'season' => $race_data->season,
			'link' => $race_data->link,
			'code' => $this->build_race_code($race_data),
			'week' => $this->get_race_week($race_data->date, $race_data->season),
			'discipline' => $race_data->discipline,
		);
		
		return $data;
	}
	
	/**
	 * get_add_race_to_db_results function.
	 * 
	 * @access public
	 * @param string $link (default: '')
	 * @param bool $formatted (default: false)
	 * @param int $race_id (default: 0)
	 * @return void
	 */
	public function get_add_race_to_db_results($link='', $formatted=false, $race_id=0) {
		global $wpdb;

		if (empty($link))
			return;

		// get race results data //
		$data=array();
		$race_results=$this->get_race_results($link);
	
		if (!$formatted)
			return $race_results;

		foreach ($race_results as $result) :
			$rider_id=uci_get_rider_id_by_name($result->name);			

			// check if we have a rider id, otherwise create one //
			if (!$rider_id)
				$rider_id='CREATE';

			if (!isset($result->par) || empty($result->par) || is_null($result->par)) :
				$par=0;
			else :
				$par=$result->par;
			endif;

			if (!isset($result->pcr) || empty($result->pcr) || is_null($result->pcr)) :
				$pcr=0;
			else :
				$pcr=$result->pcr;
			endif;

			$data[]=array(
				'race_id' => $race_id,
				'place' => $result->place,
				'name' => $result->name,
				'nat' => $result->nat,
				'age' => $result->age,
				'result' => $result->result,
				'par' => $par,
				'pcr' => $pcr,
				'rider_id' => $rider_id,
			);
		endforeach;

		return $data;		
	}

	/**
	 * insert_race_into_db function.
	 *
	 * @access public
	 * @param string $data (default: '')
	 * @return void
	 */
	public function insert_race_into_db($data='') {
		global $wpdb;

		if (empty($data))
			return false;
		
		$post_id=0;
		$race=get_page_by_path($data['code'], OBJECT, 'races');
		$race_data=array(
			'post_title' => $data['event'],
			'post_content' => '',
			'post_status' => 'publish',	
			'post_type' => 'races',
			'post_name' => $data['code'],			
		);

		// if race is null, add it, else update it //
		if ($race === null) :
			$post_id=wp_insert_post($race_data);
		else :
			$race_data['ID']=$race->ID;
		 	$post_id=wp_update_post($race_data);
		endif;		
		
		// check for error //
		if (is_wp_error($post_id))
			return false;
			
		// update taxonomies //
		wp_set_object_terms($post_id, $data['nat'], 'country', false);
		wp_set_object_terms($post_id, $data['class'], 'race_class', false);
		wp_set_object_terms($post_id, $data['season'], 'season', false);
		wp_set_object_terms($post_id, $data['discipline'], 'discipline', false);
		
		// update meta //
		update_post_meta($post_id, '_race_date', $data['date']);
		update_post_meta($post_id, '_race_winner', $data['winner']);
		update_post_meta($post_id, '_race_week', $data['week']);
		update_post_meta($post_id, '_race_link', $data['link']);

		return $post_id;
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
		$season_weeks=uci_results_get_season_weeks($season);

		if (empty($season_weeks))
			return 0;

		return $this->get_week_of_date($date, $season_weeks);
	}

	/**
	 * get_week_of_date function.
	 *
	 * @access public
	 * @param string $date (default: '')
	 * @param array $weeks (default:'')
	 * @return void
	 */
	public function get_week_of_date($date='', $weeks='') {
		if (empty($weeks))
			return;
			
		// cycle through weeks and if date falls in there, return the week //
		foreach ($weeks as $week) :		
			$week_start=strtotime($week->start);
			$week_end=strtotime($week->end);
			$date_raw=strtotime($date);

			if ($date_raw>=$week_start && $date_raw<=$week_end)
				return $week->week;
				
		endforeach;

		return 0;
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
		if (!$race_id || !$link)
			return false;

		$race_results=$this->get_race_results($link);

		$this->insert_race_results($race_id, $race_results);
	}
	
	/**
	 * insert_race_results function.
	 * 
	 * @access protected
	 * @param string $race_id (default: '')
	 * @param string $race_results (default: '')
	 * @return void
	 */
	protected function insert_race_results($race_id='', $race_results='') {
		if (empty($race_id) || empty($race_results))
			return;
			
		$discipline=strtolower(uci_get_first_term($race_id, 'discipline'));

		foreach ($race_results as $result) :
			$meta_value=array();
			$rider_id=$this->insert_race_results_rider_id($result->name, $result->nat);

			// essentially converts our object to an array //			
			foreach ($result as $key => $value) :
				$meta_value[$key]=$value;
			endforeach;

			// filter value //
			$meta_value=apply_filters('uci_results_insert_race_result_'.$discipline, $meta_value, $race_id, $result, $rider_id);			

			update_post_meta($race_id, "_rider_$rider_id", $meta_value);
		endforeach;			
	}
	
	/**
	 * insert_race_results_rider_id function.
	 * 
	 * @access protected
	 * @param string $rider_name (default: '')
	 * @param string $rider_country (default: '')
	 * @return void
	 */
	protected function insert_race_results_rider_id($rider_name='', $rider_country='') {
		if (empty($rider_name))
			return 0;
			
		$rider=get_page_by_title($rider_name, OBJECT, 'riders');

		// check if we have a rider id, otherwise create one //
		if ($rider===null || empty($rider->ID)) :
			$rider_insert=array(
				'post_title' => $rider_name,
				'post_content' => '',
				'post_status' => 'publish',	
				'post_type' => 'riders',
				'post_name' => sanitize_title_with_dashes($rider_name)
			);
			$rider_id=wp_insert_post($rider_insert);
			
			wp_set_object_terms($rider_id, $rider_country, 'country', false);
		else :
			$rider_id=$rider->ID;
		endif;
		
		return $rider_id;			
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
		//$race_results_row_arr=array('place', 'name', 'nat', 'age', 'result', 'par', 'pcr');

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

		// get all rows from the table //
		$rows=$nodes->item(0)->getElementsByTagName('tr');

		// get header row //
		$header_row=$rows->item(0); //->getElementsByTagName('tr'); //get all rows from the table
		$header_cols=$header_row->getElementsByTagName('td');
		$header_arr=array();

		foreach ($header_cols as $col) :
			$col_value=preg_replace("/[^A-Za-z0-9 ]/", '', $col->nodeValue); // remove non alpha chars
			$col_value=strtolower($col_value); // make lowercase

			$header_arr[]=$col_value;
		endforeach;

		// build our rows (results) //
		foreach ($rows as $row_key => $row) :
			// skip header row (first) //
			if ($row_key==0)
				continue;

			// process row //
			$race_results[$row_key]=new stdClass();
			$cols=$row->getElementsByTagName('td'); 	// get each column by tag name
			$col_values=array();

			// get col values //
			foreach ($cols as $key => $col) :
		  		$col_values[]=$col->nodeValue;
		  	endforeach;

		  	// make our results row array w/ header as key(s) //
		  	$race_results[$row_key]=array_combine($header_arr, $col_values);

		  	// we need to make one swap - rank/place //
		  	$race_results[$row_key]['place']=$race_results[$row_key]['rank'];
		  	unset($race_results[$row_key]['rank']);
		endforeach;

		// convert everything to an object //
		foreach ($race_results as $key => $value) :
			$race_results_obj->$key=(object) $value;
		endforeach;

		return $race_results_obj;
	}

	public function ajax_process_results_csv() {
		$form=array();
	
		foreach ($_POST['form'] as $arr) :
			$form[$arr['name']]=$arr['value'];
		endforeach;

		$data=$this->upload_csv_results($form);
	
		echo $this->csv_file_display($data);
	
		wp_die();
	}

	/**
	 * upload_csv_results function.
	 * 
	 * @access protected
	 * @param array $form (default: array())
	 * @return void
	 */
	protected function upload_csv_results($form=array()) {
		if (!isset($form['uci_results']) || !wp_verify_nonce($form['uci_results'], 'add-race-csv'))
			return false;
			
		if (empty($form['race_id']))
			$form['race_id']=$form['race_search_id'];
		
		$data=$this->process_csv_file($form['file']);
		$data['race_id']=$form['race_id'];	
		
		return $data;
	}
	
	/**
	 * process_csv_file function.
	 * 
	 * @access protected
	 * @param string $file (default: '')
	 * @return void
	 */
	protected function process_csv_file($file='') {
		global $wpdb;
		
		if (empty($file) || $file=='')
			return false;
		
		ini_set('auto_detect_line_endings',TRUE); // added for issues with MAC
		
		$data=array();
		$file=wp_remote_fopen($file);
    	$file=str_replace("\r\n", "\n", trim($file));
    	$rows=explode("\n", $file);
 
		// turn into easier to digest array //
    	foreach ($rows as $row => $cols) :
			$cols=str_getcsv($cols, ',');
			
			$data[]=$cols;
		endforeach;
		
		if (empty($data))
			return false;
			
		$header_row=array_shift($data);
		$header_row=array_map('sanitize_key', $header_row);		
		
		// builds out a more cleaner arr //
		foreach ($data as $key => $row) :
			$arr=array();
			
			foreach ($row as $k => $v) :
				$arr[$header_row[$k]]=$v;	
			endforeach;
			
			$data[$key]=$arr;
		endforeach;
		
		$clean_arr=array(
			'header' => $header_row,
			'rows' => $data	
		);

		return $clean_arr;		
	}
	
	/**
	 * csv_file_display function.
	 * 
	 * @access public
	 * @param array $arr (default: array())
	 * @return void
	 */
	public function csv_file_display($arr=array()) {
		if (empty($arr))
			return;
			
		$html='';
		
		$html.='<div class="race-info">';
			$html.='<h4>'.get_the_title($arr['race_id']).' <span class="race-date">'.get_post_meta($arr['race_id'], '_race_date', true).'</span></h4>';
		$html.='</div>';		
		
		$html.='<table class="form-table">';
		
		if (isset($arr['header'])) :
			$html.='<tr>';
			
				foreach ($arr['header'] as $head) :
					$html.='<th>'.$head.'</th>';
				endforeach;
			
			$html.='</tr>';
		endif;
		
		foreach ($arr['rows'] as $row_counter => $row) :
			$html.='<tr>';
				
				foreach ($row as $key => $col) :
					$html.='<td><input type="text" name="race[results]['.$row_counter.']['.$key.']" class="'.$key.'" value="'.$col.'" /></td>';
				endforeach;
				
			$html.='</tr>';
		endforeach;

		$html.='</table>';
		
		return $html;
	}
	
	/**
	 * add_csv_results_to_race function.
	 * 
	 * @access public
	 * @return void
	 */
	public function add_csv_results_to_race() {
		if (!isset($_POST['uci_results']) || !wp_verify_nonce($_POST['uci_results'], 'add-csv-data'))
			return;
			
		$results=array_to_object($_POST['race']['results']);
		
		$this->insert_race_results($_POST['race']['race_id'], $results);
	}

}

$uci_results_add_races=new UCIResultsAddRaces();
?>