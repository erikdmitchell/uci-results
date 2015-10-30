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
			<?php fc_login_form_fields(); ?>
		</div><!-- .login -->

		<div class="col-md-6 register">
			<h3>Register</h3>
			<?php fc_registration_form_fields(); ?>
		</div><!-- .register -->
	</div><!-- .row -->
</div><!-- .container -->
<?php get_footer(); ?>