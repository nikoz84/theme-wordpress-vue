<?php
/**
 * Vue Blocks — Newsletter Compact (Safe Mídia home section).
 *
 * Compact newsletter subscription block rendered between the news
 * grid and the "Para o Segurado" rail. The form is static (no
 * backend persistence in v2); see contracts/home-sections.contract.md
 * § 4.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="nl-compact-section">
	<div class="vb-container">
		<div class="nl-compact-block">
			<div class="nl-compact-left">
				<span class="nl-compact-icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
				</span>
				<div>
					<div class="nl-compact-title"><?php esc_html_e( 'Receba as principais notícias', 'vue-blocks' ); ?></div>
					<div class="nl-compact-sub"><?php esc_html_e( 'Inscreva-se em nossa newsletter e fique por dentro.', 'vue-blocks' ); ?></div>
				</div>
			</div>
			<form class="nl-compact-form" onsubmit="return false;">
				<label class="screen-reader-text" for="nl-compact-email"><?php esc_html_e( 'Seu e-mail', 'vue-blocks' ); ?></label>
				<input id="nl-compact-email" type="email" name="email" class="nl-compact-input" placeholder="<?php esc_attr_e( 'seu@email.com', 'vue-blocks' ); ?>" />
				<button type="submit" class="nl-compact-btn"><?php esc_html_e( 'Inscrever', 'vue-blocks' ); ?></button>
			</form>
		</div>
	</div>
</section>