<?php
/**
 * Race (Single)
 *
 * @since 	2.0.0
 */
?>

<?php
global $ucicurl_races;

$race=$ucicurl_races->get_race(get_query_var('race_code'));
?>

<div class="uci-results-race">

	<?php if (!$race->results) : ?>
		<div class="race-results-not-found">Race results not found.</div>
	<?php else : ?>
		<div class="race-name"><?php echo $race->event; ?> <?php echo ucicurl_get_country_flag($race->nat); ?></div>

		<div class="race-details">
			<div class="race-date"><?php echo date(get_option('date_format'), strtotime($race->date)); ?></div>
			<div class="race-class"><?php echo $race->class; ?></div>
		</div>

		<table class="wp-list-table widefat fixed striped single-race">
			<thead>
				<tr>
					<th scope="col" class="rider-place">Place</th>
					<th scope="col" class="rider-name">Rider</th>
					<th scope="col" class="rider-points">Points</th>
					<th scope="col" class="rider-nat">Nat</th>
					<th scope="col" class="rider-age">Age</th>
					<th scope="col" class="rider-time">Time</th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($race->results as $result) : ?>
					<tr>
						<td class="rider-place"><?php echo $result->place; ?></td>
						<td class="rider-name"><a href=""><?php echo $result->name; ?></a></td>
						<td class="rider-points"><?php echo $result->par; ?></td>
						<td class="rider-nat"><a href=""><?php echo ucicurl_get_country_flag($result->nat); ?></a></td>
						<td class="rider-age"><?php echo $result->age; ?></td>
						<td class="rider-time"><?php echo $result->result; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>

		</table>
	<?php endif; ?>

</div>