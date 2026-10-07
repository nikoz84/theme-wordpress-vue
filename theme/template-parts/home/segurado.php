<?php
/**
 * Vue Blocks — Para o Segurado (Safe Mídia home section).
 *
 * 4-card horizontal scroller with featured insurance-segment content.
 * Sources 4 most recent posts (excluding the hero + sidebar). The
 * section is hidden per FR-004 if no posts are available.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 4 most-recent posts excluding the hero + sidebar.
$vb_segurado_excluded = array();
if ( is_singular() ) {
	$vb_segurado_excluded[] = get_the_ID();
}
$vb_segurado_q = new WP_Query(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 4,
		'post__not_in'   => $vb_segurado_excluded,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

if ( ! $vb_segurado_q->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>

<section class="segurado-section">
	<div class="segurado-inner vb-container">
		<div class="segurado-block">
			<?php
			/* "Ver todos" link target — prefer the "Para o Segurado"
			 * category archive if it exists; otherwise fall back to
			 * the posts archive.
			 */
			$vb_segurado_cat = get_category_by_slug( 'segurado' );
			if ( ! $vb_segurado_cat ) {
				$vb_segurado_cat = get_category_by_slug( 'para-o-segurado' );
			}
			$vb_segurado_link = $vb_segurado_cat
				? get_category_link( $vb_segurado_cat->term_id )
				: ( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/?post_type=post' ) );
			?>
			<div class="segurado-hd">
				<h2 class="segurado-title"><?php esc_html_e( 'Para o Segurado', 'vue-blocks' ); ?></h2>
				<span class="segurado-sub"><?php esc_html_e( 'Direitos, dicas e orientações para quem já tem ou quer contratar um seguro.', 'vue-blocks' ); ?></span>
				<a href="<?php echo esc_url( $vb_segurado_link ); ?>" class="segurado-btn-all"><?php esc_html_e( 'Ver todos', 'vue-blocks' ); ?> &raquo;</a>
			</div>
			<div class="segurado-grid">
				<?php
				while ( $vb_segurado_q->have_posts() ) :
					$vb_segurado_q->the_post();
					?>
					<a href="<?php the_permalink(); ?>" class="scard">
						<div class="scard-wrap">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'vb-card', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
							<?php else : ?>
								<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
							<?php endif; ?>
							<div class="scard-overlay"></div>
							<div class="scard-content">
								<span class="scard-meta"><?php echo esc_html( vb_first_category() ); ?></span>
								<span class="scard-title"><?php the_title(); ?></span>
							</div>
						</div>
					</a>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
</section>
<?php wp_reset_postdata();