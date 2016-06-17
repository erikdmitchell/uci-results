<?php
/**
 * UCIcURLAdmin class.
 *
 * @since Version 2.0.0
 */
class UCIcURLAdmin {


	/**
	 * __construct function.
	 *
	 * @access public
	 * @param array $config (default: array())
	 * @return void
	 */
	public function __construct($config=array()) {




	}

	/**
 	 * @param string $url - results url
	 * the date comes in with hidden &nbsp; this gets a 'clean' date from the results page
	 * WHERE IS THIS FUNCTION USED?????
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



}

$ucicurl_admin=new UCIcURLAdmin();
?>