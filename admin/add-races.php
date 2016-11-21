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
		add_action('wp_ajax_get_race_data_non_db', array($this, 'ajax_get_race_data_non_db'));
		add_action('wp_ajax_prepare_add_races_to_db', array($this, 'ajax_prepare_add_races_to_db'));
		add_action('wp_ajax_add_race_to_db', array($this, 'ajax_add_race_to_db'));
	}

	/**
	 * ajax_get_race_data_non_db function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_get_race_data_non_db() {
		$form=array();
		parse_str($_POST['form'], $form);

		if (empty($form['url']))
			return false;

		$this->config->url=$form['url']; // set url
		$limit=false;

		// set limit if passed //
		if (!empty($form['limit']))
			$limit=$form['limit'];

		echo $this->get_race_data($form['season'], $limit);

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
	 * @param string $rows (default: '')
	 * @param bool $season (default: false)
	 * @param bool $limit (default: false)
	 * @return void
	 */
	public function build_races_object_from_rows($rows='', $season=false, $limit=false) {
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
			endif;

			// increase counter //
			$row_count++;

			// bail if we've reached our limit //
			if ($limit && $row_count>$limit)
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
	 * @return void
	 */
	public function get_race_data($season=false, $limit=false, $raw=false, $url=false) {
		set_time_limit(0); // mex ececution time

		$races_class_name='datatable';
		$timeout = 5;

		// if no passed url, use config //
		if (!$url) :
			if (!isset($this->config->url)) :
				return false;
			else :
				$url=$this->config->url;
			endif;
		endif;

		// check season //
		if (!$season || empty($season))
			$season=date('Y');

		$html=$this->get_url_page($url, $timeout); // get the html from the url
		$rows=$this->get_html_table_rows($html, $races_class_name); // grab our rows via dom object
		$races_obj=$this->build_races_object_from_rows($rows, $season, $limit); // build our races object from rows

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

		$empty_races=$wpdb->get_results("SELECT * FROM $wpdb->uci_results_races WHERE winner = '' AND date = '".$args['date']."' AND nat = '".$args['nat']."' AND class = '".$args['class']."' AND season = '".$args['season']."'");

		if (count($empty_races)==1)
			return $empty_races[0]->code;

/*
we need some sort of search to compare names
*/

		return false;
	}

	/**
	 * is_race_empty function.
	 *
	 * @access public
	 * @param string $code (default: '')
	 * @return void
	 */
	public function is_race_empty($code='') {
		global $wpdb;

		$winner=$wpdb->get_var("SELECT winner FROM $wpdb->uci_results_races WHERE code = '$code'");

		if ($winner!==null)
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

		//$id=$wpdb->get_var("SELECT id FROM $wpdb->uci_results_races WHERE code='$code'");
		$race=get_page_by_path($code, OBJECT, 'races');

		// we have id (aka code), but make sure it's not empty ie we preloaded the race //
		if ($race!==null) :
			if (!$this->is_race_empty($code)) :
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
		global $wpdb, $uci_results_twitter, $uci_results_pages, $ucicurl_races;

		$message=null;
		$new_results=0;
		
		if (empty($race_data))
			return false;

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
			'code' => $this->build_race_code($race_data),
			'week' => $this->get_race_week($race_data->date, $race_data->season),
		);

// rewrite check for dups (is race empty) //
// rewrite add results to db // -- IN PROGRESS
// clean up race results //
return "not run";		
		if (!$this->check_for_dups($data['code'])) :
			if ($race_id=$this->insert_race_into_db($data)) :
				$message='<div class="updated">Added '.$data['code'].' to database.</div>';
				$new_results++;
				$this->add_race_results_to_db($race_id, $race_data->link);

				// update to twitter //
/*
				if (uci_results_post_results_to_twitter()) :
					$url=get_permalink($uci_results_pages['single_race']).$data['code'];

					// use twitter if we have it //
					$twitter=$ucicurl_races->get_twitter($race_id);

					if (!empty($twitter))
						$twitter='@'.$twitter;

					$status=$race_data->winner.' wins '.$race_data->event.' ('.$race_data->class.') '.$twitter.' '.$url;
					$uci_results_twitter->update_status($status);
				endif;
*/
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
		$season_data=uci_results_get_season_weeks($season);

		if (!isset($season_data['weeks']) || empty($season_data['weeks']))
			return 0;

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
	 * add_race_results_to_db function.
	 *
	 * @access public
	 * @param bool $race_id (default: 0)
	 * @param bool $link (default: false)
	 * @return void
	 */
	public function add_race_results_to_db($race_id=0, $link=false) {
		global $wpdb;
		global $results_data;
		global $results_raw;

		if (!$race_id || !$link)
			return false;

		//$race_results=$this->get_race_results($link);
		$race=get_post($race_id);


		$race_results=$results_raw;

		foreach ($race_results as $result) :
			$rider=get_page_by_title($result->name, OBJECT, 'riders');

			// check if we have a rider id, otherwise create one //
			if ($rider===null || empty($rider->ID)) :
				$rider_insert=array(
					'post_title' => $result->name,
					'post_content' => '',
					'post_status' => 'publish',	
					'post_type' => 'riders',
					'post_name' => sanitize_title_with_dashes($result->name)
				);
				$rider_id=wp_insert_post($rider_insert);
				wp_set_object_terms($rider_id, $result->nat, 'country', false);
			else :
				$rider_id=$rider->ID;
			endif;

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

			$meta_value=array(
				'place' => $result->place,
				'name' => $result->name,
				'nat' => $result->nat,
				'age' => $result->age,
				'result' => $result->result,
				'par' => $par,
				'pcr' => $pcr,
			);			
			update_post_meta($race_id, "_rider_$rider_id", $meta_value);
		endforeach;
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

}

$uci_results_add_races=new UCIResultsAddRaces();
?>