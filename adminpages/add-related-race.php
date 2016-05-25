<?php
global $ucicurl_races;

$race=$ucicurl_races->get_race($_GET['race_id']);
$related_races=$ucicurl_races->get_related_races($_GET['race_id']);
$related_race_id=$ucicurl_races->get_related_race_id($_GET['race_id']);
?>

<h2>Add Related Race</h2>

<div class="add-related-race">

	<div class="search-form">
		<label for="search-related-races">Search Races</label>
		<input type="text" name="search-related-races" id="search-related-races" class="regular-text">

		<input type="hidden" id="main_race_id" name="main_race_id" value="<?php echo $_GET['race_id']; ?>">
	</div>

	<div class="races-list">

		<h3><?php echo $race->event; ?></h3>

		<form name="add-races" method="post" action="">
			<?php wp_nonce_field('add_related_races', 'uci_curl'); ?>
			<input type="hidden" id="race_id" name="race_id" value="<?php echo $_GET['race_id']; ?>">
			<input type="hidden" id="related_race_id" name="related_race_id" value="<?php echo $related_race_id; ?>">

			<table class="wp-list-table widefat fixed striped pages">
				<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>
						<th scope="col" class="race-date">Date</th>
						<th scope="col" class="race-name">Event</th>
						<th scope="col" class="race-nat">Nat.</th>
						<th scope="col" class="race-class">Class</th>
						<th scope="col" class="race-season">Season</th>
					</tr>
				</thead>
				<tbody id="related-races-search-results"></tbody>
			</table>

			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Add Races"></p>
		</form>

	</div>
</div>
