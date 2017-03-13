<?php
/**
 * ajax_uci_search function.
 * 
 * @access public
 * @return void
 */
function ajax_uci_search() {
	$rows=array();
	
	// set type param //
	if (isset($_POST['search_data']['types'])) :
		if (count($_POST['search_data']['types']) == 1) : // just one type
			$type=$_POST['search_data']['types'][0];
		elseif (count($_POST['search_data']['types'])) : // multiple
			$type='';
		else : // a basic fallback
			$type='';
		endif;
	else :
		$type=array('riders', 'races');
	endif;

	// build args //
	$args=array(
		'posts_per_page' => -1,
		'post_type' => $type,
		's' => $_POST['search']
	);

	// run query //
	$results=uci_search($args);
	
	foreach ($results as $result) :
		if (get_post_type($result->ID) == 'races') :
			$result=uci_search_race_details($result);
		elseif (get_post_type($result->ID) == 'riders') :
			$result=uci_search_rider_details($result);
		endif;
		
		$rows[]=uci_get_template_part('search/row', $result);
	endforeach;

	echo json_encode($rows);

	wp_die();
}
add_action('wp_ajax_uci_results_search', 'ajax_uci_search');
add_action('wp_ajax_nopriv_uci_results_search', 'ajax_uci_search');	
?>