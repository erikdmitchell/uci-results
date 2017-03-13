<?php
//The Following registers an api route with multiple parameters. 

 
function uci_custom_api(){
    register_rest_route('uci/v1', '/races', array(
        'methods' => 'GET',
        'callback' => 'uci_api_get_races',
    ));
}
add_action('rest_api_init', 'uci_custom_api');

//Customize the callback to your liking
function uci_api_get_races($data) {
	$races=new WP_Query(array(
		'posts_per_page' => 15,
		'post_type' => 'races',
		//'paged' => get_query_var('paged'),
	));

	return $races->posts;
}	
?>