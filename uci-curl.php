<?php
/**
 * Plugin Name: UCI cURL
 * Plugin URI: 
 * Description: 
 * Version: 1.0.0
 * Author: 
 * Author URI: 
 * License: 
 */

class Top25_cURL {

	public $table='uci_races';
	public $version='1.0.0';
	
	function __construct() {
		$this->url='http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=306&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=485&StartDateSort=20130907&EndDateSort=20140223&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8';
		
/*
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL,$this->url);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_HEADER,false);
			
		$this->result=curl_exec($curl);
		$this->result_arr=explode("\n",utf8_encode($this->result));
			
		curl_close($curl);
*/
		
		add_action('admin_menu',array($this,'admin_page'));
		add_action('admin_enqueue_scripts',array($this,'admin_scripts_styles'));
	}
	
	function admin_page() {
		add_options_page('UCI cURL', 'UCI cURL', 'administrator', 'uci-curl',array($this,'display_admin_page'));
		add_options_page('UCI View DB', 'UCI View DB', 'administrator', 'uci-view-db',array($this,'display_view_db_page'));
	}
	
	function admin_scripts_styles() {
		wp_enqueue_script('uci-curl-admin',plugins_url('/js/admin.js',__FILE__),array('jquery'),$this->version);
		
		wp_enqueue_style('uci-curl-admin',plugins_url('/css/admin.css',__FILE__),array(),$this->version);
	}
	
	function display_admin_page() {
		$html=null;

		if (isset($_POST['get-race-data']) && $_POST['get-race-data']='yes') :
			$results=$this->get_race_data();
		endif;
				
		$html.='<div class="uci-curl">';
			$html.='<h3>cURL</h3>';
		
			$html.='<form method="POST">';
				$html.='<button type="submit" name="get-race-data" id="get-race-data" value="yes">Get Data</button>';
			$html.='</form>';
		
			if (isset($results)) :
				foreach ($results as $result) :
					$html.=$result;
				endforeach;
			endif;
		$html.='</div>';
		
		$html.='<div class="modal></div>';
		
		echo $html;	
	}
	
	function get_race_data() {
		$races=array();
		$races_class_name="datatable";
		$races_obj=new stdClass();
		$arr=array();
		$timeout = 5;
				
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		//curl_setopt($ch,CURLOPT_HEADER,false); //
		
		$html = curl_exec($ch);
		//$html=utf8_encode($html);
		
		curl_close($ch);
		
		// Create a DOM parser object
		$dom = new DOMDocument();
		
		// Parse HTML - The @ before the method call suppresses any warnings that loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);
		
		//discard white space 
		$dom->preserveWhiteSpace = false; 
		
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
						$date=$col->nodeValue;
						$races[$row_count]->date=$date;
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
				$races[$row_count]->results=$this->get_race_results($races[$row_count]->link); // run our curl result page stuff //
			endif;
			$row_count++;		
		endforeach;
		
		foreach ($races as $key => $value) :
			$races_obj->$key=$value;
		endforeach;

		foreach ($races_obj as $race) :
			$arr[]=$this->add_race_to_db($race);
		endforeach;
		
		return $arr;
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
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$html = curl_exec($ch);
		curl_close($ch);
		
		// Create a DOM parser object
		$dom = new DOMDocument();
		
		// Parse HTML - The @ before the method call suppresses any warnings that loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);
		
		//discard white space 
		$dom->preserveWhiteSpace = false; 
		
		// get our results table //
		$race_results=array();
		$race_results_obj=new stdClass();
		$results_class_name="datatable";
		
		$finder = new DomXPath($dom);
		
		$nodes = $finder->query("//*[contains(@class, '$results_class_name')]");
		
		$rows=$nodes->item(0)->getElementsByTagName('tr'); //get all rows from the table
		
		// loop over the table rows
		$row_count=0;
		foreach ($rows as $row) : 
		  if ($row_count!=0) :
		  	$race_results[$row_count]=new stdClass();
		  	$cols = $row->getElementsByTagName('td'); 	// get each column by tag name
			  foreach ($cols as $key => $col) :
					// rank, name, nat, age, result, par, pcr
					if ($key==0) {
						$race_results[$row_count]->place=$col->nodeValue;
					} else if ($key==1) {
						$race_results[$row_count]->name=$col->nodeValue;
					} else if ($key==2) {
						$race_results[$row_count]->nat=$col->nodeValue;
					} else if ($key==3) {
						$race_results[$row_count]->age=$col->nodeValue;
					} else if ($key==4) {
						$race_results[$row_count]->result=$col->nodeValue;
					} else if ($key==5) {
						$race_results[$row_count]->par=$col->nodeValue;
					}	else if ($key==6) {
						$race_results[$row_count]->pcr=$col->nodeValue;
					}
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
			'data' => serialize($race_data),
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
	//----------------------------- end add_race_to_db helper functions -----------------------------//

	function display_view_db_page() {
		global $wpdb;
		$html=null;
		
		$html.='<h3>View DB</h3>';
		
		$races=$wpdb->get_results("SELECT * FROM ".$this->table);
		
		foreach ($races as $race) :
			$html.=$this->display_race_table($race);
		endforeach;
		
		echo $html;
	}
	
	function display_race_table($race) {
		$html=null;
		$data=unserialize($race->data);

/*
echo '<pre>';
print_r($race);
//print_r($data);
echo '</pre>';
*/	
	
		if (!isset($data->results)) :
			$html.=$race->id.'<br />';
			$html.=$race->code.'<br />';
			$html.='This has no results<br>';
		endif;
/*
if ($race->id==188) :
echo '188<br>';
print_r($data);
endif;	
*/
/*
		$html.=$data->date.'<br />';
		$html.=$data->event.'<br />';
		$html.=$data->nat.'<br />';
		$html.=$data->class.'<br />';						
		$html.=$data->winner.'<br />';
		$html.=$data->link.'<br />';
*/
		
/*
		$html.='<table class="resultdata">';
			if (isset($data->results)) :
				foreach ($data->results as $result) :
					$html.='<tr>';
						$html.='<td>'.$result->place.'</td>';
						$html.='<td>'.$result->name.'</td>';
						$html.='<td>'.$result->nat.'</td>';
						$html.='<td>'.$result->age.'</td>';
						$html.='<td>'.$result->result.'</td>';
						$html.='<td>'.$result->par.'</td>';
						$html.='<td>'.$result->pcr.'</td>';
					$html.='</tr>';
				endforeach;
			else :
				$html.=$race->id.' - This race had no results<br />';				
			endif;
		$html.='</table>';
*/
		
		//$html.='<hr />';
		
		return $html;
	}
}

$uci_curl=new Top25_cURL();

?>