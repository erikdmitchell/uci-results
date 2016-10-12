<?php
global $ucicurl_races;

if ((!isset($_GET['race_id']) || $_GET['race_id']==0) && isset($_POST['race_id']))
	$_GET['race_id']==$_POST['race_id'];

$race=$ucicurl_races->get_race($_GET['race_id']);
$related_races=$ucicurl_races->get_related_races($_GET['race_id']);
?>

<?php uci_results_admin_notices(); ?>

<div class="uci-results-admin-single-race two-col">

	<form id="update-race-information" method="post" action="">

		<h2><input type="text" name="name" id="name" value="<?php echo $race->event; ?>" placeholder="Race Name" /></h2>
		<span class="code"><input type="text" name="code" id="code" value="<?php echo $race->code; ?>" placeholder="code" /></span>

		<div class="single-race col-right">
			<div class="race-details postbox uci-results-sidebox">
				<div class="upper-box">
					<h2>Race Details</h2>
				</div>

				<div class="inner-box">

						<?php wp_nonce_field('update_single_race_info', 'uci_results_admin'); ?>
						<input type="hidden" name="race_id" value="<?php echo $_GET['race_id']; ?>" />

						<div class="row">
							<label for="date">Date</label>
							<input type="text" name="date" id="date" class="date" value="<?php echo date(get_option('date_format'), strtotime($race->date)); ?>" />
						</div>
						<div class="row season">
							<label for="season">Season</label>
							<?php uci_results_seasons_dropdown('season', $race->season); ?>
						</div>
						<div class="row">
							<label for="week">Week</label>
							<input type="text" name="week" id="week" class="week" value="<?php echo $race->week; ?>" />
						</div>
						<div class="row">
							<label for="class">Class</label>
							<input type="text" name="class" id="class" class="class" value="<?php echo $race->class; ?>" />
						</div>
						<div class="row">
							<label for="nat">Nat.</label>
							<input type="text" name="nat" id="nat" class="nat" value="<?php echo $race->nat; ?>" />
						</div>
						<div class="row">
							<label for="twitter">Twitter</label>
							<input type="text" name="twitter" id="twitter" class="twitter" value="<?php echo $race->twitter; ?>" />
						</div>
						<div class="row">
							<p>
								<label for="series_id">Series</label>
								<?php $ucicurl_races->series_dropdown('series_id', $race->series_id); ?>
							</p>
						</div>
						<div class="action-buttons">
							<input type="submit" id="doaction" class="button action button-primary" value="Update">
						</div>
				</div>
			</div>
		</div>

	</form>

	<div class="col-left">
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
						<td class="rider-name"><a href="<?php echo admin_url('admin.php?page=uci-results&tab=riders&rider='.urlencode($result->name)); ?>"><?php echo $result->name; ?></a></td>
						<td class="rider-nat"><?php echo $result->nat; ?></td>
						<td class="rider-age"><?php echo $result->age; ?></td>
						<td class="rider-result"><?php echo $result->time; ?></td>
						<td class="rider-par"><?php echo $result->points; ?></td>
						<td class="rider-pcr"><?php echo $result->pcr; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2>Related Races <a href="<?php echo admin_url('admin.php?page=uci-results&tab=races&race_id='.$_GET['race_id'].'&add_related_race=yes'); ?>" id="add-related-race" class="page-title-action">Add Related Race</a></h2>

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
				<?php if ($related_races) : foreach ($related_races as $race) : ?>
					<tr>
						<td class="race-date"><?php echo date(get_option('date_format'), strtotime($race->date)); ?></td>
						<td class="race-name"><a href="<?php echo admin_url('admin.php?page=uci-results&tab=races&race_id='.$race->id); ?>"><?php echo $race->event; ?></a></td>
						<td class="race-nat"><?php echo $race->nat; ?></td>
						<td class="race-class"><?php echo $race->class; ?></td>
						<td class="race-season"><?php echo $race->season; ?></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>

</div>