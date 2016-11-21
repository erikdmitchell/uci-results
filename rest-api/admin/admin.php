<?php
class UCIResultsAdmin {
	
	public function __construct() {
		add_action('admin_menu', array($this, 'register_menu_page'));
	}

	public function register_menu_page() {
	    add_menu_page(
	        __('UCI Results 2', 'uci-results'),
	        'uci-results',
	        'manage_options',
	        'uci-results-api',
	        array($this, 'admin_page'),
	        '',
	        80
	    );
	}
	
	public function admin_page() {
		echo 'admin page';
	}
}

new UCIResultsAdmin();
?>