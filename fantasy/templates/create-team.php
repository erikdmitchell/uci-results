<?php
/**
 * The template for the create team page.
 *
 * @since Version 0.0.1
 */
?>

<?php
if (isset($_POST['create_team']) && $_POST['create_team']) :
	fc_process_create_team($_POST);
endif;
?>

<div class="fantasy-cycling-create-team">
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
				<label for="team-name">Rider 1:</label>
			</div>
			<div class="col-md-6">
				<?php fc_rider_list_dropdown_race(array('name' => 'riders[]')); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Rider 2:</label>
			</div>
			<div class="col-md-6">
				<?php fc_rider_list_dropdown_race(array('name' => 'riders[]')); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Rider 3:</label>
			</div>
			<div class="col-md-6">
				<?php fc_rider_list_dropdown_race(array('name' => 'riders[]')); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Rider 4:</label>
			</div>
			<div class="col-md-6">
				<?php fc_rider_list_dropdown_race(array('name' => 'riders[]')); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Rider 5:</label>
			</div>
			<div class="col-md-6">
				<?php fc_rider_list_dropdown_race(array('name' => 'riders[]')); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<label for="team-name">Rider 6:</label>
			</div>
			<div class="col-md-6">
				<?php fc_rider_list_dropdown_race(array('name' => 'riders[]')); ?>
			</div>
		</div>

		<input type="submit" name="submit" id="submit" value="Create Team" />
		<input type="hidden" name="wp_user_id" value="<?php echo get_current_user_id(); ?>" />
		<input type="hidden" name="create_team" value="1" />
	</form>
</div>