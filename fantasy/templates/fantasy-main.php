<?php
/**
 * The template for our main fantasy page.
 *
 * @since Version 0.0.1
 */
?>
<?php get_header(); ?>

<div class="fantasy-cycling-main">
	<?php if (isset($_GET['action']) && $_GET['action']=='create-team') : ?>
		<?php echo fc_get_create_team_page(); ?>
	<?php else: ?>
		<?php fc_user_teams(get_current_user_id()); ?>
	<?php endif; ?>
	<?php fc_team_standings(); ?>
</div>
