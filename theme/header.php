<?php
/**
 * Header — Safe Mídia visual layout.
 *
 * The Safe Mídia navbar is a self-contained template part; the wrapper
 * `<header>` it emits already includes the `.navbar` and `.nav-inner`
 * classes the Safe Mídia CSS binds to.
 *
 * @package Vue_Blocks
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>
		/* Evita "flash" de tema antes do Vue montar (preserved from prior version) */
		(function () {
			try {
				var saved = localStorage.getItem( 'vb-theme' );
				if ( saved === 'dark' || ( ! saved && window.matchMedia( '(prefers-color-scheme: dark)' ).matches ) ) {
					document.documentElement.setAttribute( 'data-theme', 'dark' );
				}
			} catch ( e ) {}
		})();
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Ir para o conteúdo', 'vue-blocks' ); ?></a>

	<?php get_template_part( 'template-parts/header/navbar' ); ?>

	<div id="content" class="site-content">