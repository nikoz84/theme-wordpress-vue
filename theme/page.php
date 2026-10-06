<?php
/**
 * O template usado para exibir páginas estáticas (page.php)
 *
 * @package Vue_Blocks
 */

get_header();
?>

<div class="vb-layout">

	<main id="primary" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'page' );

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}

		endwhile;
		?>

	</main>

	<?php get_sidebar(); ?>

</div>

<?php
get_footer();
