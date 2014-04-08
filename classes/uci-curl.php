<?php
class Top25_cURL {

	public $table='uci_races';
	public $version='1.0.0';
	public $url='http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=485&StartDateSort=20130907&EndDateSort=20140223&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8';

	function __construct() {
		add_action('admin_menu',array($this,'admin_page'));
		add_action('admin_enqueue_scripts',array($this,'admin_scripts_styles'));
		
		add_action('wp_ajax_get-data',array($this,'ajax_get_data'));
	}	

	function admin_page() {
		add_options_page('UCI cURL', 'UCI cURL', 'administrator', 'uci-curl',array($this,'display_admin_page'));
		add_options_page('UCI View DB', 'UCI View DB', 'administrator', 'uci-view-db',array($this,'display_view_db_page'));
	}
	
	function admin_scripts_styles() {
		wp_enqueue_script('uci-curl-admin',UCICURLBASE.'/js/admin.js',array('jquery'),$this->version,true);
		
		wp_enqueue_style('uci-curl-admin',UCICURLBASE.'/css/admin.css',array(),$this->version);
	}
	
	function display_admin_page() {
		$html=null;
		$results=array();

		$html.='<div class="uci-curl">';
			$html.='<h3>cURL</h3>';
		
			$html.='<button type="button" name="get-race-data" id="get-race-data" value="yes">Load All Data</button>';
			
			$html.='<div class="data-results"></div>';
			
			$html.=$this->get_race_data(false);
			
			$html.='<div class="db-results"></div>';
			
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
		$races=array();
		$races_class_name="datatable";
		$races_obj=new stdClass();
		$arr=array();
		$timeout = 5;
				
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
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
						//$races[$row_count]->date=$this->get_race_date($link);
						$races[$row_count]->date=$this->reformat_date($col->nodeValue);
/* 						$race_data->date=$this->reformat_date($race_data->date); */
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
			
			return $arr;
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
				if ($race->code==$code) :
					return true;
				endif;
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

		$html.='<table class="race-table">';
			$html.='<tr class="header">';
				$html.='<td>Date</td>';
				$html.='<td>Event</td>';
				$html.='<td>Nat.</td>';
				$html.='<td>Class</td>';
				$html.='<td>Winner</td>';
				$html.='<td>Link</td>';
			$html.='</tr>';
			
			foreach ($obj as $result) :
				if ($alt%2) :
					$class='alt';
				else :
					$class=null;
				endif;
				$html.='<tr class="'.$class.'">';
					$html.='<td>'.$result->date.'</td>';
					$html.='<td>'.$result->event.'</td>';
					$html.='<td>'.$result->nat.'</td>';
					$html.='<td>'.$result->class.'</td>';
					$html.='<td>'.$result->winner.'</td>';
					$html.='<td class="race-link"><a href="#" data-link="'.$result->link.'">Results</a></td>';
				$html.='</tr>';
				$alt++;
			endforeach;
			
		$html.='</table>';
		
		return $html;
	}

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

	//----------------------------- UCI View DB Page functions -----------------------------//
	function display_view_db_page() {
		global $wpdb;
		$html=null;
		//$limit=3;
		
		$html.='<h3>View DB</h3>';
		
		$races=$wpdb->get_results("SELECT * FROM ".$this->table);
		
		foreach ($races as $key => $race) :
			$html.=$this->display_race_table($race,false);
			//if ($key==$limit-1)
				//break;
		endforeach;
		
		echo $html;
	}
	
	function display_race_table($race,$results=true) {
		global $field_quality;
		$html=null;
		$alt=0;
		$data=unserialize(base64_decode($race->data));
		set_time_limit(0); // unlimited max execution time //
		$math=$field_quality->get_race_math($data);
	
		if (!isset($data->results)) :
			$html.=$race->id.'<br />';
			$html.=$race->code.'<br />';
			$html.='This has no results<br>';
		endif;
		
		$html.='<table class="race-table">';
			$html.='<tr class="header">';
				$html.='<td>Date</td>';
				$html.='<td>Event</td>';
				$html.='<td>Nat.</td>';
				$html.='<td>Class</td>';
				$html.='<td>Winner</td>';
				$html.='<td>Link</td>';
				$html.='<td>Race Total</td>';
			$html.='</tr>';

			$html.='<tr>';
				$html.='<td>'.$data->date.'</td>';
				$html.='<td>'.$data->event.'</td>';
				$html.='<td>'.$data->nat.'</td>';
				$html.='<td>'.$data->class.'</td>';
				$html.='<td>'.$data->winner.'</td>';
				$html.='<td class="race-link"><a href="#" data-link="'.$data->link.'">Results</a></td>';
				$html.='<td>';
					foreach ($math as $k => $v) :
						$html.= $k.': '.$v.'<br />';
					endforeach;
				$html.='</td>';
			$html.='</tr>';
		$html.='</table>';

		if ($results) :
			$html.='<table class="race-results-full-table">';
				$html.='<tr class="header">';
					$html.='<td>Age</td>';
					$html.='<td>Name</td>';
					$html.='<td>Nat.</td>';
					$html.='<td>PAR</td>';
					$html.='<td>PCR</td>';
					$html.='<td>Place</td>';
					$html.='<td>Result</td>';
				$html.='</tr>';
				
				if (isset($data->results)) :
					foreach ($data->results as $result) :
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
				else :
					$html.=$race->id.' - This race had no results<br />';				
				endif;
			$html.='</table>';
		endif;
		
		$html.='<hr />';
		
		return $html;
	}

	//----------------------------- ajax functions -----------------------------//
	function ajax_get_data() {
		$results=array();
		
		switch ($_POST['type']) :
			case 'all':
				$results=$this->get_race_data();		
				break;
			case 'race':			
				$results=$this->get_race_results($_POST['link']);	
				$results=$this->build_default_race_results_table($results);			
				break;		
		endswitch;

		echo json_encode($results);
		
		exit;
	}
	/*
	$GLOBALS['normalizeChars'] = array(
	'?'=>'S', '?'=>'s', 'Ð'=>'Dj','?'=>'Z', '?'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
	'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
	'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
	'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
	'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
	'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
	'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', '?'=>'f');
	$string = strtr($string, $GLOBALS['normalizeChars']);
	*/	
}

$uci_curl=new Top25_cURL();



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