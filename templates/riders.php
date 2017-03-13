<?php
/**
 * template for riders page
 *
 * It can be overriden
 *
 * @since 2.0.0
 */

get_header(); ?>

<?php
$riders=new WP_Query(array(
	'posts_per_page' => 15,
	'post_type' => 'riders',
	'ranking' => true,
	'paged' => get_query_var('paged'),
));
?>

<div class="em-container uci-results uci-results-rider-rankings">

	<h1 class="page-title">Rider Rankings</h1>

	<div class="rider-rankings">
		<div class="em-row header">
			<div class="em-col-md-1 rider-rank">Rank</div>
			<div class="em-col-md-4 rider-name">Rider</div>
			<div class="em-col-md-1 rider-nat">Nat</div>
			<div class="em-col-md-2 rider-points">Points</div>
		</div>

		<?php if ($riders->posts) : while ($riders->have_posts() ) : $riders->the_post(); ?>
			<div class="em-row">
				<div class="em-col-md-1 rider-rank"><?php echo $post->rank->rank; ?></div>
				<div class="em-col-md-4 rider-name"><a href="<?php uci_results_rider_url($post->post_name); ?>"><?php the_title(); ?></a></div>
				<div class="em-col-md-1 rider-nat"><a href="<?php echo uci_results_country_url($post->nat); ?>"><?php echo uci_results_get_country_flag($post->nat); ?></a></div>
				<div class="em-col-md-2 rider-points"><?php echo $post->rank->points; ?></div>
			</div>
		<?php endwhile; endif; ?>
	</div>

	<?php uci_pagination($riders->max_num_pages); ?>

	<?php wp_reset_postdata(); ?>
</div>

<?php get_footer(); ?>