<?php
/**
 * Vue Blocks — Safe Mídia Site Footer (template part).
 *
 * Footer with logo, columns of links (footer menu + categories),
 * newsletter form, and copyright bar.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$vb_year       = (int) gmdate( 'Y' );
$vb_site_title = get_bloginfo( 'name' );
?>

<footer class="site-footer">
	<div class="footer-main">

		<div>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-logo">
				<span class="logo-shield">
					<svg width="18" height="20" viewBox="0 0 18 22" fill="none" aria-hidden="true">
						<path d="M9 1L1.5 4.5V10.5C1.5 15.1 4.7 19.4 9 21C13.3 19.4 16.5 15.1 16.5 10.5V4.5L9 1Z" fill="white" fill-opacity=".92"/>
					</svg>
				</span>
				<span class="footer-logo-text">Vue <span>Blocks</span></span>
			</a>
			<p class="footer-desc">
				<?php esc_html_e( 'Portal de notícias do setor. Conteúdo editorial independente, com curadoria e atualização diária.', 'vue-blocks' ); ?>
			</p>
			<div class="footer-socials">
				<?php
				/*
				 * Footer social icons — same Customizer source as the navbar
				 * (per FR-006). Empty URL → icon hidden.
				 */
				$vb_socials_footer = array(
					'facebook'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>',
					'instagram' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
					'x'         => '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H9.751l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
					'linkedin'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>',
				);
				foreach ( $vb_socials_footer as $vb_slug => $vb_svg ) :
					$vb_url = get_theme_mod( "vb_social_{$vb_slug}", '' );
					if ( '' === $vb_url ) {
						continue;
					}
					?>
					<a href="<?php echo esc_url( $vb_url ); ?>" class="footer-soc" aria-label="<?php echo esc_attr( ucfirst( $vb_slug ) ); ?>" target="_blank" rel="noopener">
						<?php echo $vb_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<div>
			<h4 class="footer-col-title"><?php esc_html_e( 'Editorias', 'vue-blocks' ); ?></h4>
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'footer-links',
						'fallback_cb'    => false,
						'depth'          => 1,
						'items_wrap'     => '<ul class="footer-links">%3$s</ul>',
					)
				);
				?>
			<?php else : ?>
				<ul class="footer-links">
					<?php
					$cats = get_categories( array( 'number' => 5, 'hide_empty' => true ) );
					foreach ( $cats as $cat ) :
						?>
						<li><a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div>
			<h4 class="footer-col-title"><?php esc_html_e( 'Institucional', 'vue-blocks' ); ?></h4>
			<ul class="footer-links">
				<li><a href="#"><?php esc_html_e( 'Sobre', 'vue-blocks' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Contato', 'vue-blocks' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Política de Privacidade', 'vue-blocks' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Termos de Uso', 'vue-blocks' ); ?></a></li>
			</ul>
		</div>

		<div>
			<h4 class="footer-col-title"><?php esc_html_e( 'Newsletter', 'vue-blocks' ); ?></h4>
			<p class="footer-nl-sub">
				<?php esc_html_e( 'Receba as principais notícias do setor no seu e-mail.', 'vue-blocks' ); ?>
			</p>
			<form class="footer-nl-form" onsubmit="return false;">
				<input type="email" name="email" class="footer-nl-input" placeholder="<?php esc_attr_e( 'seu@email.com', 'vue-blocks' ); ?>" aria-label="<?php esc_attr_e( 'Seu e-mail', 'vue-blocks' ); ?>" />
				<button type="submit" class="footer-nl-btn"><?php esc_html_e( 'Assinar', 'vue-blocks' ); ?></button>
			</form>
		</div>

	</div>

	<div class="footer-bottom">
		<span>&copy; <?php echo (int) $vb_year; ?> <?php esc_html_e( 'Safe Mídia', 'vue-blocks' ); ?>. <?php esc_html_e( 'Todos os direitos reservados.', 'vue-blocks' ); ?></span>
		<span><?php esc_html_e( 'Customizado por Nicolás Romero', 'vue-blocks' ); ?></span>
	</div>
</footer>