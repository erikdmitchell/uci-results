<?php global $fantasy_cycling_api; ?>

<div class="uci-results">
	<h2>UCI RR</h2>

<?php
	
$ucirr=new UCIRR();
$ucirr->run();

?>

</div>

<?php

class UCIRR {
	
	protected $base_url='http://www.uci.infostradasports.com';
	
	public function __construct() {
		include_once(UCI_RESULTS_PATH.'admin/simple_html_dom.php');		
	}
	
	function run() {
		// url is current road url (all) //
		$url='http://www.uci.infostradasports.com/asp/lib/TheASP.asp?PageID=19004&TaalCode=2&StyleID=0&SportID=102&CompetitionID=-1&EditionID=-1&EventID=-1&GenderID=1&ClassID=1&EventPhaseID=0&Phase1ID=0&Phase2ID=0&CompetitionCodeInv=1&PhaseStatusCode=262280&DerivedEventPhaseID=-1&SeasonID=492&StartDateSort=20161022&EndDateSort=20171024&Detail=1&DerivedCompetitionID=-1&S00=-3&S01=2&S02=1&PageNr0=-1&Cache=8';
		$html=file_get_html($url);
		$headers=array();
		$races=array();
		$counter=0;
		
		// table headers //
		foreach ($html->find('td.caption') as $td) :
			$headers[]=sanitize_key($td->plaintext);
		endforeach;
		
		// get our race data //
		foreach ($html->find('table.datatable tr') as $tr) :
			$counter++;
			$arr=array();
			
			if ($counter==1) :
				continue;
			endif;
			
			foreach ($tr->find('td') as $key => $td) :	
				if ($headers[$key]=='date') :
					$date=$this->get_date_details(str_replace('&nbsp;', ' ', $td->plaintext));
					$arr=array_merge($arr, $date);
				elseif ($headers[$key]=='event') :
					
					foreach ($td->find('a') as $a) :
						$arr['url']=$this->base_url.$a->href;
					endforeach;
					
					$arr[$headers[$key]]=str_replace('&nbsp;', ' ', $td->plaintext);
				else :
					$arr[$headers[$key]]=str_replace('&nbsp;', ' ', $td->plaintext);
				endif;
			endforeach;
			$races[]=$arr;
		endforeach;
		
		// cycle through links and see if it's reults or not -- this may also be easily done my our single marker //
		
echo '<pre>';
print_r($races);
echo '</pre>';		
	}
	
	function get_date_details($date='') {
		$date_arr=array();
		$date=explode('-', $date);

		if (count($date)==1) :
			$date_arr['start']=$date[0];
			$date_arr['end']=$date[0];
			$date_arr['single']=1;
		else :
			$date_details=explode(' ' , $date[1]);
			
			$date_arr['start']=$date[0].' '.$date_details[2];
			$date_arr['end']=$date[1];
			$date_arr['single']=0;		
		endif;
		
		return $date_arr;		
	}
	
