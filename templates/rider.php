<?php
/**
 * Template Name: Rider (Single)
 *
 * @since 	2.0.0
 */

global $ucicurl_riders;

$rider=$ucicurl_riders->get_rider($attributes['rider_id']);

global $wp_query;
print_r($wp_query->query_vars);
?>

<div class="ucicurl-rider">
	<table class="wp-list-table widefat fixed striped pages">
		<thead>
			<tr>
				<th scope="col" class="race-date">Date</th>
				<th scope="col" class="race-name">Event</th>
				<th scope="col" class="rider-place">Place</th>
				<th scope="col" class="rider-points">Points</th>
				<th scope="col" class="race-class">Class</th>
				<th scope="col" class="race-season">Season</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($rider->results as $result) : ?>
				<tr>
					<td class="race-date"><?php echo date(get_option('date_format'), strtotime($result->date)); ?></td>
					<td class="race-name"><a href="#"><?php echo $result->event; ?></a></td>
					<td class="rider-place"><?php echo $result->place; ?></td>
					<td class="rider-points"><?php echo $result->par; ?></td>
					<td class="race-class"><?php echo $result->class; ?></td>
					<td class="race-season"><?php echo $result->season; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>