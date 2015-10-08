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
?>