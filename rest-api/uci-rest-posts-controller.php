<?php
/**
 * UCI_REST_Posts_Controller
 *
 * Core class to access posts via the REST API.
 *
 * @since 
 *
 * @see UCI_REST_Controller
 */
class UCI_REST_Posts_Controller extends WP_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @since 4.7.0
	 * @access protected
	 * @var string
	 */
	protected $post_type;

	/**
	 * Instance of a post meta fields object.
	 *
	 * @since 4.7.0
	 * @access protected
	 * @var WP_REST_Post_Meta_Fields
	 */
	protected $meta;

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param string $post_type Post type.
	 */
	public function __construct( $post_type ) {
		$this->namespace=$this->rest_base.'/'.$this->version;
				
		$this->post_type = $post_type;
		$obj = get_post_type_object( $post_type );
		$this->rest_base = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;

		$this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
	}

}
?>