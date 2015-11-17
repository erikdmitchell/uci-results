<?php
/**
 * The template for the create team page.
 *
 * @since Version 0.0.1
 */
?>
<div class="fantasy-cycling-create-team">
	<?php if ($race_id=fc_get_race_id()) : ?>
		<form name="new-team" id="new-team" method="post" action="">
			<?php
			if (isset($_GET['team'])) :
				$team=$_GET['team'];
			else :
				$team=$wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id=".get_current_user_id()." AND meta_key='team_name'");
			endif;

			if (!$team || $team=='') :
				echo '<a href="'.get_edit_user_link().'">Please add a team name first.</a>';
				return;
			endif;

			$dd_list=fc_rider_list_dropdown_race(array(
				'id' => $race_id,
				'name' => 'riders[]',
				'echo' => false
			));
			$race=fc_get_race($race_id);
			?>
			<div class="row">
				<div class="col-md-2">
					<label for="race">Race:</label>
				</div>
				<div class="col-md-6">
					<span class="race-name"><?php echo $race->name; ?></span>
					<input type="hidden" name="race" value="<?php echo $race_id; ?>" />
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