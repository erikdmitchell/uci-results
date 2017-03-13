<?php
//The Following registers an api route with multiple parameters. 

 
function uci_custom_api(){
    register_rest_route('uci/v1', '/races', array(
        'methods' => 'GET',
        'callback' => 'uci_api_get_races',
    ));

    register_rest_route('uci/v1', '/riders', array(
        'methods' => 'GET',
        'callback' => 'uci_api_get_riders',
    ));
}
add_action('rest_api_init', 'uci_custom_api');

function uci_api_get_races($args) {
	$races=new WP_Query(array(
		'posts_per_page' => 15,
		'post_type' => 'races',
		//'paged' => get_query_var('paged'),
	));

	return $races->posts;
}

function uci_api_get_riders($args) {
	$riders=new WP_Query(array(
		'posts_per_page' => 15,
		'post_type' => 'riders',
		//'paged' => get_query_var('paged'),
	));

	return $riders->posts;
}
?>