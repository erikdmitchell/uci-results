<?php
global $uci_results_query, $uci_results_post, $ucicurl_races;

$s_season=isset($_GET['season']) ? $_GET['season'] : '';
$s_class=isset($_GET['class']) ? $_GET['class'] : '';
$s_nat=isset($_GET['nat']) ? $_GET['nat'] : '';
$search=isset($_GET['search']) ? $_GET['search'] : '';

$races=new UCI_Results_Query(array(
	'type' => 'races',
	'class' => $s_class,
	'season' => $s_season,
	'nat' => $s_nat,
));
?>

<h2>Races <span class="ucicurl-admin-total">(<?php echo $races->found_posts; ?>)</span></h2>

<div class="tablenav top">
	<div class="pagination">
		<?php uci_results_admin_pagination(); ?>
	</div>

	<div class="alignright actions">
		<form name="races-search" method="get" action="">
			<input type="hidden" name="page" value="uci-curl">
			<input type="hidden" name="tab" value="races">
			<input id="race-search" name="search" type="text" value="<?php echo $search; ?>" />
			<input type="submit" id="search-submit" class="button action" value="Search">
		</form>
	</div>

	<form name="races-filter" method="get" action="">
		<input type="hidden" name="page" value="uci-curl">
		<input type="hidden" name="tab" value="races">

		<div class="alignleft actions">
			<select name="season" class="season">
				<option value="0">-- Select Season --</option>
				<?php foreach ($ucicurl_races->seasons() as $season) : ?>
					<option value="<?php echo $season; ?>" <?php selected($s_season, $season); ?>><?php echo $season; ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="alignleft actions">
			<select name="class" class="class">
				<option value="0">-- Select Class --</option>
				<?php foreach ($ucicurl_races->classes() as $class) : ?>
					<option value="<?php echo $class; ?>" <?php selected($s_class, $class); ?>><?php echo $class; ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="alignleft actions">
			<select name="nat" class="nat">
				<option value="0">-- Select Country --</option>
				<?php foreach ($ucicurl_races->nats() as $country) : ?>
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
			<th scope="col" class="race-season">Season</th>
		</tr>
	</thead>
	<tbody>
		<?php if ($races->have_posts()) : while ( $races->have_posts() ) : $races->the_post(); ?>
			<tr>
				<td class="race-date"><?php echo $uci_results_post->date; ?></td>
				<td class="race-name"><a href="<?php echo admin_url('admin.php?page=uci-curl&tab=races&race_id='.$uci_results_post->id); ?>"><?php echo $uci_results_post->event; ?></a></td>
				<td class="race-nat"><?php echo $uci_results_post->nat; ?></td>
				<td class="race-class"><?php echo $uci_results_post->class; ?></td>
				<td class="race-season"><?php echo $uci_results_post->season; ?></td>
			</tr>
		<?php endwhile; endif; ?>
	</tbody>
</table>

<?php uci_results_admin_pagination(); ?>