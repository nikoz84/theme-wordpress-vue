<?php
/**
 * Vue Blocks — Mais Lidas da Semana (Safe Mídia home section).
 *
 * 8 most-recent posts (any category). Hidden per FR-004 if no posts
 * exist. The label "Mais Lidas da Semana" is editorial; the data
 * sourcing is "most recent" per research.md R3 (no view-count
 * tracking in v2).
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_mais_q = new WP_Query(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 8,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
if ( ! $vb_mais_q->have_posts() ) {
	return;
}
?>

<section class="maislist-section">
	<div class="vb-container">
		<div class="sec-head">
			<h2 class="sec-title"><?php esc_html_e( 'Mais Lidas da Semana', 'vue-blocks' ); ?></h2>
			<div class="sec-line"></div>
			<a href="#" class="btn-see-more"><?php esc_html_e( 'Ver todas', 'vue-blocks' ); ?> &raquo;</a>
		</div>
		<div class="mais-layout">
			<div class="mais-col">
				<?php
				$vb_mais_i = 1;
				while ( $vb_mais_q->have_posts() ) :
					$vb_mais_q->the_post();
					?>
					<a href="<?php the_permalink(); ?>" class="mais-item">
						<span class="mais-num"><?php echo (string) $vb_mais_i; ?></span>
						<div>
							<div class="mais-cat"><?php echo esc_html( vb_first_category() ); ?></div>
							<div class="mais-title"><?php the_title(); ?></div>
						</div>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="mais-thumb"><?php the_post_thumbnail( 'thumbnail', array( 'alt' => esc_attr( get_the_title() ) ) ); ?></div>
						<?php endif; ?>
					</a>
					<?php $vb_mais_i++; ?>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
</section>
<?php wp_reset_postdata();