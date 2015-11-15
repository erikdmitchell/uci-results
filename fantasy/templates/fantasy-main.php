<?php
/**
 * The template for our main fantasy page.
 *
 * @since Version 0.0.1
 */
?>
<?php get_header(); ?>

<div class="container">
	<div class="row content">
		<div class="col-md-12">
			<?php mdw_theme_post_thumbnail('posts-full-featured'); ?>

			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header"><?php the_title( '<h1 class="entry-title">', '</h1>' );	?></header><!-- .entry-header -->

					<div class="entry-content">
						<?php	the_content( __( 'Continue reading <span class="meta-nav">&raquo;</span>', 'mdw-theme' ) ); ?>
					</div><!-- .entry-content -->

					<div class="row">
						<div class="col-md-4">
							<?php if (is_user_logged_in()) : ?>
								<?php if (isset($_GET['action']) && $_GET['action']=='create-team') : ?>
									<?php echo fc_get_create_team_page(); ?>
								<?php else: ?>
									<h3>My Teams</h3>
									<?php fc_user_teams(get_current_user_id()); ?>
								<?php endif; ?>
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
							<h3>Overall Standings</h3>
							<?php fc_team_standings(); ?>
						</div>

						<div class="col-md-4 next-race">
							<h3>Upcoming Races</h3>
							<?php fc_upcoming_races(); ?>
						</div>
					</div>

					<div class="row">


<!--
						<div class="news-notes col-md-4">
							<h3>News &amp; Notes</h3>
							<?php fc_fantasy_cycling_posts(); ?>
						</div>
-->


					</div><!-- .row -->

				</article><!-- #post-## -->
			<?php endwhile; endif; ?>

		</div>
	</div>
</div><!-- .container -->

<?php get_footer(); ?>