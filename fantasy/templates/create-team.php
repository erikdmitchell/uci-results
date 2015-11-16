<?php
/**
 * The template for the create team page.
 *
 * @since Version 0.0.1
 */
?>
<div class="fantasy-cycling-create-team">
	<?php if (isset($_GET['race_id']) && $_GET['race_id']!=0) : ?>
		<form name="new-team" method="post" action="">
			<?php
			if (isset($_GET['team'])) :
				$team='<input type="text" name="team_name" id="team-name" class="longtext" value="'.$_GET['team'].'" maxlength="250" readonly />';
			else :
				$team='<input type="text" name="team_name" id="team-name" class="longtext" value="" maxlength="250" />';
			endif;

			$dd_list=fc_rider_list_dropdown_race(array(
				'id' => $_GET['race_id'],
				'name' => 'riders[]',
				'echo' => false
			));
			$race=fc_get_race($_GET['race_id']);
			?>
			<div class="row">
				<div class="col-md-2">
					<label for="race">Race:</label>
				</div>
				<div class="col-md-6">
					<span class="race-name"><?php echo $race->name; ?></div>
					<input type="hidden" name="race" value="<?php echo $_GET['race_id']; ?>" />
				</div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<label for="team-name">Team Name:</label>
				</div>
				<div class="col-md-6">
					<?php echo $team; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<label for="team-name">Rider 1:</label>
				</div>
				<div class="col-md-6">
					<?php echo $dd_list; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<label for="team-name">Rider 2:</label>
				</div>
				<div class="col-md-6">
					<?php echo $dd_list; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<label for="team-name">Rider 3:</label>
				</div>
				<div class="col-md-6">
					<?php echo $dd_list; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<label for="team-name">Rider 4:</label>
				</div>
				<div class="col-md-6">
					<?php echo $dd_list; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<label for="team-name">Rider 5:</label>
				</div>
				<div class="col-md-6">
					<?php echo $dd_list; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2">
					<label for="team-name">Rider 6:</label>
				</div>
				<div class="col-md-6">
					<?php echo $dd_list; ?>
				</div>
			</div>

			<input type="submit" name="submit" id="submit" value="Create Team" />
			<input type="hidden" name="wp_user_id" value="<?php echo get_current_user_id(); ?>" />
			<input type="hidden" name="create_team" value="1" />
		</form>
	<?php else : ?>
		There was an error, please try again.
	<?php endif; ?>
</div>