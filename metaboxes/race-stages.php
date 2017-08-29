<?php
/**
 * Calls the class on the post edit screen.
 */
function call_racestagesMetabox() {
    new racestagesMetabox();
}
 
if (is_admin()) :
	add_action('load-post.php', 'call_racestagesMetabox');
	add_action('load-post-new.php', 'call_racestagesMetabox');
endif;
 
/**
 * The Class.
 */
class racestagesMetabox {
 
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
	    wp_enqueue_script('flatpickr-script', UCI_RESULTS_ADMIN_URL.'js/flatpickr.min.js', array('jquery'), '2.4.8', true);
	    wp_enqueue_script('fantasy-cycling-admin-races-mb-script', UCI_RESULTS_ADMIN_URL.'js/race-stages.js', array('flatpickr-script'), '0.1.0', true);
		
		wp_enqueue_style('flatpickr-style', UCI_RESULTS_ADMIN_URL.'css/flatpickr.min.css', '', '2.4.8');
    }
 
    /**
     * Adds the meta box container.
     */
    public function add_meta_box($post_type) {
        $post_types = array('races');
 
        if (in_array($post_type, $post_types)) {
            add_meta_box(
                'race_stages',
                __('Race Stages', 'fantasy-cycling'),
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
        // Check if our nonce is set.
        if (!isset($_POST['uci_results_admin_race_stages']))
            return $post_id;

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['uci_results_admin_race_stages'], 'update_race_stages')) {
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
        $data=$this->md_array_map('sanitize_text_field', $_POST['race_stages']); // sanitize

		$post_country=wp_get_post_terms($post_id, 'country', array('fields' => 'names'));
		$post_race_class=wp_get_post_terms($post_id, 'race_class', array('fields' => 'names'));
		$post_discipline=wp_get_post_terms($post_id, 'discipline', array('fields' => 'names'));
		$post_series=wp_get_post_terms($post_id, 'series', array('fields' => 'names'));
		$post_season=wp_get_post_terms($post_id, 'season', array('fields' => 'names'));

		// prevents infinite loop //
		remove_action('save_post', array($this, 'save'));

		// build out race stage date info and create posts //
		foreach ($data['date_name'] as $key => $name) :
			$existing_post=get_page_by_title($name, OBJECT, 'races');
			
			if ($existing_post===null) :
				$this_post_id=wp_insert_post(array(
					'post_title' => $name,
					'post_content' => '',
					'post_parent' => $post_id,
					'post_type' => 'races',
					'post_status' => 'publish',
					'menu_order' => $key,
				));			
			else :
				$this_post_id=$existing_post->ID;
				wp_update_post(array(
					'ID' => $existing_post->ID,
					'post_title' => $name,
					'post_parent' => $post_id,
					'menu_order' => $key,					
				));		
			endif;

			update_post_meta($this_post_id, '_race_date_start', $data['date'][$key]);
			update_post_meta($this_post_id, '_race_date_end', $data['date'][$key]);

			wp_set_object_terms($this_post_id, $post_country, 'country', true);
			wp_set_object_terms($this_post_id, $post_race_class, 'race_class', true);
			wp_set_object_terms($this_post_id, $post_discipline, 'discipline', true);
			wp_set_object_terms($this_post_id, $post_series, 'series', true);
			wp_set_object_terms($this_post_id, $post_season, 'season', true);									
		endforeach;
		
		// re-hook this function
		add_action('save_post', array($this, 'save'));	
    }
 
	/**
	 * md_array_map function.
	 * 
	 * @access public
	 * @param mixed $function
	 * @param mixed $arr
	 * @return void
	 */
	public function md_array_map($function, $arr) {
		$result = array();
		
		foreach ($arr as $key => $val) :
			$result[$key] = (is_array($val) ? $this->md_array_map($function, $val) : $function($val));
		endforeach;

		return $result;
	}
	 
    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content($post) {
        if (!empty($post->post_parent))
        	return;
        	
        wp_nonce_field('update_race_stages', 'uci_results_admin_race_stages');
        
		$race_stages=get_posts(array(
	        'posts_per_page' => -1,
	        'post_type' => 'races',
	        'post_parent' => $post->ID,
	        'meta_key' => '_race_date_start',
            'orderby'   => 'meta_value_num',
        ));
        
        if (empty($race_stages))
        	$race_stages[]='';
        ?>
        
        <div class="fantasy-cycling-metabox" id="fc-race-stages">

	        <div class="row race-dates">
		        	        
				<?php foreach ($race_stages as $stage) : ?>
					<div class="date">
						<label for=""><input type="text" name="race_stages[date_name][]" class="stage" value="<?php echo $stage->post_title; ?>" /></label>
						<input type="text" name="race_stages[date][]" class="stage-date fc-datetimepicker" value="<?php echo get_post_meta($stage->ID, '_race_date_start', true); ?>" />
						<button class="remove-race-date" class="">- (may not work)</button>
						<a href="<?php echo get_edit_post_link($stage->ID); ?>" class="button">View Race</a>
					</div>
				<?php endforeach; ?>
				
				<button id="add-race-date" class="">+</button>
					
	        </div>

        </div>
        
        <?php
    }
}
?>