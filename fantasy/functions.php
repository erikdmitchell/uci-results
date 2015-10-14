<?php
function fc_output_buffer() {
	ob_start();
}
add_action('init','fc_output_buffer');

function fc_login_protect_page() {
	if (!is_user_logged_in()) :
		wp_redirect('/login/');
		exit;
	endif;
}

function fc_fantasy_page_redirect() {
	if (!is_user_logged_in())
		return false;

	wp_redirect('/fantasy/');
	exit;
}
?>