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
	<?php $team=fc_get_team($team_name); ?>

	<h3><?php echo $team_name; ?></h3>
	<ul class="roster">
		<?php foreach ($team->data as $rider) : ?>
			<li class="rider"><a href=""><?php echo $rider; ?></a></li>
		<?php endforeach; ?>
	</ul>
</div>