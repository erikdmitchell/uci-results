<?php

/**
 * UCIResultsAdminPages class.
 */
class UCIResultsAdminPages {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts_styles'));
		add_action('admin_menu',array($this,'admin_page'));
	}

	/**
	 * admin_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_page() {
		add_menu_page('UCI cURL', 'UCI cURL', 'manage_options', 'uci-curl', array($this, 'display_admin_page'), 'dashicons-sos');
	}

	/**
	 * admin_scripts_styles function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_scripts_styles() {
		wp_enqueue_script('uci-curl-admin',UCICURL_URL.'/js/admin.js',array('jquery'), '0.1.0',true);

		wp_enqueue_style('uci-curl-admin',UCICURL_URL.'/css/admin.css',array(), '0.1.0');
	}

	/**
	 * display_admin_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function display_admin_page() {
		global $ucicurl_riders;

		$html=null;
		$tabs=array(
			'uci-curl' => 'Results',
			'races' => 'Races',
			'riders' => 'Riders',
			'rider-rankings' => 'Rider Rankings'
		);
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'uci-curl';

		$html.='<div class="wrap">';
			$html.='<h1>UCI Results</h1>';

			$html.='<h2 class="nav-tab-wrapper">';
				foreach ($tabs as $key => $name) :
					if ($active_tab==$key) :
						$class='nav-tab-active';
					else :
						$class=null;
					endif;

					$html.='<a href="?page=uci-curl&tab='.$key.'" class="nav-tab '.$class.'">'.$name.'</a>';
				endforeach;
			$html.='</h2>';

			switch ($active_tab) :
				case 'races':
					if (isset($_GET['race_id']) && isset($_GET['add_related_race'])) :
						$html.=ucicurl_get_admin_page('add-related-race');
					elseif (isset($_GET['race_id'])) :
						$html.=ucicurl_get_admin_page('single-race');
					else :
						$html.=ucicurl_get_admin_page('races');
					endif;
					break;
				case 'riders':
					if (isset($_GET['rider']) && $_GET['rider']!='') :
						$atts['rider_id']=$ucicurl_riders->get_rider_id($_GET['rider']);

						$html.=ucicurl_get_admin_page('single-rider', $atts);
					else :
						$html.=ucicurl_get_admin_page('riders');
					endif;
					break;
				case 'rider-rankings' :
						$html.=ucicurl_get_admin_page('rider-rankings');
						break;
				default:
					if (isset($_GET['action']) && $_GET['action']=='update-rankings') :
						$html.=ucicurl_get_admin_page('update-rankings');
					else :
						$html.=ucicurl_get_admin_page('curl');
					endif;

					break;
			endswitch;

		$html.='</div><!-- /.wrap -->';

		echo $html;
	}

}
?>