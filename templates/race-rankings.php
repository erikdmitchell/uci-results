<?php
/**
 * Template Name: Race Rankings
 *
 * @since 	1.0.8
 */
?>

<?php
global $RaceStats,$wp_query;

$paged=get_query_var('paged',1);
$season=get_query_var('season','2015/2016');
$races=$RaceStats->get_races(array(
	'paged' => $paged,
	'per_page' => 15
));
?>

<?php get_header(); ?>

<div class="container">
	<div class="row content">
		<div class="col-md-12">

			<div class="uci-curl-race-rankings">
				<h3>Race Rankings</h3>

				<div id="season-race-rankings" class="season-race-rankings">
					<div class="header row">
						<div class="name col-md-4">Name</div>
						<div class="date col-md-2">Date</div>
						<div class="nat col-md-1">Nat</div>
						<div class="class col-md-1">Class</div>
						<div class="fq col-md-1">FQ</div>
					</div>

					<?php foreach ($races as $race) : ?>
						<div class="row">
							<div class="name col-md-4"><a href="<?php echo single_race_link($race->code); ?>"><?php echo $race->name; ?></a></div>
							<div class="date col-md-2"><?php echo $race->date; ?></div>
							<div class="nat col-md-1"><?php echo $race->nat; ?></div>
							<div class="class col-md-1"><?php echo $race->class; ?></div>
							<div class="fq col-md-1"><?php echo $race->fq; ?></div>
						</div>
					<?php endforeach; ?>
				</div>

				<?php uci_curl_pagination(); ?>

			</div><!-- .uci-curl-rider-rankings -->

		</div>
	</div>
</div><!-- .container -->

<?php get_footer(); ?>