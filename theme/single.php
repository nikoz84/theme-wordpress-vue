<?php
/**
 * O template usado para exibir um único post (single.php)
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

			get_template_part( 'template-parts/content' );

			the_post_navigation(
				array(
					'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Anterior:', 'vue-blocks' ) . '</span> <span class="nav-title">%title</span>',
					'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Próximo:', 'vue-blocks' ) . '</span> <span class="nav-title">%title</span>',
				)
			);

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