	public function startlist($url='') {
		include_once(UCI_RESULTS_PATH.'admin/simple_html_dom.php');
		// sample is http://www.procyclingstats.com/race/Tour_de_Suisse_2017_Startlist

		// load page //
		$html = file_get_html($url);
		
		// get riders list //
		$startlist_riders=array();

		foreach($html->find('a[class=rider blue]') as $rider) :
			$arr=array();
			
			// rider url //
			$arr['url']=$rider->href;
			
			// get last name //
			foreach ($rider->find('span') as $span) :
				$arr['last']=$span->innertext;
			endforeach;
			
			// remove last from full for first //
			$arr['first']=trim(str_replace('<span>'.$arr['last'].'</span>', '', $rider->innertext));
			
			$startlist_riders[]=$arr;
		endforeach;

		return $startlist_riders;
	}

/*
function rider($rider_url='') {
	$url_overview=$this->base_url.$rider_url;
	$url_statistics=$this->base_url.$rider_url.'&c=3';	


    
    rider_main_page = requests.get(url_overview)
    rider_main_bs = \
        bs(rider_main_page.content.decode('utf-8', 'ignore'), 'html.parser')
        
    rider_statistics_page = requests.get(url_statistics)
    rider_statistics_bs = \
        bs(rider_statistics_page.content.decode('utf-8', 'ignore'), 'html.parser')

	
}
*/

function rider($rider_info='') {
	$url_overview=$this->base_url.$rider_info['url'];
	$rider_html=file_get_html($url_overview);
	$rider=new stdClass();
	
	// rider name //
	$rider->first=$rider_info['first'];
	$rider->last=$rider_info['last'];
	
	// rider pcs link //
	$rider->pcs_link=$url_overview;
	
	// rider personal data //
	foreach ($rider_html->find('b') as $b) :	
		if (strpos($b->innertext, 'Date of birth') !== false) :
			$personal_data_wrap=$b->parent();
			break;
		endif;
    endforeach;
    
    // convert to array [0] = dob and age, [1] = nationality, [2] = weight, [3] = height //
    $personal_data_arr=explode('<span>', $personal_data_wrap->innertext);
	
	// dob //
	$rider->dob='';
	
	// age //
	preg_match("/^.*?\([^\d]*(\d+)[^\d]*\).*$/", $personal_data_arr[0], $matches);
	$rider->age=$matches[1];
	
	// nat //
	$nat=explode(':', $personal_data_arr[1]);
	$rider->nat=trim(strip_tags($nat[1]));
		
	// weight //
	$weight=explode(':', $personal_data_arr[2]);
	$weight=str_replace('kg', '', $weight[1]);
	$rider->weight=preg_replace("/\s|&nbsp;/", '', $weight);
		
	// height //
	$height=explode(':', $personal_data_arr[3]);
	$height=str_replace('m', '', $height[1]);
	$height=strip_tags($height);
	$rider->height=preg_replace("/\s|&nbsp;/", '', $height);

	// team //
	foreach ($rider_html->find('b') as $b) :	
		if ($b->plaintext==$this->year) :
			$rider->team=strip_tags($b->nextSibling());
			break;
		endif;
    endforeach;

	// one day //
	foreach ($rider_html->find('div') as $e) :	
		if ($e->innertext == 'One day races') :
			$rider->one_day=strip_tags($e->previousSibling());
			break;
		endif;
    endforeach;

	// gc //
	foreach ($rider_html->find('div') as $e) :	
		if ($e->innertext == 'GC') :
			$rider->gc=strip_tags($e->previousSibling());
			break;
		endif;
    endforeach;
    
	// tt //
	foreach ($rider_html->find('div') as $e) :	
		if ($e->innertext == 'Time trial') :
			$rider->tt=strip_tags($e->previousSibling());
			break;
		endif;
    endforeach;
    
	// sprint //
	foreach ($rider_html->find('div') as $e) :	
		if ($e->innertext == 'Sprint') :
			$rider->sprint=strip_tags($e->previousSibling());
			break;
		endif;
    endforeach;
    
    $rider->stats=$this->rider_statistics($rider_info['url']);
	
	return $rider;
}

function rider_statistics($url='') {
	$url_overview=$this->base_url.$url.'&c=3';
	$rider_stats_html=file_get_html($url_overview);
	$stats=new stdClass();
	
	// name is col 1, position is col 2, value is col 3 //
	foreach ($rider_stats_html->find('tr') as $tr) :
		$key=sanitize_key($tr->children(0)->plaintext);
		$stats->$key=array(
			'position' => $tr->children(1)->plaintext,
			'value' => $tr->children(2)->plaintext,
		);
	endforeach;
	
	// running point score for last 12 months //
	$rps_arr=array();
	
	foreach ($rider_stats_html->find('a') as $e) :	
		if ($e->innertext == 'Running point score') :
			$rps_url=$e->href; // get link
			break;
		endif;
    endforeach;	

	// parse page //
	$rps_html=file_get_html($this->base_url.$rps_url);

	// get values //
	foreach ($rps_html->find('tr') as $tr) :
		$rps_arr[]=$tr->children(1)->plaintext;
	endforeach;

	$rps_arr=array_slice($rps_arr, 1, 12);
	$stats->running_points_score=array_sum($rps_arr);
	
	return $stats;
}
	
}
?>