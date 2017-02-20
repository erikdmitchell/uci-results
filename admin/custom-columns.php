<?php
/**
 * set_custom_edit_riders_columns function.
 * 
 * @access public
 * @param mixed $columns
 * @return void
 */
function set_custom_edit_riders_columns($columns) {
	unset($columns['date']);
	
    $columns['country'] = __('Country', 'uci-results');
    $columns['twitter'] = __('Twitter', 'uci-results');
    $columns['date'] = __('Date', 'uci-results');

    return $columns;
}
add_filter('manage_riders_posts_columns', 'set_custom_edit_riders_columns');

/**
 * custom_riders_columns function.
 * 
 * @access public
 * @param mixed $column
 * @param mixed $post_id
 * @return void
 */
function custom_riders_columns($column, $post_id) {
    switch ($column) :
        case 'country' :       
        	$terms=get_the_term_list($post_id, 'country', '', ',', '');        	

            if (is_string($terms)) :
                echo $terms;
            else :
                _e('Unable to get country', 'uci-results');
            endif;
            break;

        case 'twitter' :
            echo get_post_meta($post_id , '_rider_twitter', true); 
            break;
    endswitch;
}
add_action('manage_riders_posts_custom_column' , 'custom_riders_columns', 10, 2);

/**
 * set_custom_edit_races_columns function.
 * 
 * @access public
 * @param mixed $columns
 * @return void
 */
function set_custom_edit_races_columns($columns) {
	unset($columns['date']);
	
	$columns['race_date'] = __('Date', 'uci-results');
    $columns['country'] = __('Country', 'uci-results');
    $columns['class'] = __('Class', 'uci-results');
    $columns['season'] = __('Season', 'uci-results');
    $columns['series'] = __('Series', 'uci-results');

    return $columns;
}
add_filter('manage_races_posts_columns', 'set_custom_edit_races_columns');

/**
 * custom_races_columns function.
 * 
 * @access public
 * @param mixed $column
 * @param mixed $post_id
 * @return void
 */
function custom_races_columns($column, $post_id) {
    switch ($column) :
        case 'country' :       
        	$terms=get_the_term_list($post_id, 'country', '', ',', '');        	

            if (is_string($terms)) :
                echo $terms;
            else :
                _e('Unable to get country', 'uci-results');
            endif;
            break;
        case 'class' :       
        	$terms=get_the_term_list($post_id, 'race_class', '', ',', '');        	

            if (is_string($terms)) :
                echo $terms;
            else :
                _e('Unable to get class', 'uci-results');
            endif;
            break;
        case 'season' :       
        	$terms=get_the_term_list($post_id, 'season', '', ',', '');        	

            if (is_string($terms)) :
                echo $terms;
            else :
                _e('Unable to get season', 'uci-results');
            endif;
            break;
        case 'series' :       
        	$terms=get_the_term_list($post_id, 'series', '', ',', '');        	

            if (is_string($terms)) :
                echo $terms;
            else :
                _e('', 'uci-results');
            endif;
            break;                        
        case 'race_date' :	        
            echo get_post_meta($post_id , '_race_date', true); 
            break;
    endswitch;
}
add_action('manage_races_posts_custom_column' , 'custom_races_columns', 10, 2);
?>