<?php
/**
 * The template for the create team page.
 *
 * @since Version 0.0.1
 */
?>
<?php
$max_riders=6;
?>
<div class="row">
	<div class="fantasy-cycling-create-team col-md-12">
		<?php if (isset($_GET['action']) && $_GET['action']=='teamupdated') : ?>
			<div class="updated">Your roster has been updated</div>
		<?php endif; ?>
		<?php if ($race_id=fc_get_race_id()) : ?>
			<form name="new-team" id="new-team" method="post" action="">
				<?php
				if (!is_user_logged_in()) :
					echo '<a href="/login/">Please login first.</a><br />';
					echo '<a href="/register/">Click here to register.</a><br />';
					return;
				endif;

				$team=fc_get_user_team();

				if (!$team || $team=='') :
					echo '<a href="'.get_edit_user_link().'">Please add a team name first.</a>';
					return;
				endif;

				$race=fc_get_race($race_id);
				?>

				<?php if (!fc_race_has_start_list($race_id)): ?>
					<div class="row">
						<div class="col-md-12">
							<h4>No start list yet. Check back soon.</h4>
						</div>
					</div>
				<?php endif; ?>

				<div class="row">
					<div class="col-md-2">
						<label for="race">Race:</label>
					</div>
					<div class="col-md-8 race-name">
						<?php echo $race->name; ?> (<?php echo $race->race_start; ?>)
					</div>
				</div>

				<div class="row">
					<div class="col-md-2">
						<label for="team-name">Team Name:</label>
					</div>
					<div class="col-md-8">
						<?php echo stripslashes($team); ?>
						<input type="hidden" name="team_name" value="<?php echo $team; ?>" />
					</div>
				</div>

				<div class="fc-team-roster">
					<div class="row header">
						<div class="col-md-1">&nbsp;</div>
						<div class="col-md-3">Rider</div>
						<div class="col-md-1">Last Year</div>
						<div class="col-md-3">
							<div class="row">
								<div class="col-md-12 last-week">Last Week</div>
							</div>
							<div class="row last-week-race-name">
								<div class="col-md-12"><?php echo fc_fantasy_get_last_week_race_name($race_id); ?></div>
							</div>
						</div>
						<div class="col-md-1">Rank</div>
						<div class="col-md-3 season-points">
							<div class="row">
								<div class="col-md-12 uci-points">
									UCI Points
								</div>
							</div>
							<div class="row sub-col">
								<div class="col-md-2 c2">C2</div>
								<div class="col-md-2 c1">C1</div>
								<div class="col-md-2 cc">CC</div>
								<div class="col-md-2 cn">CN</div>
								<div class="col-md-2 cdm">CDM</div>
								<div class="col-md-2 cm">CM</div>
							</div>
						</div>

					</div>
					<?php for ($i=0;$i<$max_riders;$i++) : ?>
						<div id="rider-<?php echo $i; ?>" class="row add-remove-rider">
							<div class="col-md-1"><?php fc_add_rider_modal_btn(); ?></div>
							<div class="col-md-3 rider-name"><input type="text" name="riders[]" class="rider-name-input" value="" placeholder="Add Rider" readonly /></div>
							<div class="col-md-1 last-year-finish"></div>
							<div class="col-md-3 last-week-finish"></div>
							<div class="col-md-1 rank"></div>
							<div class="col-md-3 season-points">
								<div class="row sub-col">
									<div class="col-md-2 c2"></div>
									<div class="col-md-2 c1"></div>
									<div class="col-md-2 cc"></div>
									<div class="col-md-2 cn"></div>
									<div class="col-md-2 cdm"></div>
									<div class="col-md-2 cm"></div>
								</div>
							</div>
						</div>
					<?php endfor; ?>
				</div>
				<?php
				fc_add_rider_modal(array(
					'race_id' => $race_id,
					'sort_by' => 'rank',
				)); ?>

				<?php if (fc_race_has_start_list($race_id) && fc_is_race_roster_edit_open($race_id)) : ?>
					<input type="submit" name="submit" id="submit" value="Save Team" />
				<?php endif; ?>

				<input type="hidden" name="race" value="<?php echo $race_id; ?>" />
				<input type="hidden" name="wp_user_id" value="<?php echo get_current_user_id(); ?>" />
				<input type="hidden" name="create_team" value="1" />
				<input type="hidden" name="redirect" value="<?php echo site_url('/fantasy/create-team/?race_id='.$race_id); ?>" />
			</form>
		<?php else : ?>
			There was an error, please try again.
		<?php endif; ?>
	</div>
</div><!-- .row -->


