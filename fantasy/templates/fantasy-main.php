<?php
/**
 * The template for our main fantasy page.
 *
 * @since Version 0.0.1
 */
?>

<div class="row">
	<div class="col-md-3">
		<?php if (is_user_logged_in()) : ?>
			<h3>My Teams</h3>
			<?php fc_user_teams(get_current_user_id()); ?>
		<?php else : ?>
			<div class="login-register-wrap">
				<h3>Login</h3>
				<?php wp_login_form(); ?>
				<ul class="login-register">
					<li><a href="/register/">Register</a></li>
					<li><a href="/lostpassword/">Lost Password?</a></li>
				</ul>
			</div>
		<?php endif; ?>
	</div>

	<div class="team-standings-wrap col-md-4">
		<h3>Final Standings</h3>
		<?php fc_final_standings(); ?>
	</div>

	<div class="col-md-5 next-race">
		<h3>Upcoming Races</h3>
		<?php fc_upcoming_races(); ?>
	</div>
</div>