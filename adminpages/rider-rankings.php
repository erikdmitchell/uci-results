<?php
global $uci_results_query, $rider_rankings_post, $ucicurl_riders, $ucicurl_races;

$name='';
$search=isset($_GET['search']) ? $_GET['search'] : '';

$riders=new RiderRankingsQuery(array(
	'season' => isset($_GET['season']) ? $_GET['season'] : '2016/2017',
	'week' => isset($_GET['week']) ? $_GET['week'] : 1,
	'nat' => isset($_GET['nat']) ? $_GET['nat'] : '',
	'order_by' => 'rank',
	'order' => 'ASC'
));
?>

<div class="ucicurl-rider-rankings">
	<h2>Rider Rankings <span class="ucicurl-admin-total">(<?php echo $riders->found_posts; ?>)</span></h2>

	<div class="tablenav top">
		<div class="pagination">
			<?php uci_results_admin_pagination(); ?>
		</div>

		<form id="rankings-filter" name="rankings-filter" method="get" action="">
			<input type="hidden" name="page" value="uci-results">
			<input type="hidden" name="tab" value="rider-rankings">

			<div class="alignleft actions">
				<select id="season" name="season" class="season">
					<option value="0">-- Select Season --</option>
					<?php foreach ($ucicurl_races->seasons() as $season) : ?>
						<option value="<?php echo $season; ?>" <?php selected($_season, $season); ?>><?php echo $season; ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="alignleft actions">
				<select id="week" name="week" class="week">
					<option value="0">-- Select Week --</option>
					<?php foreach ($ucicurl_races->weeks($_season) as $week) : ?>
						<option value="<?php echo $week; ?>" <?php selected($_week, $week); ?>><?php echo $week; ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="alignleft actions">
				<select name="nat" class="nat">
					<option value="0">-- Select Country --</option>
					<?php foreach ($ucicurl_races->nats() as $country) : ?>
						<option value="<?php echo $country; ?>" <?php selected($_nat, $country); ?>><?php echo $country; ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<input type="submit" id="doaction" class="button action" value="Apply">
		</form>
	</div>

	<table class="wp-list-table widefat fixed striped riders">
		<thead>
			<tr>
				<th scope="col" class="rider-rank">Rank</th>
				<th scope="col" class="rider-name">Name</th>
				<th scope="col" class="rider-nat">Nat.</th>
				<th scope="col" class="rider-points">Points</th>
			</tr>
		</thead>
		<tbody>
			<?php if ($riders->have_posts()) : while ( $riders->have_posts() ) : $riders->the_post(); ?>
				<tr>
					<td class="rider-rank"><?php echo $rider_rankings_post->rank; ?></td>
					<td class="rider-name"><a href="<?php echo admin_url('admin.php?page=uci-results&tab=riders&rider='.urlencode($rider_rankings_post->post_name)); ?>"><?php echo $rider_rankings_post->post_title; ?></a></td>
					<td class="rider-nat"><?php echo $rider_rankings_post->nat; ?></td>
					<td class="rider-points"><?php echo $rider_rankings_post->points; ?></td>
				</tr>
			<?php endwhile; endif; ?>
		</tbody>
	</table>

	<?php uci_results_admin_pagination(); ?>
</div>