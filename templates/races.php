<?php
/**
 * div e template for races page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

get_header(); ?>

<?php
$races=uci_get_races(array(
	'per_page' => 15,
));
?>

<div class="em-container uci-results uci-results-races">

	<h1 class="page-title">Races</h1>

	<div class="uci-results-races">
		<div class="em-row header">
				<div class="em-col-md-6 race-name">Name</div>
				<div class="em-col-md-2 race-date">Date</div>
				<div class="em-col-md-1 race-nat">Nat</div>
				<div class="em-col-md-1 race-class">Class</div>
		</div>

		<?php if ($races) : foreach ($races as $race) : ?>
			<div class="em-row">
				<div class="em-col-md-6 race-name"><a href="<?php uci_results_race_url($race->post_name); ?>"><?php echo $race->post_title; ?></a></div>
				<div class="em-col-md-2 race-date"><?php echo $race->race_date; ?></div>
				<div class="em-col-md-1 race-nat"><?php echo uci_results_get_country_flag($race->nat); ?></div>
				<div class="em-col-md-1 race-class"><?php echo $race->class; ?></div>
			</div>
		<?php endforeach; endif; ?>

	</div>

	<?php uci_pagination('races'); ?>
</div>

<?php get_footer(); ?>