<?php
/**
 * The template for our main fantasy page.
 *
 * @since Version 0.0.1
 */
?>

<div class="row">
	<div class="col-md-7">
		<?php tru_get_fantasy_cycling_posts(); ?>
	</div>

	<div class="col-md-5">
		<div class="row">
			<div class="col-md-12 teams login">
				<?php if (is_user_logged_in()) : ?>
<!-- 					<h3>Teams</h3> -->
					<?php //fc_user_teams(get_current_user_id()); ?>
				<?php else : ?>
					<!--
					<div class="login-register-wrap">
						<h3>Login</h3>
						<?php wp_login_form(); ?>
						<ul class="login-register">
							<li><a href="/register/">Join Now!</a></li>
							<li><a href="/lostpassword/">Lost Password?</a></li>
						</ul>
					</div>
					-->
					<a href="/register/"><button class="btn-join btn">Join Now!</button></a>
					<a href="/login/"><button class="btn-join btn">Log In</button></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 next-race">
				<h3>Upcoming Races</h3>
				<?php fc_upcoming_races(); ?>
			</div>
		</div>
		<div class="row">
			<div class="team-standings-wrap col-md-12">
				<h3>Final Standings</h3>
				<?php fc_final_standings(); ?>
			</div>
		</div>
	</div>

</div>