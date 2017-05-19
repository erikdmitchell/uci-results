<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

global $uci_rider_stats;

$uci_rider_stats=array();

class UCIRiderStats {

	public $name;
	
	public $id;
	
	public $discipline;
	
	public $options;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param string $args (default: '')
	 * @return void
	 */
	public function __construct($args='') {
		$default_args=array(
			'id' => '',
			'name' => '',
			'discipline' => '',
			'options' => array(),
		);
		$args=uci_results_parse_args($args, $default_args);

		$this->id=$args['id'];
		$this->name=$args['name'];
		$this->discipline=$args['discipline'];
		$this->options=$args['options'];
	}
	
	/**
	 * get_stats function.
	 * 
	 * @access public
	 * @return void
	 */
	public function get_stats() {
		return 'stats';
	}

    /**
     * _register function.
     * 
     * @access public
     * @return void
     */
    public function _register() {
	    global $uci_rider_stats;
	    
	    $uci_rider_stats[$this->id]=$this;
    }
    
}

/**
 * uci_rider_stats_init function.
 * 
 * @access public
 * @return void
 */
function uci_rider_stats_init() {
    uci_results_register_stats('UCICrossStats');
 
    do_action('uci_rider_stats_init');
}
add_action('init', 'uci_rider_stats_init', 1);

/**
 * uci_results_register_stats function.
 * 
 * @access public
 * @param mixed $stat
 * @return void
 */
function uci_results_register_stats($stat) {
    global $uci_rider_stats_factory;
 
    $uci_rider_stats_factory->register($stat);
}
?>