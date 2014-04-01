<?php
// Use the Curl extension to query Google and get back a page of results
$url = $_POST['url'];
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
$race_data_class="crumblepad";
$race_data_obj=new stdClass();
$race_date_class='subtitlered';

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

// get our race data //
$nodes = $finder->query("//*[contains(@class, '$race_data_class')]");
$race_data_raw=$nodes->item(0)->nodeValue;
//echo $race_data_raw;

$race_data_obj->name=null;
$race_data_obj->country=null;
$race_data_obj->rank=null;



$nodes = $finder->query("//*[contains(@class, '$race_date_class')]");
$race_date=$nodes->item(0)->nodeValue;
$race_date=explode(':',$race_date);

$race_data_obj->date=$race_date[0];
$race_data_obj->location=$race_date[1];

//print_r($race_data_obj);
?>