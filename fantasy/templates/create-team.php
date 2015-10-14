<?php
/**
 * The template for the create team page.
 *
 * @since Version 0.0.1
 */
?>
<?php get_header(); ?>

<?php
if (isset($_POST['create_team']) && $_POST['create_team']) :
	fc_process_create_team($_POST);
endif;
?>

<div class="fantasy-cycling-create-team">
	<h3>Create Team</h3>
	<form name="new-team" method="post" action="">
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Team Name:</label>
			</div>
			<div class="col-md-6">
				<input type="text" name="team_name" id="team-name" class="longtext" value="" maxlength="250" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Top 10 Rider:</label>
			</div>
			<div class="col-md-6">
				<?php echo fc_rider_list_dropdown('riders[]'); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Top 10 Rider:</label>
			</div>
			<div class="col-md-6">
				<?php echo fc_rider_list_dropdown('riders[]'); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Top 20 Rider:</label>
			</div>
			<div class="col-md-6">
				<?php echo fc_rider_list_dropdown('riders[]',10,10); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Top 20 Rider:</label>
			</div>
			<div class="col-md-6">
				<?php echo fc_rider_list_dropdown('riders[]',10,10); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Top 30 Rider:</label>
			</div>
			<div class="col-md-6">
				<?php echo fc_rider_list_dropdown('riders[]',20,10); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Rider (Not Top 10):</label>
			</div>
			<div class="col-md-6">
				<?php echo fc_rider_list_dropdown('riders[]',10,500); ?>
			</div>
		</div>

		<input type="submit" name="submit" id="submit" value="Create Team" />
		<input type="hidden" name="wp_user_id" value="<?php echo get_current_user_id(); ?>" />
		<input type="hidden" name="create_team" value="1" />
	</form>
</div>