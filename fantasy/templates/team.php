<?php
/**
 * The template for the team (roster) page.
 *
 * @since Version 0.0.1
 */
?>
<?php get_header(); ?>

<div class="row">
	<div class="col-md-7 fantasy-cycling-team">
		<?php if ($team=fc_get_team($_GET['team'])) : ?>
			<?php echo $team; ?>
		<?php else : ?>
			<?php fc_user_teams(get_current_user_id()); ?>
		<?php endif; ?>
	</div>

	<div class="col-md-5 next-race">
		<h3>Upcoming Races</h3>
		<?php fc_add_rosters(); ?>
	</div>
</div>