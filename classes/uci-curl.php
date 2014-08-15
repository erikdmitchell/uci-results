<?php
class Top25_cURL {

	public $table='uci_races';
	public $version='1.0.2';
	public $config=array();

	function __construct($config=array()) {
		add_action('admin_menu',array($this,'admin_page'));
		add_action('admin_enqueue_scripts',array($this,'admin_scripts_styles'));
		
		add_action('wp_ajax_get-data',array($this,'ajax_get_data'));

		$this->config=(object) $config;
	}	

	function admin_page() {
		add_menu_page('UCI Cross','UCI Cross','administrator','uci-cross',array($this,'display_admin_page'));
		add_submenu_page('uci-cross','UCI cURL','UCI cURL','administrator','uci-curl',array($this,'display_curl_page'));
		add_submenu_page('uci-cross','UCI View DB','UCI View DB','administrator','uci-view-db',array(new ViewDB(),'display_view_db_page'));
	}
	
	function admin_scripts_styles() {
		wp_enqueue_script('uci-curl-admin',UCICURLBASE.'/js/admin.js',array('jquery'),$this->version,true);
		
		wp_enqueue_style('uci-curl-admin',UCICURLBASE.'/css/admin.css',array(),$this->version);
	}

	function display_admin_page() {
		$html=null;
		$stats=new RaceStats();
		$rider_stats=new RiderStats();

		$html.='<div class="uci-cross">';
			$html.='<h2>UCI Cross</h2>';
			
			//$html.='<h3>Race Stats</h3>';
			//$html.=$stats->get_season_race_rankings(2);
			
			$html.='<h3>Rider Stats</h3>';
			$html.=$rider_stats->get_sos('Sven Nys');
		$html.='</div>';

		echo $html;
	}	

	function display_curl_page() {
		$html=null;
		$results=array();

		$html.='<div class="uci-curl">';
			$html.='<h3>cURL</h3>';
		
			//$html.='<button type="button" name="get-race-data" id="get-race-data" value="yes">Load All Data</button>';
			
			if (isset($_POST['submit']) && $_POST['submit']=='Add to DB' && isset($_POST['races'])) :	
				foreach ($_POST['races'] as $race) :		
					echo $this->add_race_to_db(unserialize(base64_decode($race)));
				endforeach;
			endif;
			
			$html.='<div class="data-results"></div>'; // empty?
			
			$html.=$this->get_race_data(false);
			
			$html.='<div class="db-results"></div>'; // empty?
			
		$html.='</div>';
		
		$html.='<div class="loading-modal"></div>';
		
		echo $html;
	}
	
	/**
	 * this function does all the magic as far as parseing results and adding them to the db
	 * @param true/false $add_to_db - whether or not we should run the add db function. NOTE: there will be no preformatted result if false
	 * @return array - the results of the db input (formatted for wp-admin)
	 *		the default is a table of race data for individual adding/info 
	**/
	function get_race_data($add_to_db=true) {
		set_time_limit(0); // mex ececution time
		$races=array();
		$races_class_name='datatable';
		$races_obj=new stdClass();
		$arr=array();
		$timeout = 5;
				
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		
		$html = curl_exec($ch);
		
		curl_close($ch);
		
		// Create a DOM parser object
		$dom = new DOMDocument();
		
		// Parse HTML - The @ before the method call suppresses any warnings that loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);
		
		//discard white space 
		@$dom->preserveWhiteSpace = false; 
		
		$finder = new DomXPath($dom);
		
		$nodes = $finder->query("//*[contains(@class, '$races_class_name')]");
		
		$rows=$nodes->item(0)->getElementsByTagName('tr'); //get all rows from the table
		
		// loop over the table rows
		$row_count=0;
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
				
				// check for code in db, only get results if not in db //
				$code=$this->build_race_code($races[$row_count]);
				if (!$this->check_for_dups($code)) :
					$races[$row_count]->results=$this->get_race_results($races[$row_count]->link); // run our curl result page stuff //
					//$fq=new Field_Quality();
					//$races[$row_count]->field_quality=$fq->get_race_math($races[$row_count]);	
				else :
					unset($races[$row_count]); // remove duplicate so it doesn't display
				endif;
			endif;
			$row_count++;			
		endforeach;
	
		foreach ($races as $key => $value) :
			$races_obj->$key=$value;
		endforeach;

		if ($add_to_db) :
			foreach ($races_obj as $race) :		
				$arr[]=$this->add_race_to_db($race);
			endforeach;
		endif;
	
		return $this->build_default_race_table($races_obj);
	}
	
