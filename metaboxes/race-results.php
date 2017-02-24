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
	
        // Display the form, using the current value.
        ?>
        
        <table class="uci-results-race-results widefat fixed striped">
	        <thead>
		        <tr>
			        <th class="place">Place</th>
			        <th class="name">Name</th>
			        <th class="nat">Nat</th>
			        <th class="age">Age</th>
			        <th class="result">Result</th>
			        <th class="par">Par</th>
			        <th class="pcr">Pacr</th>
		        </tr>
	        </thead>
	        <tbody>
		        <?php foreach ($riders as $rider) : ?>
		        	<tr id="rider-">
			        	<td class="place"><?php echo $rider['place']; ?></td>
			        	<td class="name"><?php echo $rider['name']; ?></td>
			        	<td class="nat"><?php echo $rider['nat']; ?></td>
			        	<td class="age"><?php echo $rider['age']; ?></td>
			        	<td class="result"><?php echo $rider['result']; ?></td>
			        	<td class="par"><?php echo $rider['par']; ?></td>
			        	<td class="pcr"><?php echo $rider['pcr']; ?></td> 
		        	</tr>
		        <?php endforeach; ?>
	        </tbody>
        </table>

        <?php
    }
}
?>