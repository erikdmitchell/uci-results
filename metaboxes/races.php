<?php
/**
 * Calls the class on the post edit screen.
 */
function call_UCIResultsRacesMetabox() {
    new UCIResultsRacesMetabox();
}
 
if (is_admin()) :
	add_action('load-post.php', 'call_UCIResultsRacesMetabox');
	add_action('load-post-new.php', 'call_UCIResultsRacesMetabox');
endif;
 
/**
 * The Class.
 */
class UCIResultsRacesMetabox {
 
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
		global $wp_scripts;

		// get registered script object for jquery-ui
		$ui = $wp_scripts->query('jquery-ui-core');
	    
	    wp_enqueue_script('jquery-ui-datepicker');
	    wp_enqueue_script('uci-results-admin-races-mb-script', UCI_RESULTS_URL.'js/races-metabox.js', array('jquery-ui-datepicker'));

		wp_enqueue_style('jquery-ui-smoothness', "https://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css");
    }
 
    /**
     * Adds the meta box container.
     */
    public function add_meta_box($post_type) {
        // Limit meta box to certain post types.
        $post_types = array('races');
 
        if (in_array($post_type, $post_types)) {
            add_meta_box(
                'race_details',
                __('Race Details', 'uci-results'),
                array($this, 'render_meta_box_content'),
                $post_type,
                'normal',
                'high'
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
        if (!isset($_POST['uci_results_admin_race_details']))
            return $post_id;

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['uci_results_admin_race_details'], 'update_race_details')) {
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
        $data=$_POST['race'];
        $data=array_map('sanitize_text_field', $data); // sanitize

		// append prefix to keys //
		foreach ($data as $key => $value) :
			$data[$prefix.$key]=$value;
			unset($data[$key]);
		endforeach;

        // Update the meta //
        foreach ($data as $meta_key => $meta_value) :
        	update_post_meta($post_id, $meta_key, $meta_value);
        endforeach;
    }
 
 
    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content($post) {
	    $prefix='race';
	    
        // Add an nonce field so we can check for it later. //
        wp_nonce_field('update_race_details', 'uci_results_admin_race_details');
 
		// get values in array by matching key w/ preifx //
		$meta=array();
        $post_meta=get_post_meta($post->ID);
        $default_meta=array(
	        'date' => '',
	        'winner' => '',
	        'week' => '',
	        'link' => '',
	        'related' => '',
	        'twitter' => '',
	    );
 
        foreach ($post_meta as $key => $value) :
			$exp_key=explode('_', $key);

			if ($exp_key[1] == $prefix)
				$meta[$exp_key[2]]=$value[0];
		endforeach;
		
		$meta=wp_parse_args($meta, $default_meta);

        // Display the form, using the current value.
        ?>
        
        <div class="uci-results-metabox">
	        <div class="row">
		        <label for="date"><?php _e('Date', 'uci-results'); ?></label>
				<input type="text" id="date" name="race[date]" class="uci-results-datepicker date" value="<?php echo esc_attr($meta['date']); ?>" size="25" />
	        </div>
	                
	        <div class="row">
		        <label for="winner"><?php _e('Winner', 'uci-results'); ?></label>
				<input type="text" id="winner" name="race[winner]" value="<?php echo esc_attr($meta['winner']); ?>" size="25" />
	        </div>
	        
	        <div class="row">
		        <label for="week"><?php _e('Week', 'uci-results'); ?></label>
				<input type="text" id="week" name="race[week]" class="number" value="<?php echo esc_attr($meta['week']); ?>" size="25" />
	        </div>
	        
	        <div class="row">
		        <label for="link"><?php _e('Link', 'uci-results'); ?></label>
				<input type="text" id="link" name="race[link]" class="code url" value="<?php echo esc_attr($meta['link']); ?>" size="25" />
	        </div>
	        
	        <div class="row">
		        <label for="related-races-id"><?php _e('Related Races ID', 'uci-results'); ?></label>
				<input type="text" id="related-races-id" name="race[related_races_id]" class="number" value="<?php echo esc_attr($meta['related']); ?>" size="25" />
	        </div>                                                        
	
	        <div class="row">
		        <label for="twitter"><?php _e('Twitter', 'uci-results'); ?></label>
				<input type="text" id="twitter" name="race[twitter]" value="<?php echo esc_attr($meta['twitter']); ?>" size="25" />
	        </div>
        </div>
        
        <?php
    }
}


/*
function setup_race_meta_rest_api() {
	register_api_field(
		'races',
    	'_race_date',
		array(
			'get_callback' => 'slug_get_race_meta',
			'update_callback' => null,
			'schema' => null,
		)
	);

	register_api_field(
		'races',
    	'_race_winner',
		array(
			'get_callback' => 'slug_get_race_meta',
			'update_callback' => null,
			'schema' => null,
		)
	);
	
	register_api_field(
		'races',
    	'_race_week',
		array(
			'get_callback' => 'slug_get_race_meta',
			'update_callback' => null,
			'schema' => null,
		)
	);
	
	register_api_field(
		'races',
    	'_race_link',
		array(
			'get_callback' => 'slug_get_race_meta',
			'update_callback' => null,
			'schema' => null,
		)
	);
	
	register_api_field(
		'races',
    	'_race_related_races_id',
		array(
			'get_callback' => 'slug_get_race_meta',
			'update_callback' => null,
			'schema' => null,
		)
	);			

	register_api_field(
		'races',
    	'_race_twitter',
		array(
			'get_callback' => 'slug_get_race_meta',
			'update_callback' => null,
			'schema' => null,
		)
	);
}
add_action('rest_api_init', 'setup_race_meta_rest_api');

function slug_get_race_meta($object, $field_name, $request) {
	return get_post_meta($object['id'], $field_name, true);
}
*/

?>