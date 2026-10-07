<?php
/**
 * Vue Blocks — Safe Mídia Navbar (template part).
 *
 * Renders the sticky navbar matching the Safe Mídia reference:
 *   - Logo shield + brand name (links to home)
 *   - Primary nav (wp_nav_menu via the "primary" location)
 *   - Social icons (stub — links to the WP social menu if configured)
 *   - Mobile hamburger toggle (data-target attributes only; the toggle
 *     behavior is layered by Vue via the `vue-blocks-app` script and
 *     matches the `safe-midia` data hooks below)
 *   - Subscribe CTA pill button
 *
 * Markup follows the Safe Mídia visual structure so the existing
 * `theme/style.css` Safe Mídia section styles bind directly.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<header class="navbar">
	<nav class="nav-inner">

		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-logo">
			<span class="logo-shield">
				<svg width="18" height="20" viewBox="0 0 18 22" fill="none" aria-hidden="true">
					<path d="M9 1L1.5 4.5V10.5C1.5 15.1 4.7 19.4 9 21C13.3 19.4 16.5 15.1 16.5 10.5V4.5L9 1Z" fill="white" fill-opacity=".92"/>
				</svg>
			</span>
			<span class="logo-text">Safe <span>Mídia</span></span>
		</a>

		<span class="nav-sep" aria-hidden="true"></span>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav-links',
					'fallback_cb'    => false,
					'depth'          => 2,
					'link_class'     => '',
					'item_class'     => '',
				)
			);
			?>
		<?php else : ?>
			<div class="nav-links">
				<a href="#" class="active">Mercado</a>
				<a href="#">Auto</a>
				<a href="#">Saúde</a>
				<a href="#">Vida</a>
				<a href="#">Empresarial</a>
				<a href="#">Opinião</a>
			</div>
		<?php endif; ?>

		<span class="nav-sep" aria-hidden="true"></span>

		<div class="nav-socials">
			<?php
			/*
			 * Social icons — read URLs from the Customizer (theme_mod).
			 * An empty value suppresses the <a> element so the navbar stays
			 * clean when the admin hasn't set a profile for a platform
			 * yet (per FR-005).
			 */
			$vb_socials_nav = array(
				'facebook'  => array(
					'label' => __( 'Facebook', 'vue-blocks' ),
					'svg'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>',
				),
				'instagram' => array(
					'label' => __( 'Instagram', 'vue-blocks' ),
					'svg'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
				),
				'x'         => array(
					'label' => __( 'X', 'vue-blocks' ),
					'svg'   => '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
				),
				'linkedin'  => array(
					'label' => __( 'LinkedIn', 'vue-blocks' ),
					'svg'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>',
				),
			);
			foreach ( $vb_socials_nav as $vb_slug => $vb_meta ) :
				$vb_url = get_theme_mod( "vb_social_{$vb_slug}", '' );
				if ( '' === $vb_url ) {
					continue;
				}
				?>
				<a href="<?php echo esc_url( $vb_url ); ?>" class="soc" title="<?php echo esc_attr( $vb_meta['label'] ); ?>" aria-label="<?php echo esc_attr( $vb_meta['label'] ); ?>" target="_blank" rel="noopener">
					<?php echo $vb_meta['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php endforeach; ?>
		</div>

		<button class="nav-hamburger" id="safe-midia-navHamburger" aria-label="Menu">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
				<line x1="3" y1="6" x2="21" y2="6"/>
				<line x1="3" y1="12" x2="21" y2="12"/>
				<line x1="3" y1="18" x2="21" y2="18"/>
			</svg>
		</button>

		<button class="btn-sub" type="button">
			<?php esc_html_e( 'Assinar Newsletter', 'vue-blocks' ); ?>
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
		</button>

	</nav>
</header>

<nav class="mobile-menu" id="safe-midia-mobileMenu">
	<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => '',
				'fallback_cb'    => false,
				'depth'          => 2,
				'items_wrap'     => '%3$s',
				'link_class'     => '',
				'item_class'     => '',
			)
		);
		?>
	<?php else : ?>
		<a href="#" class="active">Mercado</a>
		<a href="#">Auto</a>
		<a href="#">Saúde</a>
		<a href="#">Vida</a>
		<a href="#">Empresarial</a>
		<a href="#">Opinião</a>
	<?php endif; ?>
</nav>