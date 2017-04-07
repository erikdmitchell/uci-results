<?php
function procress_road_race_results($meta_value, $race_id, $result, $rider_id) {
	$meta_value['place']=$meta_value['rank'];
	
	unset($meta_value['rank']);

	return $meta_value;
}
add_filter('uci_results_insert_race_result_road', 'procress_road_race_results', 10, 4);
//$meta_value=apply_filters('uci_results_insert_race_result', $meta_value, $race_id, $result, $rider_id);			
?>