<?php
global $ucicurl_races, $ucicurl_viewdb;

$s_season=isset($_POST['season']) ? $_POST['season'] : '';
$s_class=isset($_POST['class']) ? $_POST['class'] : '';
$s_nat=isset($_POST['nat']) ? $_POST['nat'] : '';
?>

<h2>Races</h2>

<div class="tablenav top">
	<div class="alignright actions">
		<form name="races-search" method="get" action="">
			<input type="hidden" name="page" value="uci-curl">
			<input type="hidden" name="tab" value="races">
			<input id="race-search" name="search" type="text" />
			<input type="submit" id="search-submit" class="button action" value="Search">
		</form>
	</div>

	<form name="races-filter" method="post" action="">
		<?php wp_nonce_field('filter_races', 'ucicurl_admin'); ?>

		<div class="alignleft actions">
			<select name="season" class="season">
				<option value="0">-- Select Season --</option>
				<?php foreach ($ucicurl_viewdb->seasons() as $season) : ?>
					<option value="<?php echo $season; ?>" <?php selected($s_season, $season); ?>><?php echo $season; ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="alignleft actions">
			<select name="class" class="class">
				<option value="0">-- Select Class --</option>
				<?php foreach ($ucicurl_viewdb->classes() as $class) : ?>
					<option value="<?php echo $class; ?>" <?php selected($s_class, $class); ?>><?php echo $class; ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="alignleft actions">
			<select name="nat" class="nat">
				<option value="0">-- Select Country --</option>
				<?php foreach ($ucicurl_viewdb->nats() as $country) : ?>
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
			<th scope="col" class="race-date">Date</th>
			<th scope="col" class="race-name">Event</th>
			<th scope="col" class="race-nat">Nat.</th>
			<th scope="col" class="race-class">Class</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($ucicurl_races->races() as $race) : ?>
			<tr>
				<td class="race-date"><?php echo $race->date; ?></td>
				<td class="race-name"><a href=""><?php echo $race->event; ?></a></td>
				<td class="race-nat"><?php echo $race->nat; ?></td>
				<td class="race-class"><?php echo $race->class; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>