<?php
global $ucicurl_riders;

$s_season=isset($_POST['season']) ? $_POST['season'] : '';
$s_class=isset($_POST['class']) ? $_POST['class'] : '';
$s_nat=isset($_POST['nat']) ? $_POST['nat'] : '';
?>

<h2>Riders</h2>

<div class="tablenav top">
	<div class="alignright actions">
		<form name="riders-search" method="get" action="">
			<input type="hidden" name="page" value="uci-curl">
			<input type="hidden" name="tab" value="riders">
			<input id="riders-search" name="search" type="text" />
			<input type="submit" id="search-submit" class="button action" value="Search">
		</form>
	</div>

	<form name="riders-filter" method="post" action="">
		<?php wp_nonce_field('filter_riders', 'ucicurl_admin'); ?>

		<div class="alignleft actions">
			<select name="nat" class="nat">
				<option value="0">-- Select Country --</option>
				<?php foreach ($ucicurl_riders->nats() as $country) : ?>
					<option value="<?php echo $country; ?>" <?php selected($s_nat, $country); ?>><?php echo $country; ?></option>
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
		<?php foreach ($ucicurl_riders->riders() as $rider) : ?>
			<tr>
				<td class="rider-name"><a href="<?php echo admin_url('admin.php?page=uci-curl&tab=riders&rider='.urlencode($rider->name)); ?>"><?php echo $rider->name; ?></a></td>
				<td class="rider-nat"><?php echo $rider->nat; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>