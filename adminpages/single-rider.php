<?php
global $ucicurl_riders;

$rider=$ucicurl_riders->get_rider($attributes['rider_id']);
?>

<h2><?php echo $rider->name; ?></h2>

<div class="tablenav top">
	<!--
	<div class="alignright actions">
		<form name="riders-search" method="get" action="">
			<input type="hidden" name="page" value="uci-curl">
			<input type="hidden" name="tab" value="riders">
			<input id="riders-search" name="search" type="text" />
			<input type="submit" id="search-submit" class="button action" value="Search">
		</form>
	</div>
	-->

	<form name="riders-filter" method="post" action="">
		<?php wp_nonce_field('filter_single_rider', 'ucicurl_admin'); ?>

		<div class="alignleft actions">
		</div>

<!-- 		<input type="submit" id="doaction" class="button action" value="Apply"> -->
	</form>
</div>

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
				<td class="race-name"><a href=""><?php echo $result->event; ?></a></td>
				<td class="rider-place"><?php echo $result->place; ?></td>
				<td class="rider-points"><?php echo $result->par; ?></td>
				<td class="race-class"><?php echo $result->class; ?></td>
				<td class="race-season"><?php echo $result->season; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>