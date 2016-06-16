<?php
global $ucicurl_races;

$_season=isset($_GET['season']) ? $_GET['season'] : 0;
?>

<div class="ucicurl-update-rankings">

	<h2>Update Rider Rankings</h2>

	<form name="update-rankings" class="update-rankings" method="get" action="">
		<input type="hidden" name="page" value="uci-curl">
		<input type="hidden" name="tab" value="uci-curl">
		<input type="hidden" name="action" value="update-rankings">

		<div class="seasons">
			<select name="season" class="season">
				<option value="0">-- Select Season --</option>
				<?php foreach ($ucicurl_races->seasons() as $season) : ?>
					<option value="<?php echo $season; ?>" <?php selected($_season, $season); ?>><?php echo $season; ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<input type="button" id="update" class="button button-primary" value="Update Rankings">
		<input type="button" id="update-weekly" class="button button-secondary" value="Update Weekly Rank">
	</form>

	<div class="update-rider-ranking-notes">
		<div class="rider-ranking-totals">
			<span class="current-count">0</span> out of <span class="total">0</span> processed.
		</div>
		<div class="response-result"></div>
	</div>

	<div class="update-weekly-rank-result"></div>

</div>