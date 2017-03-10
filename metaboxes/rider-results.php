<?php
/**
 * Calls the class on the post edit screen.
 */
function call_UCIResultsRiderResultsMetabox() {
    new UCIResultsRiderResultsMetabox();
}
 
if (is_admin()) :
	add_action('load-post.php', 'call_UCIResultsRiderResultsMetabox');
	add_action('load-post-new.php', 'call_UCIResultsRiderResultsMetabox');
endif;
 
/**
 * The Class.
 */
class UCIResultsRiderResultsMetabox {
 
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
        $post_types = array('riders');
 
        if ( in_array( $post_type, $post_types ) ) {
            add_meta_box(
                'riders_results',
                __('Rider Results', 'uci-results'),
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
		$results=uci_results_get_rider_results(array('rider_id' => $post->ID));
        ?>
        
        <table class="uci-results-rider-results widefat fixed striped">
	        <thead>
		        <tr>
			        <th class="place">Place</th>
			        <th class="race">Race</th>
			        <th class="age">Age</th>
			        <th class="result">Result</th>
			        <th class="par">Par</th>
			        <th class="pcr">Pacr</th>
		        </tr>
	        </thead>
	        <tbody>
		        <?php foreach ($results as $result) : ?>
		        	<tr id="rider-">
			        	<td class="place"><?php echo $result['place']; ?></td>
			        	<td class="name"><?php echo $result['race_name']; ?></td>
			        	<td class="age"><?php echo $result['age']; ?></td>
			        	<td class="result"><?php echo $result['result']; ?></td>
			        	<td class="par"><?php echo $result['par']; ?></td>
			        	<td class="pcr"><?php echo $result['pcr']; ?></td> 
		        	</tr>
		        <?php endforeach; ?>
	        </tbody>
        </table>

        <?php
    }
}
?>