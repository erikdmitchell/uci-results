<?php
/**
 * Calls the class on the post edit screen.
 */
function call_UCIResultsResultsMetabox() {
    new UCIResultsResultsMetabox();
}
 
if (is_admin()) :
	add_action('load-post.php', 'call_UCIResultsResultsMetabox');
	add_action('load-post-new.php', 'call_UCIResultsResultsMetabox');
endif;
 
/**
 * The Class.
 */
class UCIResultsResultsMetabox {
 
    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save'));
    }
 
    /**
     * Adds the meta box container.
     */
    public function add_meta_box( $post_type ) {
        // Limit meta box to certain post types.
        $post_types = array('races');
 
        if ( in_array( $post_type, $post_types ) ) {
            add_meta_box(
                'results_details',
                __( 'Race Results', 'uci-results' ),
                array($this, 'render_meta_box_content'),
                $post_type,
                'normal',
                'default'
            );
        }
    }
 
    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save( $post_id ) {
        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */
        // Check if our nonce is set.
        if (!isset($_POST['uci_results_admin']))
            return $post_id;
 
        $nonce = $_POST['uci_results_admin'];
 
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'update_riders_twitter_meta' ) ) {
            return $post_id;
        }
 
        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
 
        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
 
        /* OK, it's safe for us to save the data now. */
        // Sanitize the user input.
        //$mydata=sanitize_text_field($_POST['twitter_name']);
 
 // key = _rider_ID
 
        // Update the meta field.
        //update_post_meta($post_id, '_rider_twitter', $mydata);
    }
 
 
    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content($post) {
        // Add an nonce field so we can check for it later.
        wp_nonce_field('update_riders_twitter_meta', 'uci_results_admin');
 
        // Use get_post_meta to retrieve an existing value from the database.

 
        // Display the form, using the current value.
        ?>
        
        <table>
	        <thead>
		        <tr>
			        <th>Race ID</th>
			        <th>Place</th>
			        <th>Name</th>
			        <th>Nat</th>
			        <th>Age</th>
			        <th>Result</th>
			        <th>Par</th>
			        <th>Pacr</th>
			        <th>Rider ID</th>
		        </tr>
	        </thead>
	        <tbody>
	        </tbody>
        </table>      
	        

        <?php
    }
}
?>