<?php
global $ucicurl_riders;

$season=isset($_GET['season']) ? $_GET['season'] : '2015/2016';
$rankings=$ucicurl_riders->get_rider_rankings(array('season' => $season));
$rank=1;
?>

<div class="ucicurl-rider-rankings">
	<h2>Rider Rankings (<?php echo $season; ?>)</h2>

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
			<?php foreach ($rankings as $rider) : ?>
				<tr>
					<td class="rider-rank"><?php echo $rank; ?></td>
					<td class="rider-name"><a href=""><?php echo $rider->name; ?></a></td>
					<td class="rider-nat"><?php echo $rider->nat; ?></td>
					<td class="rider-points"><?php echo $rider->points; ?></td>
				</tr>
				<?php $rank++; ?>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php $ucicurl_riders->admin_pagination(); ?>
</div>