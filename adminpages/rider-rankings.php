<?php
global $rider_rankings_post;

$search=isset($_GET['search']) ? $_GET['search'] : '';
$season=isset($_GET['season']) ? $_GET['season'] : '20162017';
$week=isset($_GET['week']) ? $_GET['week'] : 1;
$nat=isset($_GET['nat']) ? $_GET['nat'] : '';

$riders=new RiderRankingsQuery(array(
	'season' => $season,
	'week' => $week,
	'nat' => $nat,
));
?>

<div class="ucicurl-rider-rankings">
	<h2>Rider Rankings <span class="ucicurl-admin-total">(<?php echo $riders->found_posts; ?>)</span></h2>

	<div class="tablenav top">
		<div class="pagination">
			<?php uci_rider_rankings_admin_pagination(); ?>
		</div>

		<form id="rankings-filter" name="rankings-filter" method="get" action="">
			<input type="hidden" name="page" value="uci-results">
			<input type="hidden" name="subpage" value="rider-rankings">

			<div class="alignleft actions">
				<?php echo uci_get_race_seasons_dropdown('season', $season); ?>
			</div>

			<div class="alignleft actions">
				<?php echo uci_get_season_weeks_dropdown($season, $week); ?>
			</div>

			<div class="alignleft actions">
				<?php echo uci_get_country_dropdown('nat', $nat); ?>
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

	<?php uci_rider_rankings_admin_pagination(); ?>
</div>