<?php
/**
 * Template Name: Rider (Single)
 *
 * @since 	1.0.0
 * @version	1.0.0
 */
?>

<?php
global $RiderStats,$wp_query;

$rider_name=get_query_var('rider',0);
$results=$RiderStats->get_rider($rider_name);
?>

<?php get_header(); ?>
<div class="container">
	<div class="row content">
		<div class="col-md-12">

			<?php if (!$results) : ?>
				Rider/results not found.
			<?php else : ?>
				<div class="uci-curl-rider-rankings">
					<h3><?php echo $rider_name; ?> (<a href=""><?php echo $results[0]->country; ?></a>)</h3>

					<div id="season-rider-rankings" class="season-rider-rankings">
						<div class="header row">
							<div class="place col-md-1">Place</div>
							<div class="points col-md-1">Points</div>
							<div class="date col-md-2">Date</div>
							<div class="race col-md-4">Race</div>
							<div class="class col-md-1">Class</div>
							<div class="country col-md-1">Country</div>
							<div class="fq col-md-1">FQ</div>
						</div>

						<?php foreach ($results as $result) : ?>
							<div class="row">
							<div class="place col-md-1"><?php echo $result->place; ?></div>
							<div class="points col-md-1"><?php echo $result->par; ?></div>
							<div class="date col-md-2"><?php echo $result->date; ?></div>
							<div class="race col-md-4"><a href=""><?php echo $result->race; ?></a></div>
							<div class="class col-md-1"><?php echo $result->class; ?></div>
							<div class="country col-md-1"><?php echo $result->race_country; ?></div>
							<div class="fq col-md-1"><?php echo $result->fq; ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div><!-- .uci-curl-rider-rankings -->
			<?php endif; ?>
		</div>
	</div>
</div><!-- .container -->

<?php get_footer(); ?>