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
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content($post) {
	    $prefix='race';
	    
        // Add an nonce field so we can check for it later. //
        wp_nonce_field('update_related_races', 'uci_results_admin_related_races');
		add_thickbox();
		
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
				<a id="add-related-race" href="#" data-id="<?php echo $post->ID; ?>"><span class="dashicons dashicons-plus-alt"></span></a>
			</div>
        </div>
        
        <?php
    }
    
}

?>