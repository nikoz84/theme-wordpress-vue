<?php
/**
 * Vue Blocks — Colunistas (Safe Mídia home section).
 *
 * One card per distinct post_author in the "colunistas" category.
 * Sourced via the `vb_get_colunista_cards()` helper (per
 * contracts/home-sections.contract.md § 11). Hidden when the helper
 * returns an empty array.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_colunista_cards = vb_get_colunista_cards( 3 );
if ( empty( $vb_colunista_cards ) ) {
	return;
}
?>

<section class="colunistas-section">
	<div class="vb-container">
		<div class="sec-head">
			<h2 class="sec-title"><?php esc_html_e( 'Colunistas', 'vue-blocks' ); ?></h2>
			<div class="sec-line"></div>
		</div>
		<div class="col-grid">
			<?php foreach ( $vb_colunista_cards as $vb_card ) : ?>
				<a href="<?php echo esc_url( $vb_card['latest_post_permalink'] ); ?>" class="col-card">
					<div class="col-card-top">
						<div class="col-avatar">
							<?php if ( ! empty( $vb_card['avatar_url'] ) ) : ?>
								<img src="<?php echo esc_url( $vb_card['avatar_url'] ); ?>" alt="<?php echo esc_attr( $vb_card['author_display_name'] ); ?>" />
							<?php else : ?>
								<img src="<?php echo esc_url( vb_placeholder_image( $vb_card['author_id'] ) ); ?>" alt="<?php echo esc_attr( $vb_card['author_display_name'] ); ?>" />
							<?php endif; ?>
						</div>
						<div>
							<div class="col-name"><?php echo esc_html( $vb_card['author_display_name'] ); ?></div>
							<div class="col-role">
								<?php
								echo esc_html(
									! empty( $vb_card['author_bio'] )
										? wp_trim_words( $vb_card['author_bio'], 12, '…' )
										: __( 'Colunista', 'vue-blocks' )
								);
								?>
							</div>
						</div>
					</div>
					<div class="col-card-body">
						<div class="col-tag"><?php esc_html_e( 'Artigo', 'vue-blocks' ); ?></div>
						<div class="col-article-title"><?php echo esc_html( $vb_card['latest_post_title'] ); ?></div>
						<div class="col-read"><?php esc_html_e( 'Ler coluna →', 'vue-blocks' ); ?></div>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>