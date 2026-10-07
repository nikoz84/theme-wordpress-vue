<?php
/**
 * Vue Blocks — Safe Mídia News Grid (template part).
 *
 * 3-column grid of the next 6 most-recent published posts after
 * the hero + sidebar. Excludes posts already consumed by the hero
 * (passed in via the `vb_excluded_post_ids` filter).
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_excluded = apply_filters( 'vb_news_grid_excluded_post_ids', array() );
?>

<section class="news-section">
	<div class="vb-container">
		<div class="news-head">
			<h2 class="sec-title"><?php esc_html_e( 'Últimas Notícias', 'vue-blocks' ); ?></h2>
			<div class="sec-line"></div>
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/?post_type=post' ) ); ?>" class="btn-see-more">
				<?php esc_html_e( 'Ver todas', 'vue-blocks' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
		</div>

	<?php
	$grid_q = new WP_Query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 6,
			'post__not_in'   => $vb_excluded,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	?>

	<?php if ( $grid_q->have_posts() ) : ?>
		<div class="news-grid">
			<?php
			while ( $grid_q->have_posts() ) :
				$grid_q->the_post();
				get_template_part( 'template-parts/news/news-card' );
			endwhile;
			?>
		</div>
	<?php else : ?>
		<p class="vb-empty-state"><?php esc_html_e( 'Nenhuma notícia publicada ainda.', 'vue-blocks' ); ?></p>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
	</div>
</section>