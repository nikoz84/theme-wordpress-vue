<?php
/**
 * Vue Blocks — Archive template (category / tag / author / date).
 *
 * Refined to the Safe Mídia visual language per Contracts/single-post.contract.md
 * US3. Uses Safe Mídia `.news-grid` markup wrapping the refined
 * `template-parts/content.php` ncard variant.
 *
 * @package Vue_Blocks
 */

get_header();
?>

<main id="primary" class="site-main vb-layout vb-archive">

	<?php if ( have_posts() ) : ?>

		<header class="page-header">
			<h1 class="sec-title"><?php the_archive_title(); ?></h1>
			<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
		</header>

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
			<h2 class="sec-title"><?php esc_html_e( 'Nenhum conteúdo encontrado', 'vue-blocks' ); ?></h2>
			<p><?php esc_html_e( 'Tente uma busca ou volte mais tarde.', 'vue-blocks' ); ?></p>
			<?php get_search_form(); ?>
		</div>

	<?php endif; ?>

</main>

<?php
get_footer();