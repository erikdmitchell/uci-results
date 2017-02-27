<?php
/**
 * Calls the class on the post edit screen.
 */
function call_UCIRelatedRacesMetabox() {
    new UCIRelatedRacesMetabox();
}
 
if (is_admin()) :
	add_action('load-post.php', 'call_UCIRelatedRacesMetabox');
	add_action('load-post-new.php', 'call_UCIRelatedRacesMetabox');
endif;
 
class UCIRelatedRacesMetabox {
 
    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts_styles'));
    }
    
    /**
     * admin_scripts_styles function.
     * 
     * @access public
     * @param mixed $hook
     * @return void
     */
    public function admin_scripts_styles($hook) {	    
		wp_enqueue_script('uci-results-related-races-admin', UCI_RESULTS_ADMIN_URL.'/js/related-races.js', array('jquery'), '0.1.0', true);
    }
 
    /**
     * Adds the meta box container.
     */
    public function add_meta_box($post_type) {
        $post_types = array('races');
 
        if (in_array($post_type, $post_types)) {
            add_meta_box(
                'related_races',
                __('Related Races', 'uci-results'),
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
    public function save($post_id) {	    
        $prefix='_race_';
      
        // Check if our nonce is set.
        if (!isset($_POST['uci_results_admin_related_races']))
            return $post_id;

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['uci_results_admin_related_races'], 'update_related_races')) {
            return $post_id;
        }

        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;
 
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
 
        // OK, it's safe for us to save the data now. //

    }
 
 
    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content($post) {
	    $prefix='race';
	    
        // Add an nonce field so we can check for it later. //
        wp_nonce_field('update_related_races', 'uci_results_admin_related_races');
 
		$related_races=uci_get_related_races($post->ID);
		$related_race_id=uci_get_related_race_id($post->ID);
        ?>
        
        <div class="uci-results-metabox related-races">
	        <?php foreach ($related_races as $race) : ?>
				<div id="race-<?php echo $race->ID; ?>" class="row">
					<div class="race-name"><?php echo $race->post_title; ?></div>
					<div class="race-date"><?php echo date(get_option('date_format'), strtotime($race->race_date)); ?></div>
					<div class="action-icons"><a href="#" class="remove-related-race" data-id="<?php echo $race->ID; ?>" data-rrid="<?php echo $related_race_id; ?>"><span class="dashicons dashicons-dismiss"></span></a></div>
				</div>
			<?php endforeach; ?>
			<div class="row add-race">
				<a href="#"><span class="dashicons dashicons-plus-alt"></span></a>
			</div>
        </div>
        
        <?php
    }
    
}

?>