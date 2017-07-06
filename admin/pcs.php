<?php

class PCS {
	
	protected $base_url='http://www.procyclingstats.com/';
	
	public function __construct() {
		include_once(UCI_RESULTS_ADMIN_PATH.'simple_html_dom.php');
	}
	
	public function race_results($url='') {
		$result_links=$this->race_result_urls($url);
		
		foreach ($result_links as $result_link) :
			$this->get_results($result_link['url']);
		endforeach;
	}
	
	protected function race_result_urls($url='') {
		$html=file_get_html($url);
		$result_links=array();
		
		foreach ($html->find('.content .subs a.ResultQuickNav') as $link) :
			$arr=array(
				'type' => $link->innertext,
				'url' => $this->base_url.'race.php'.$link->href,
			);
			
			$result_links[]=$arr;
		endforeach;
		
		return $result_links;		
	}
	
	protected function get_results($url='') {
		$html=file_get_html($url);
		$fields_to_ignore=array('pcs', 'km/h');
		$keys_to_skip=array();
		$headers=array();
		$race_results=array();
		
		parse_str($url, $url_arr);
echo "<p>$url</p>";		
		// get results //
		foreach ($html->find('.res'.$url_arr['id']) as $element)
			$results=$element;
			
		// get headers //
		foreach ($results->find('div b') as $key => $element) :
			if (!in_array(strtolower($element->plaintext), $fields_to_ignore)) :
				$headers[]=strtolower($element->plaintext);
			else :
				$keys_to_skip[]=$key;
			endif;
		endforeach;
print_r($keys_to_skip);		
		// get results //
		foreach ($results->find('.result .line') as $line) :
			$arr=array();
			
			foreach ($line->find('span.show') as $key => $span) :
				if (!in_array($key, $keys_to_skip))
					$arr[]=$span->plaintext;
			endforeach;
			
			$race_results[]=$arr;
		endforeach;
echo '<pre>';
print_r($headers);
print_r($race_results);
echo '</pre>';		
	}
	
}
	
?>