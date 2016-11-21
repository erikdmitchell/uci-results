<?php
class UCIResultsAdmin {
	
	public function __construct() {
		add_action('admin_menu', array($this, 'register_menu_page'));
		add_action('admin_enqueue_scripts', array($this, 'uci_results_api_admin_scripts_styles'));
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
		$html=null;
		
		$html.=uci_results_get_admin_page('rest-api-admin');
		
		echo $html;
	}

	public function uci_results_api_admin_scripts_styles() {
		wp_enqueue_style('uci-results-api-admin-styles', UCI_RESULTS_API_URL.'admin/css/admin.css');
	}
}

new UCIResultsAdmin();
?>