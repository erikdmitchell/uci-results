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
$races=new UCI_Results_Query(array(
	'per_page' => 15,
	'type' => 'races'
));
?>

<div class="uci-results uci-results-races">

	<h1 class="page-title">Races</h1>

	<div class="table uci-results-races">
		<div class="row header">
				<div class="race-name">Name</div>
				<div class="race-date">Date</div>
				<div class="race-nat">Nat</div>
				<div class="race-class">Class</div>
		</div>

		<?php if ($races->have_posts()) : while ( $races->have_posts() ) : $races->the_post(); ?>
			<div class="row">
				<div class="race-name"><a href="<?php uci_results_race_url($uci_results_post->code); ?>"><?php echo $uci_results_post->name; ?></a></div>
				<div class="race-date"><?php echo $uci_results_post->date; ?></div>
				<div class="race-nat"><?php echo ucicurl_get_country_flag($uci_results_post->nat); ?></div>
				<div class="race-class"><?php echo $uci_results_post->class; ?></div>
			</div>
		<?php endwhile; endif; ?>

	</div>

	<?php uci_results_pagination(); ?>
</div>

<?php get_footer(); ?>