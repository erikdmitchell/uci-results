<?php
/**
 * Country (Single)
 *
 * @since 	2.0.0
 */
?>

<?php
// get country
?>

<div class="uci-results-country">

			<?php if (empty($results)) : ?>
				Results not found.
			<?php else : ?>
				<div class="uci-curl-rider-rankings">
					<h1 class="entry-title"><?php echo get_country_flag($country,'full'); ?> (<?php echo $season; ?>)</h1>

					<div id="season-rider-rankings" class="season-rider-rankings">
						<div class="header row">
							<div class="name col-md-2">Rider</div>
							<div class="place col-md-1">Place</div>
							<div class="points col-md-1">Points</div>
							<div class="date col-md-2">Date</div>
							<div class="race col-md-4">Race</div>
							<div class="class col-md-1">Class</div>
							<div class="fq col-md-1">FQ</div>
						</div>

						<?php foreach ($results as $result) : ?>
							<div class="row">
								<div class="name col-md-2"><?php echo $result->rider; ?></div>
								<div class="place col-md-1"><?php echo $result->place; ?></div>
								<div class="points col-md-1"><?php echo $result->points; ?></div>
								<div class="date col-md-2"><?php echo $result->date; ?></div>
								<div class="race col-md-4"><a href="<?php echo single_race_link($result->code); ?>"><?php echo $result->race; ?></a></div>
								<div class="class col-md-1"><?php echo $result->class; ?></div>
								<div class="fq col-md-1"><?php echo $result->fq; ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div><!-- .uci-curl-rider-rankings -->
			<?php endif; ?>

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
						<td class="rider-name"><a href="<?php echo uci_results_rider_url($result->slug); ?>"><?php echo $result->name; ?></a></td>
						<td class="rider-points"><?php echo $result->points; ?></td>
						<td class="rider-nat"><a href="<?php echo uci_results_country_url($result->nat); ?>"><?php echo ucicurl_get_country_flag($result->nat); ?></a></td>
						<td class="rider-age"><?php echo $result->age; ?></td>
						<td class="rider-time"><?php echo $result->time; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>

		</table>
</div>