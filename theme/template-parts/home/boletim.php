<?php
/**
 * Vue Blocks — Boletim Regulatório (Safe Mídia home section).
 *
 * Regulatory updates from a "regulatorio" WordPress category. The
 * "type" chip per row is derived from the post's first tag (or "outros"
 * if no tags). Hidden per FR-004 if no posts exist.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_boletim_cat = get_category_by_slug( 'regulatorio' );
if ( ! $vb_boletim_cat ) {
	return;
}

$vb_boletim_q = new WP_Query(
	array(
		'category'      => $vb_boletim_cat->term_id,
		'posts_per_page' => 6,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
if ( ! $vb_boletim_q->have_posts() ) {
	return;
}
?>

<section class="boletim-section">
	<div class="vb-container">
		<div class="sec-head">
			<h2 class="sec-title"><?php esc_html_e( 'Boletim Regulatório', 'vue-blocks' ); ?></h2>
			<div class="sec-line"></div>
			<a href="<?php echo esc_url( get_category_link( $vb_boletim_cat->term_id ) ); ?>" class="btn-see-more"><?php esc_html_e( 'Ver todos', 'vue-blocks' ); ?> &raquo;</a>
		</div>
		<div class="boletim-block">
			<div class="boletim-header">
				<span class="boletim-header-title"><?php esc_html_e( 'ATUALIZAÇÕES', 'vue-blocks' ); ?></span>
				<span class="boletim-header-period"><?php echo esc_html( date_i18n( 'F Y' ) ); ?></span>
			</div>
			<?php
			while ( $vb_boletim_q->have_posts() ) :
				$vb_boletim_q->the_post();
				$vb_tags = wp_get_post_tags( get_the_ID() );
				$vb_tipo = ! empty( $vb_tags ) ? strtolower( $vb_tags[0]->slug ) : 'outros';
				$vb_tipo_label = ! empty( $vb_tags ) ? $vb_tags[0]->name : __( 'Outros', 'vue-blocks' );
				?>
				<a href="<?php the_permalink(); ?>" class="boletim-item">
					<span class="boletim-tipo <?php echo esc_attr( sanitize_html_class( $vb_tipo ) ); ?>"><?php echo esc_html( $vb_tipo_label ); ?></span>
					<div>
						<div class="boletim-text"><?php the_title(); ?></div>
						<div class="boletim-num"><?php echo esc_html( get_the_date() ); ?></div>
					</div>
				</a>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php wp_reset_postdata();