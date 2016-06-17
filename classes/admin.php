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



		$this->setup_config($config);
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

		$this->config=json_decode(json_encode($config), FALSE); // convert to object and store
	}

}

$ucicurl_admin=new UCIcURLAdmin();
?>