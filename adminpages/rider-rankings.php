<?php
global $uci_results_query, $uci_results_post, $ucicurl_riders, $ucicurl_races;

$_season=isset($_GET['season']) ? $_GET['season'] : '2014/2015';
$_nat=isset($_GET['nat']) ? $_GET['nat'] : '';
$name='';
$_week=isset($_GET['week']) ? $_GET['week'] : 1;
$search=isset($_GET['search']) ? $_GET['search'] : '';

$riders=new UCI_Results_Query(array(
	'type' => 'riders',
	'rankings' => true,
	'season' => $_season,
	'week' => $_week,
	'nat' => $_nat,
));
?>

<div class="ucicurl-rider-rankings">
	<h2>Rider Rankings <span class="ucicurl-admin-total">(<?php echo $riders->found_posts; ?>)</span></h2>

	<div class="tablenav top">
		<div class="pagination">
			<?php uci_results_admin_pagination(); ?>
		</div>

		<div class="alignright actions">
			<form name="races-search" method="get" action="">
				<input type="hidden" name="page" value="uci-curl">
				<input type="hidden" name="tab" value="rider-rankings">
				<input id="race-search" name="search" type="text" value="<?php echo $search; ?> NO WORK" />
				<input type="submit" id="search-submit" class="button action" value="Search">
			</form>
		</div>

		<form name="rankings-filter" method="get" action="">
			<input type="hidden" name="page" value="uci-curl">
			<input type="hidden" name="tab" value="rider-rankings">

			<div class="alignleft actions">
				<select name="season" class="season">
					<option value="0">-- Select Season --</option>
					<?php foreach ($ucicurl_races->seasons() as $season) : ?>
						<option value="<?php echo $season; ?>" <?php selected($_season, $season); ?>><?php echo $season; ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="alignleft actions">
				<select name="week" class="week">
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

	<table class="wp-list-table widefat fixed striped pages">
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
					<td class="rider-rank"><?php echo $uci_results_post->rank; ?></td>
					<td class="rider-name"><a href="<?php echo admin_url('admin.php?page=uci-curl&tab=riders&rider='.urlencode($uci_results_post->name)); ?>"><?php echo $uci_results_post->name; ?></a></td>
					<td class="rider-nat"><?php echo $uci_results_post->nat; ?></td>
					<td class="rider-points"><?php echo $uci_results_post->points; ?></td>
				</tr>
			<?php endwhile; endif; ?>
		</tbody>
	</table>

	<?php uci_results_admin_pagination(); ?>
</div>