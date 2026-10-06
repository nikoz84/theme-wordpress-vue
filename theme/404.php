<?php
/**
 * Vue Blocks — 404 (Not found) template.
 *
 * Refined to the Safe Mídia visual language per US3.
 *
 * @package Vue_Blocks
 */

get_header();
?>

<main id="primary" class="site-main vb-layout vb-404">

	<section class="archive-empty vb-404-block">
		<h1 class="sec-title"><?php esc_html_e( 'Página não encontrada', 'vue-blocks' ); ?></h1>
		<p class="vb-404-msg">
			<?php esc_html_e( 'A página que você procura não existe ou foi movida. Tente a pesquisa abaixo ou volte à home.', 'vue-blocks' ); ?>
		</p>
		<div class="vb-404-search">
			<?php get_search_form(); ?>
		</div>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-see-more">
			<?php esc_html_e( 'Voltar à home', 'vue-blocks' ); ?> &rarr;
		</a>
	</section>

</main>

<?php
get_footer();