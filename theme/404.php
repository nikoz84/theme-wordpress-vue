<?php
/**
 * O template usado para exibir páginas 404 (não encontrado).
 *
 * @package Vue_Blocks
 */

get_header();
?>

<div class="vb-layout">
	<main id="primary" class="site-main">
		<section class="error-404 not-found">
			<header class="page-header">
				<h1 class="page-title"><?php esc_html_e( 'Ops! Página não encontrada.', 'vue-blocks' ); ?></h1>
			</header>

			<div class="page-content">
				<p><?php esc_html_e( 'O endereço que você tentou acessar não existe. Que tal pesquisar por algo?', 'vue-blocks' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		</section>
	</main>

	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
