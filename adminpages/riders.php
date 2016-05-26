<?php
global $ucicurl_riders;

//$s_season=isset($_GET['season']) ? $_GET['season'] : '';
$search=isset($_GET['search']) ? $_GET['search'] : '';
$nat=isset($_GET['nat']) ? $_GET['nat'] : '';

$riders=$ucicurl_riders->riders(array(
	'nat' => $nat,
));
?>

<h2>Riders <span class="ucicurl-admin-total">(<?php echo $ucicurl_riders->admin_pagination['total']; ?>)</span></h2>

<div class="tablenav top">
	<div class="pagination">
		<?php $ucicurl_riders->admin_pagination(); ?>
	</div>

	<div class="alignright actions">
		<form name="riders-search" method="get" action="">
			<input type="hidden" name="page" value="uci-curl">
			<input type="hidden" name="tab" value="riders">
			<input id="riders-search" name="search" type="text" value="<?php echo $search; ?>" />
			<input type="submit" id="search-submit" class="button action" value="Search">
		</form>
	</div>

	<form name="riders-filter" method="get" action="">
		<input type="hidden" name="page" value="uci-curl">
		<input type="hidden" name="tab" value="riders">

		<div class="alignleft actions">
			<select name="nat" class="nat">
				<option value="0">-- Select Country --</option>
				<?php foreach ($ucicurl_riders->nats() as $country) : ?>
					<option value="<?php echo $country; ?>" <?php selected($nat, $country); ?>><?php echo $country; ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<input type="submit" id="doaction" class="button action" value="Apply">
	</form>
</div>

<table class="wp-list-table widefat fixed striped pages">
	<thead>
		<tr>
			<th scope="col" class="rider-name">Name</th>
			<th scope="col" class="rider-nat">Nat.</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($riders as $rider) : ?>
			<tr>
				<td class="rider-name"><a href="<?php echo admin_url('admin.php?page=uci-curl&tab=riders&rider='.urlencode($rider->name)); ?>"><?php echo $rider->name; ?></a></td>
				<td class="rider-nat"><?php echo $rider->nat; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php $ucicurl_riders->admin_pagination(); ?>