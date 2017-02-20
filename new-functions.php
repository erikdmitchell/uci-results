<?php
function uci_results_get_rider_results($rider_id=0) {
	if (!$rider_id)
		return false;
		
	$results=array();
		
    // get race ids via meta //
	$results_args_meta = array(
		'posts_per_page' => -1,
		'post_type' => 'races',
		'meta_query' => array(
		    array(
		        'key' => '_rider_'.$rider_id,
		    )
		),
		'fields' => 'ids'
	);
	$race_ids=get_posts($results_args_meta);
	
	foreach ($race_ids as $race_id) :
		$result=get_post_meta($race_id, '_rider_'.$rider_id, true);
		$result['race_id']=$race_id;
		$result['race_name']=get_the_title($race_id);
		
		$results[]=$result;
	endforeach;

	return $results;
}
?>