	function alter_race_link($link) {
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

	function get_race_results($url) {
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

	function add_race_to_db($race_data) {
		global $wpdb;

		$message=null;
		$table='uci_races';

		// build data array ..
		$data=array(
			'data' => base64_encode(serialize($race_data)),
			'code' => $this->build_race_code($race_data),
		);	

		if (!$this->check_for_dups($data['code'])) :
			if ($wpdb->insert($table,$data)) :
				$message='<div class="updated">Added '.$data['code'].' to database.</div>';
			else :
				$message='<div class="error">Unable to insert '.$data['code'].' into the database.</div>';
			endif;
		else :
			$message='<div class="error">'.$data['code'].' is already in the database</div>';
		endif;
		
		return $message; 
	}

	//----------------------------- begin add_race_to_db helper functions -----------------------------//
	/**
	 * @param object $obj - race object
	 * takes the race name and date to build a string which becomes our "code" to prevent dups
	 * returns string
	**/
	function build_race_code($obj) {
		$code=$obj->event.$obj->date; // combine name and date
		$code=str_replace(' ','',$code); // remove spaces
		$code=strtolower($code); // make lowercase
		
		return $code;
	}
	
	/**
	 * @param string $code - race code
	 * compares race code to those in db; if true, there's a dup and we do not enter race
	 * returns true/false
	**/
	function check_for_dups($code) {
		global $wpdb;
		$table='uci_races';
		
		$races_in_db=$wpdb->get_results("SELECT code FROM ".$table);
		
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
	//----------------------------- end add_race_to_db helper functions -----------------------------//

	function reformat_date($date) {
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
		
		$date=$date_arr[0].' '.$date_arr[1].' '.$date_arr[2];
		
		return $date;
	}

	function build_default_race_table($obj) {
		$html=null;
		$alt=0;
		
		$html.='<form name="add-races-to-db" method="post">';
			$html.='<table class="race-table">';
				$html.='<tr class="header">';
					$html.='<td>&nbsp;</td>';
					$html.='<td>Date</td>';
					$html.='<td>Event</td>';
					$html.='<td>Nat.</td>';
					$html.='<td>Class</td>';
					$html.='<td>Winner</td>';
				$html.='</tr>';
				
				foreach ($obj as $result) :
					if ($alt%2) :
						$class='alt';
					else :
						$class=null;
					endif;
					$html.='<tr class="'.$class.'">';
						$html.='<td><input class="race-checkbox" type="checkbox" name="races[]" value="'.base64_encode(serialize($result)).'" /></td>';
						$html.='<td>'.$result->date.'</td>';
						$html.='<td>'.$result->event.'</td>';
						$html.='<td>'.$result->nat.'</td>';
						$html.='<td>'.$result->class.'</td>';
						$html.='<td>'.$result->winner.'</td>';
					$html.='</tr>';
					$alt++;
				endforeach;
				
				$html.='<tr>';
					$html.='<td colspan="2"><input type="checkbox" id="selectall" />Select All</td>';
				$html.='</tr>';
				
			$html.='</table>';
			
			$html.='<p class="submit">';
				$html.='<input type="submit" name="submit" id="submit" class="button button-primary" value="Add to DB">';
			$html.='</p>';
		$html.='</form>';
				
		return $html;
	}
	
	/**
	 * builds our default results table on curl page
	 * called by ajax function
	 */
	function build_default_race_results_table($obj) {
		$html=null;
		$alt=0;

		$html.='<table class="race-results-table">';
			$html.='<tr class="header">';
				$html.='<td>Age</td>';
				$html.='<td>Name</td>';
				$html.='<td>Nat.</td>';
				$html.='<td>PAR</td>';
				$html.='<td>PCR</td>';
				$html.='<td>Place</td>';
				$html.='<td>Result</td>';
			$html.='</tr>';
			
			foreach ($obj as $result) :
				if ($alt%2) :
					$class='alt';
				else :
					$class=null;
				endif;
				$html.='<tr class="'.$class.'">';
					$html.='<td>'.$result->place.'</td>';
					$html.='<td>'.$result->name.'</td>';
					$html.='<td>'.$result->nat.'</td>';
					$html.='<td>'.$result->age.'</td>';
					$html.='<td>'.$result->result.'</td>';
					$html.='<td>'.$result->par.'</td>';
					$html.='<td>'.$result->pcr.'</td>';		
				$html.='</tr>';
				$alt++;
			endforeach;
			
		$html.='</table>';
		
		return $html;
	}

	//----------------------------- ajax functions -----------------------------//
	function ajax_get_data() {
		global $field_quality;
		$results=array();
		
		switch ($_POST['type']) :
			case 'all':
				$results=$this->get_race_data();		
				break;
			case 'race':			
				$results=$this->get_race_results($_POST['link']);	
				$results=$this->build_default_race_results_table($results);			
				break;
			case 'race-data':
				$results=$field_quality->get_race_math_by_id($_POST['id']);
				break;
		endswitch;

		echo json_encode($results);
		
		exit;
	}
}

/** The same as curl_exec except tries its best to convert the output to utf8 **/
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