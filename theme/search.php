<?php
/**
 * Vue Blocks — Search results template.
 *
 * Refined to the Safe Mídia visual language per US3.
 *
 * @package Vue_Blocks
 */

get_header();
?>

<main id="primary" class="site-main vb-layout vb-search">

	<header class="page-header">
		<h1 class="sec-title">
			<?php
			/* translators: %s: search query string. */
			printf( esc_html__( 'Resultados da busca por: &ldquo;%s&rdquo;', 'vue-blocks' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="news-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content' );
			endwhile;
			?>
		</div>

		<?php vb_pagination(); ?>

	<?php else : ?>

		<div class="archive-empty">
			<h2 class="sec-title"><?php esc_html_e( 'Nenhum resultado encontrado', 'vue-blocks' ); ?></h2>
			<p><?php esc_html_e( 'Tente uma busca diferente ou volte mais tarde.', 'vue-blocks' ); ?></p>
			<?php get_search_form(); ?>
		</div>

	<?php endif; ?>

</main>

<?php
get_footer();