<?php
/**
 * Custom login/registration form.
 * This CANNOT be overwritten.
 *
 * @since 	1.1.0
 */
 // todo : check for reg, check logged in
?>
<?php get_header(); ?>
<div class="container login-register-page">
	<div class="row">
		<div class="col-md-6 login">
			<h3>Login</h3>
			<?php $login=(isset($_GET['login']) ) ? $_GET['login'] : 0; ?>
			<?php
			if ( $login === "failed" ) {
				echo '<p class="error login-msg"><strong>Error:</strong> Invalid username and/or password.</p>';
			} elseif ( $login === "empty" ) {
				echo '<p class="error login-msg"><strong>Error:</strong> Username and/or Password is empty.</p>';
			} elseif ( $login === "false" ) {
				echo '<p class="error login-msg"><strong>Error:</strong> You are logged out.</p>';
			}
			?>
			<?php wp_login_form(); ?>
		</div><!-- .login -->

		<div class="col-md-6 register">
			<h3>Register</h3>
			<?php fc_registration_form_fields(); ?>
		</div><!-- .register -->
	</div><!-- .row -->
</div><!-- .container -->






<?php get_footer(); ?>