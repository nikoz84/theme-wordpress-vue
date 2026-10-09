<?php
/**
 * Vue Blocks — Newsletter Grande (Safe Mídia home section).
 *
 * Large newsletter section with a dark navy background, an
 * eyebrow + headline + description on the left, and a perks list +
 * form on the right. The form is static (no backend persistence in
 * v2); see contracts/home-sections.contract.md § 6.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="nl-section">
	<div class="vb-container">
		<div class="nl-block">
			<div class="nl-left">
				<div class="nl-eyebrow"><?php esc_html_e( 'NEWSLETTER', 'vue-blocks' ); ?></div>
				<h2 class="nl-headline"><?php esc_html_e( 'As notícias do setor direto no seu e-mail.', 'vue-blocks' ); ?></h2>
				<p class="nl-desc">
					<?php esc_html_e( 'Receba uma seleção semanal das principais matérias, análises e novidades regulatorias do setor de seguros.', 'vue-blocks' ); ?>
				</p>
			</div>
			<div class="nl-right">
				<ul class="nl-perks">
					<li class="perk"><span class="perk-dot">&check;</span> <?php esc_html_e( 'Curadoria semanal das principais matérias', 'vue-blocks' ); ?></li>
					<li class="perk"><span class="perk-dot">&check;</span> <?php esc_html_e( 'Análises exclusivas do mercado de seguros', 'vue-blocks' ); ?></li>
					<li class="perk"><span class="perk-dot">&check;</span> <?php esc_html_e( 'Atualizações regulatórias em primeira mão', 'vue-blocks' ); ?></li>
				</ul>
				<form class="nl-form" id="nl-grande" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php vb_newsletter_hidden_fields( 'nl-grande' ); ?>
					<label class="screen-reader-text" for="nl-grande-email"><?php esc_html_e( 'Seu e-mail', 'vue-blocks' ); ?></label>
					<input id="nl-grande-email" type="email" name="vb_email" required autocomplete="email" class="nl-input" placeholder="<?php esc_attr_e( 'seu@email.com', 'vue-blocks' ); ?>" />
					<button type="submit" class="nl-btn"><?php esc_html_e( 'Assinar', 'vue-blocks' ); ?></button>
				</form>
			<?php $vb_msg = vb_newsletter_message( 'nl-grande' ); ?>
			<?php if ( $vb_msg ) : ?><p class="vb-nl-inline-msg" role="status"><?php echo esc_html( $vb_msg ); ?></p><?php endif; ?>
				<p class="nl-note"><?php esc_html_e( 'Sem spam. Cancele a qualquer momento.', 'vue-blocks' ); ?></p>
			</div>
		</div>
	</div>
</section>