<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

global $uci_rider_stats_init;

class UCIRiderStatsInit {

	public $stats=array();

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param string $args (default: '')
	 * @return void
	 */
	public function __construct($args='') {
		add_action('uci_rider_stats_init', array($this, '_register_stats'), 100);
	}

    /**
     * register function.
     * 
     * @access public
     * @param mixed $stat
     * @return void
     */
    public function register($stat) {
		$this->stats[$stat]=new $stat();
	}
	
	/**
	 * unregister function.
	 * 
	 * @access public
	 * @param mixed $stat
	 * @return void
	 */
	public function unregister($stat) {
		unset($this->stats[$stat]);
	}
	
	/**
	 * _register_stats function.
	 * 
	 * @access public
	 * @return void
	 */
	public function _register_stats() {
		global $uci_rider_stats;
		
		$keys=array_keys($this->stats);
		$registered=array_keys($uci_rider_stats);

		foreach ($keys as $key) :
			if (in_array($this->stats[$key]->id, $registered, true)) :
				unset($this->stats[$key]);
				continue;
			endif;

			$this->stats[$key]->_register();
		endforeach;
	}    
}

$uci_rider_stats_init=new UCIRiderStatsInit();

/**
 * uci_results_register_stats function.
 * 
 * @access public
 * @param mixed $stat
 * @return void
 */
function uci_results_register_stats($stat) {
    global $uci_rider_stats_init;
 
    $uci_rider_stats_init->register($stat);
}
?>