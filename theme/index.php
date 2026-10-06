<?php
/**
 * O template mais genérico usado quando nenhum template mais específico existe.
 * index.php é o ÚNICO arquivo de template obrigatório em um tema WordPress.
 * https://developer.wordpress.org/themes/core-concepts/templates/
 *
 * @package Vue_Blocks
 */

get_header();
?>

<div class="vb-layout">

	<main id="primary" class="site-main">

		<?php if ( have_posts() ) : ?>

			<?php if ( is_home() && ! is_front_page() ) : ?>
				<header class="page-header">
					<h1 class="page-title"><?php single_post_title(); ?></h1>
				</header>
			<?php endif; ?>

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
