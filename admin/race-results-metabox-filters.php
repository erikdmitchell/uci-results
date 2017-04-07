<?php
function race_results_metabox_road($output, $post_id) {
	$output=array('place', 'name', 'nat', 'age', 'result');
		
	return $output;
}
add_filter('race_results_metabox_rider_output_road', 'race_results_metabox_road', 10, 2);
//$rider_output=apply_filters('race_results_metabox_rider_output_'.$discipline, $rider_output, $post->ID);	
?>