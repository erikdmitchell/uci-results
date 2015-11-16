<?php
/**
 * The template for the team (roster) page.
 *
 * @since Version 0.0.1
 */
?>
<?php get_header(); ?>

<div class="fantasy-cycling-team">
	<?php
	if (!isset($_GET['team'])) :
		$team_name=false;
	else :
		$team_name=$_GET['team'];
	endif;
	?>
	<?php if ($team=fc_get_team($team_name)) : ?>
		<h3><?php echo $team_name; ?></h3>
		<div class="race-name"><?php echo $team->race_name; ?></div>
		<div class="total-points"><?php echo $team->teams[0]->total; ?></div>

		<div class="riders">
			<?php foreach ($team->teams[0]->riders as $rider) : ?>
				<div class="rider row">
					<div class="name col-md-4"><?php echo $rider->name; ?><span class="nat"><?php echo get_country_flag($rider->nat); ?></span></div>
					<div class="place col-md-1"><?php echo $rider->place; ?></div>
					<div class="points col-md-1"><?php echo $rider->points; ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<?php fc_user_teams(get_current_user_id()); ?>
	<?php endif; ?>
</div>