<?php
/**
 * uci_scripts_styles function.
 *
 * @access public
 * @return void
 */
function uci_scripts_styles() {
	global $uci_results_pages;

	// include on search page //
	if (is_page($uci_results_pages['search'])) :
		wp_enqueue_script('uci-results-search-script', UCI_RESULTS_URL.'/js/search.js', array('jquery'), '0.1.0');

		wp_localize_script('uci-results-search-script', 'searchAJAXObject', array('ajax_url' => admin_url('admin-ajax.php')));
	endif;
	
	wp_enqueue_script('uci-results-front-end', UCI_RESULTS_URL.'/js/front-end.js', array('jquery'), '0.1.0', true);

	wp_enqueue_style('uci-results-fa-style', UCI_RESULTS_URL.'css/font-awesome.min.css');
	wp_enqueue_style('uci-results-style', UCI_RESULTS_URL.'/css/main.css');
	wp_enqueue_style('uci-results-grid', UCI_RESULTS_URL.'/css/em-bs-grid.css');
}
add_action('wp_enqueue_scripts', 'uci_scripts_styles');

/**
 * uci_get_first_term function.
 * 
 * @access public
 * @param int $post_id (default: 0)
 * @param string $taxonomy (default: '')
 * @return void
 */
function uci_get_first_term($post_id=0, $taxonomy='') {
	$terms=wp_get_post_terms($post_id, $taxonomy, array('fields' => 'names'));
	
	if (is_wp_error($terms))
		return false;
		
	if (isset($terms[0]))
		return $terms[0];
		
	return false;
}

/**
 * uci_get_country_dropdown function.
 * 
 * @access public
 * @param string $name (default: 'country')
 * @param string $selected (default: '')
 * @return void
 */
function uci_get_country_dropdown($name='country', $selected='') {
	$html=null;
	$countries=get_terms( array(
	    'taxonomy' => 'country',
		'hide_empty' => false,
	));

	$html.='<select id="'.$name.'" name="'.$name.'" class="'.$name.'">';
		$html.='<option value="0">-- Select Country --</option>';
			foreach ($countries as $country) :
				$html.='<option value="'.$country->slug.'" '.selected($selected, $country->slug, false).'>'.$country->name.'</option>';
			endforeach;
	$html.='</select>';
	
	return $html;
}

/**
 * uci_results_get_rider_rank function.
 * 
 * @access public
 * @param int $rider_id (default: 0)
 * @param string $season (default: '')
 * @param string $week (default: '')
 * @return void
 */
function uci_results_get_rider_rank($rider_id=0, $season='', $week='') {
	global $wpdb;

	$rank=$wpdb->get_var("SELECT rank FROM $wpdb->uci_results_rider_rankings WHERE rider_id=$rider_id AND season='$season' AND week=$week");

	if (!$rank)
		$rank=0;

	return $rank;
}

/**
 * uci_results_country_url function.
 *
 * @access public
 * @param string $slug (default: '')
 * @return void
 */
function uci_results_country_url($slug='') {
	global $uci_results_pages;

	$base_url=get_permalink($uci_results_pages['country']);
	$url=$base_url.$slug;

	echo $url;
}

/**
 * uci_pagination function.
 * 
 * @access public
 * @param string $numpages (default: '')
 * @param string $pagerange (default: '')
 * @param string $paged (default: '')
 * @return void
 */
function uci_pagination($numpages='', $pagerange='', $paged='') {
	global $paged;
	
	$html=null;
	
	if (empty($pagerange))
		$pagerange = 2;

	if (empty($paged))
		$paged = 1;
	
	if ($numpages == '') :
		global $wp_query;
		
		$numpages = $wp_query->max_num_pages;
		
		if (!$numpages) :
			$numpages = 1;
		endif;
	endif;

	$pagination_args = array(
		'base'            => get_pagenum_link(1) . '%_%',
		'format'          => '?paged=%#%',
		'total'           => $numpages,
		'current'         => $paged,
		'mid_size'        => $pagerange,
		'prev_text'       => __('&laquo;'),
		'next_text'       => __('&raquo;'),
	);
	
	$paginate_links = paginate_links($pagination_args);
	
	if ($paginate_links) :
		$html.='<nav class="uci-pagination">';
			//$html.="<span class='page-numbers page-num'>Page " . $paged . " of " . $numpages . "</span> ";
			$html.=$paginate_links;
		$html.="</nav>";
	endif;
	
	echo $html;
}

function uci_results_uci_rankings_url($discipline='road', $date='') {
	global $uci_results_pages;

	$url=get_permalink($uci_results_pages['uci_rankings']).strtolower($discipline).'/'.$date;

	echo $url;
}
?>