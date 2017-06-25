<?php

class UCIParseResults {
	
	protected $base_url='http://www.uci.infostradasports.com';
	
	protected $all_img='/images/buttons/AllPagesOn.gif';
	
	public function __construct() {
		ini_set('default_socket_timeout', 100);
		
		include_once(UCI_RESULTS_PATH.'admin/simple_html_dom.php');		
	}

	public function get_races($url='', $limit=-1) {
		if (empty($url))
			return false;

		$html=file_get_html($url);		
		$races=$this->parse_datatable($html, array('parse_date' => true, 'limit' => $limit));
		
		if (empty($races))
			return false;
			
		// get stages for non single races //
		foreach ($races as $race) :
			if (!$race->single) :
				$race->stages=$this->get_race_stages($race->url);			
			endif;		
		endforeach;
		
		return $races;
	}
	
	/**
	 * get_race_stages function.
	 * 
	 * @access protected
	 * @param string $url (default: '')
	 * @return void
	 */
	protected function get_race_stages($url='') {
		$stages=array();
		$html=file_get_html($url);
		$counter=0;
		
		foreach ($html->find('table.datatable tr') as $tr) :
			$counter++;
			$stage=new stdClass();
			
			// ignore first row //
			if ($counter==1) :
				continue;
			endif;
			
			// ignore gc //
			if ($tr->find('td', 1)->plaintext == 'General classification') :
				continue;
			endif;
			
			$stage->date=str_replace('&nbsp;', ' ', $tr->find('td', 0)->plaintext);
			$stage->name=$tr->find('td', 1)->plaintext;
			$stage->winner=$tr->find('td', 2)->plaintext;
			
			$stages[]=$stage;
		endforeach; 
		
		$html->clear();
		
		$stages=array_reverse($stages); // sets stage 1 first
		
		return $stages;
	}
	
	protected function parse_datatable($html='', $args='') {	
		$headers=array();
		$rows=array();
		$counter=0;
		$row_counter=0;
		$default_args=array(
			'limit' => -1,
			'parse_date' => false,	
		);
		$args=wp_parse_args($args, $default_args);
		
		if (empty($html))
			return 'parse datatable html empty - possibly a timeout issue';
		
		// table headers //
		foreach ($html->find('td.caption') as $td) :
			$headers[]=sanitize_key($td->plaintext);
		endforeach;
		
		// get our race data //
		foreach ($html->find('table.datatable tr') as $tr) :
			$counter++;
			$row=new stdClass();
			
			if ($counter==1) :
				continue;
			endif;
			
			foreach ($tr->find('td') as $key => $td) :
				$headers_key=$headers[$key];
				
				if ($url=$this->has_url($td)) :
					$row->url=$url;
				endif;

				$row->$headers_key=str_replace('&nbsp;', ' ', $td->plaintext);
			endforeach;

			// single check
			if ($this->is_single($row->url)) :
				$row->single=1;
			else :
				$row->single=0;
			endif;

			// clean up date if need be //
			if (isset($row->date) && $args['parse_date']) :
				$date_arr=$this->get_date_details($row->date);

				// mod for single //
				if ($row->single && ($date_arr['start'] != $date_arr['end'])) :
					$date_arr['start']=$date_arr['end'];
				endif;
									
				foreach ($date_arr as $key => $value) :
					$row->$key=$value;
				endforeach;
			endif;
								
			$rows[]=$row;
			$row_counter++;

			// bail if over limit //
			if ($row_counter==$args['limit'])
				break;
		endforeach;		
		
		return $rows;
	}
	
	protected function is_single($url='') {
		$html=file_get_html($url);

		if (($html->find('.subtitlered', 0))) :
			return true;
		else :
			return false;
		endif;
	}
	
	/**
	 * has_url function.
	 * 
	 * @access protected
	 * @param string $el (default: '')
	 * @return void
	 */
	protected function has_url($el='') {
		$url=false;
		
		foreach ($el->find('a') as $a) :
			$url=$this->base_url.$a->href;
		endforeach;
		
		if ($url && !empty($url))
			return $url;
			
		return false;
	}
	
	/**
	 * get_date_details function.
	 * 
	 * @access protected
	 * @param string $date (default: '')
	 * @return void
	 */
	protected function get_date_details($date='') {
		$date_arr=array();
		$date=explode('-', $date);

		if (count($date)==1) :
			$date_arr['start']=$date[0];
			$date_arr['end']=$date[0];
		else :
			$date_details=explode(' ' , $date[1]);
			
			$date_arr['start']=$date[0].' '.$date_details[2];
			$date_arr['end']=$date[1];		
		endif;
		
		return $date_arr;		
	}
	
	protected function get_race_results($race='') {
		$results='';
		
		if ($race->single) :
			$html=file_get_html($race->url);
			$url=$this->find_all_url($html);
			$results=$this->get_results_from_url($url);
		else :
			$html=file_get_html($race->url);
			$stages=$this->parse_datatable($html);			
			$html->clear();
			
			foreach ($stages as $key => $stage) :
				$stage->results=new stdClass();
				$stage_html=file_get_html($stage->url);
				$all_url=$this->find_all_url($stage_html);
				$stage_html->clear();
				
				$stage_all_html=file_get_html($all_url);
				
				foreach ($stage_all_html->find('div.menu_item_white_border a') as $a) :
					$results_key=sanitize_key($a->plaintext);
					$results_url=$this->base_url.$a->href;
					$results_html=file_get_html($results_url);
					$results_all_url=$this->find_all_url($results_html);
					$results_html->clear();
					
					if ($results_all_url=='')
						$results_all_url=$results_url;
					
					$results_all_html=file_get_html($results_all_url);	
					$stage->results->$results_key=$this->parse_datatable($results_all_html);
				endforeach;

				$stage_all_html->clear();
			endforeach;

			$results=$stages;
		endif;

		return $results;	
	}
	
	/**
	 * get_results_from_url function.
	 * 
	 * @access protected
	 * @param string $url (default: '')
	 * @return void
	 */
	protected function get_results_from_url($url='') {
		if (empty($url))
			return 'empty results url';
					
		$html=file_get_html($url);
		
		// get proper frame //
		foreach ($html->find('frame') as $frame) :
			if ($frame->getAttribute('name')=='content') :
				$url=$this->base_url.$frame->src;		
				
				break;
			endif;
		endforeach;
		
		$html->clear();

		if (empty($url))
			return 'empty results frame url';

		$html=file_get_html($url);
		$results=$this->parse_datatable($html);
		
		return $results;
	}
	
	/**
	 * find_all_url function.
	 * 
	 * @access protected
	 * @param string $html (default: '')
	 * @return void
	 */
	protected function find_all_url($html='') {
		$all_url='';
		
		if (empty($html))
			return 'find all url empty';
		
		foreach ($html->find('img') as $img) :
			if ($img->src==$this->all_img) :					
				$all_url=$this->base_url.$img->parent()->href;
				break;
			endif;
		endforeach;	
		
		// clean url to get all results //
		$all_url=str_replace('TheASP.asp', '/asp/lib/TheASP.asp', $all_url);
		
		return $all_url;
	}
	
}
?>