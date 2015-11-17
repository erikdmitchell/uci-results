<?php
/**
 * Template Name: Race (Single)
 *
 * @since 	1.0.8
 */
?>

<?php
global $RaceStats,$wp_query;

$race_code=get_query_var('race',0);
$race=$RaceStats->get_race($race_code);
?>

<?php get_header(); ?>
<div class="container">
	<div class="row content">
		<div class="col-md-12">

			<?php if (!$race->results) : ?>
				Race not found.
			<?php else : ?>
				<div class="uci-curl-rider-rankings">
					<h1 class="entry-title"><?php echo $race->details->race; ?> <?php echo get_country_flag($race->details->nat); ?></h1>
					<div class="race-details">
						<div class="race-date"><?php echo $race->details->date; ?></div>
						<div class="race-class"><?php echo $race->details->class; ?></div>
					</div>

					<div id="season-rider-rankings" class="season-rider-rankings">
						<div class="header row">
							<div class="place col-md-1">Place</div>
							<div class="rider col-md-4">Rider</div>
							<div class="points col-md-1">Points</div>
							<div class="nat col-md-1">Nat</div>
							<div class="age col-md-1">Age</div>
							<div class="time col-md-2">Time</div>
						</div>

						<?php foreach ($race->results as $result) : ?>
							<div class="row">
								<div class="place col-md-1"><?php echo $result->place; ?></div>
								<div class="rider col-md-4"><a href="<?php echo single_rider_link($result->rider,$race->details->season); ?>"><?php echo $result->rider; ?></a></div>
								<div class="points col-md-1"><?php echo $result->points; ?></div>
								<div class="nat col-md-1"><a href="<?php echo single_country_link($result->nat,$race->details->season); ?>"><?php echo $result->nat; ?></a></div>
								<div class="age col-md-1"><?php echo $result->age; ?></div>
								<div class="time col-md-2"><?php echo $result->time; ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div><!-- .uci-curl-rider-rankings -->
			<?php endif; ?>
		</div>
	</div>
</div><!-- .container -->

<?php get_footer(); ?>