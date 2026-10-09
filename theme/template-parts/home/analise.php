<?php
/**
 * Vue Blocks — Análise de Mercado (Safe Mídia home section).
 *
 * One featured analysis + a list of recent analyses. Sources from
 * category `analise` (fallback: posts tagged `analise`). Hidden per
 * FR-004 if no posts exist.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_analise_cat = get_category_by_slug( 'analise' );
$vb_analise_q  = null;
if ( $vb_analise_cat ) {
	$vb_analise_q = new WP_Query(
		array(
			'cat'           => $vb_analise_cat->term_id,
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

if ( ! $vb_analise_q || ! $vb_analise_q->have_posts() ) {
	if ( $vb_analise_q ) {
		wp_reset_postdata();
	}
	return;
}

$vb_analise_featured = $vb_analise_q->posts[0];
$vb_analise_rest     = array_slice( $vb_analise_q->posts, 1 );
?>

<section class="analise-section">
	<div class="vb-container">
		<div class="sec-head">
			<h2 class="sec-title"><?php esc_html_e( 'Análise de Mercado', 'vue-blocks' ); ?></h2>
			<div class="sec-line"></div>
			<a href="#" class="btn-see-more"><?php esc_html_e( 'Ver todas', 'vue-blocks' ); ?> &raquo;</a>
		</div>
		<div class="analise-layout">
			<a href="<?php echo esc_url( get_permalink( $vb_analise_featured ) ); ?>" class="analise-main">
				<?php if ( has_post_thumbnail( $vb_analise_featured ) ) : ?>
					<?php echo get_the_post_thumbnail( $vb_analise_featured, 'full', array( 'alt' => esc_attr( get_the_title( $vb_analise_featured ) ) ) ); ?>
				<?php else : ?>
					<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
				<?php endif; ?>
				<div class="analise-overlay"></div>
				<div class="analise-main-content">
					<div class="analise-main-meta"><?php echo esc_html( vb_first_category( $vb_analise_featured->ID ) ); ?> · <?php echo esc_html( vb_time_ago_short( $vb_analise_featured->ID ) ); ?></div>
					<h3 class="analise-main-title"><?php echo esc_html( get_the_title( $vb_analise_featured ) ); ?></h3>
				</div>
			</a>
			<div class="analise-list">
				<?php foreach ( $vb_analise_rest as $vb_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $vb_post ) ); ?>" class="aitem">
						<div class="aitem-img">
							<?php if ( has_post_thumbnail( $vb_post ) ) : ?>
								<?php echo get_the_post_thumbnail( $vb_post, 'thumbnail', array( 'alt' => esc_attr( get_the_title( $vb_post ) ) ) ); ?>
							<?php else : ?>
								<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
							<?php endif; ?>
						</div>
						<div>
							<div class="aitem-meta"><?php echo esc_html( vb_first_category( $vb_post->ID ) ); ?> · <?php echo esc_html( vb_time_ago_short( $vb_post->ID ) ); ?></div>
							<div class="aitem-title"><?php echo esc_html( get_the_title( $vb_post ) ); ?></div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
<?php wp_reset_postdata();