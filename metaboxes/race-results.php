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
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content($post) {
        // Add an nonce field so we can check for it later.
        wp_nonce_field('update_race_results_meta', 'uci_results_admin');
 
		$riders=uci_results_get_race_results($post->ID);
		$discipline=strtolower(uci_get_first_term($post->ID, 'discipline'));
		$rider_output=array('place', 'name', 'nat', 'age', 'result', 'par', 'pcr');
	
		// FILTERS ??? //
		$rider_output=apply_filters('race_results_metabox_rider_output_'.$discipline, $rider_output, $post->ID);
        ?>
        
        <p>
        	<a href="<?php echo admin_url('admin.php?page=uci-results&subpage=results&action=add-csv&race_id='.$post->ID); ?>" class="button button-secondary">Add Results</a>
        </p>
        
        <table class="uci-results-race-results widefat fixed striped">
	        <thead>
		       <tr>
			       <?php foreach ($rider_output as $slug) : ?>
				        <th class="<?php echo $slug; ?>"><?php echo ucwords($slug); ?></th>
			        <?php endforeach; ?>
		       </tr>
	        </thead>
	        <tbody>
		        <?php foreach ($riders as $rider) : ?>
		        	<tr>
			        	<?php foreach ($rider_output as $slug) : ?>
				        	<td class="<?php echo $slug; ?>"><?php echo $rider[$slug]; ?></td>
			        	<?php endforeach; ?>
		        	</tr>
		        <?php endforeach; ?>
	        </tbody>
        </table>

        <?php
    }
    
}
?>