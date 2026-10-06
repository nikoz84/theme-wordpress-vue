<?php
/**
 * O template usado para exibir arquivos (categorias, tags, autor, data).
 * https://developer.wordpress.org/themes/templates/template-hierarchy/
 *
 * @package Vue_Blocks
 */

get_header();
?>

<div class="vb-layout">

	<main id="primary" class="site-main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<h1 class="page-title"><?php the_archive_title(); ?></h1>
				<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
			</header>

			<div class="vb-posts-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content' );
				endwhile;
				?>
			</div>

			<?php vb_pagination(); ?>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content', 'none' ); ?>

		<?php endif; ?>

	</main>

	<?php get_sidebar(); ?>

</div>

<?php
get_footer();
