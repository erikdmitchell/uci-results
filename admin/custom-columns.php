<?php
/**
 * set_custom_edit_riders_columns function.
 * 
 * @access public
 * @param mixed $columns
 * @return void
 */
function set_custom_edit_riders_columns($columns) {
    $columns['country'] = __('Country', 'uci-results');
    $columns['twitter'] = __('Twitter', 'uci-results');

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
?>