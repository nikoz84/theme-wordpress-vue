<?php
/**
 * Vue Blocks — "Publicidade" sidebar slot (widget area `vb-ad-sidebar`).
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="vb-ad-slot">
	<div class="ad-label"><?php esc_html_e( 'Publicidade', 'vue-blocks' ); ?></div>
	<?php if ( is_active_sidebar( 'vb-ad-sidebar' ) ) : ?>
		<?php dynamic_sidebar( 'vb-ad-sidebar' ); ?>
	<?php else : ?>
		<div class="ad-v ad-v--sidebar">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
			<span><?php esc_html_e( 'Espaço Publicitário', 'vue-blocks' ); ?></span>
			<span class="ad-size-desktop">300 × 600</span>
			<span class="ad-size-mobile">300 × 250</span>
		</div>
	<?php endif; ?>
</div>
