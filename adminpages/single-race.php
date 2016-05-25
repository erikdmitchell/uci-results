<?php
global $ucicurl_races;

$race=$ucicurl_races->get_race($_GET['race_id']);
$related_races=$ucicurl_races->get_related_races($_GET['race_id']);
?>

<h2><?php echo $race->event; ?></h2>

<div class="tablenav top single-race">
	<div class="date alignleft"><?php echo date(get_option('date_format'), strtotime($race->date)); ?> <span class="season">(<?php echo $race->season; ?>)</span></div>
	<div class="class alignleft"><?php echo $race->class; ?></div>
	<div class="nat alignleft"><?php echo $race->nat; ?></div>
</div>

<table class="wp-list-table widefat fixed striped pages">
	<thead>
		<tr>
			<th scope="col" class="rider-place">Place</th>
			<th scope="col" class="rider-name">Points</th>
			<th scope="col" class="rider-nat">Class</th>
			<th scope="col" class="rider-age">Age</th>
			<th scope="col" class="rider-result">Result</th>
			<th scope="col" class="rider-par">Par</th>
			<th scope="col" class="rider-pcr">Pcr</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($race->results as $result) : ?>
			<tr>
				<td class="rider-place"><?php echo $result->place; ?></td>
				<td class="rider-name"><a href="<?php echo admin_url('admin.php?page=uci-curl&tab=riders&rider='.urlencode($result->name)); ?>"><?php echo $result->name; ?></a></td>
				<td class="rider-nat"><?php echo $result->nat; ?></td>
				<td class="rider-age"><?php echo $result->age; ?></td>
				<td class="rider-result"><?php echo $result->result; ?></td>
				<td class="rider-par"><?php echo $result->par; ?></td>
				<td class="rider-pcr"><?php echo $result->pcr; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h2>Related Races <a href="<?php echo admin_url('admin.php?page=uci-curl&tab=races&race_id='.$_GET['race_id'].'&add_related_race=yes'); ?>" id="add-related-race" class="page-title-action">Add Related Race</a></h2>

<table class="wp-list-table widefat fixed striped pages related-races">
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
		<?php foreach ($related_races as $race) : ?>
			<tr>
				<td class="race-date"><?php echo date(get_option('date_format'), strtotime($race->date)); ?></td>
				<td class="race-name"><a href="<?php echo admin_url('admin.php?page=uci-curl&tab=races&race_id='.$race->id); ?>"><?php echo $race->event; ?></a></td>
				<td class="race-nat"><?php echo $race->nat; ?></td>
				<td class="race-class"><?php echo $race->class; ?></td>
				<td class="race-season"><?php echo $race->season; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>