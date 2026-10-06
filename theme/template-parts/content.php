<?php
/**
 * Template part usado para exibir um post no Loop.
 * https://developer.wordpress.org/themes/core-concepts/templates/
 *
 * @package Vue_Blocks
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'vb-post-card' ); ?>>

	<?php if ( has_post_thumbnail() && ! is_singular() ) : ?>
		<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'vb-card', array( 'class' => 'vb-post-card__thumb' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="vb-post-card__body">

		<header class="entry-header">
			<div class="vb-post-card__meta">
				<?php vb_posted_on(); ?>
			</div>

			<?php
			if ( is_singular() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title(
					sprintf( '<h2 class="entry-title vb-post-card__title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ),
					'</a></h2>'
				);
			endif;
			?>
		</header>

		<?php if ( is_singular() && has_post_thumbnail() ) : ?>
			<div class="entry-thumbnail">
				<?php the_post_thumbnail( 'large' ); ?>
			</div>
		<?php endif; ?>

		<div class="entry-content <?php echo is_singular() ? '' : 'vb-post-card__excerpt'; ?>">
			<?php
			if ( is_singular() ) {
				the_content();

				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . esc_html__( 'Páginas:', 'vue-blocks' ),
						'after'  => '</div>',
					)
				);
			} else {
				the_excerpt();
			}
			?>
		</div>

		<?php if ( ! is_singular() ) : ?>
			<a class="vb-btn vb-btn--ghost" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Ler mais', 'vue-blocks' ); ?> &rarr;
			</a>
		<?php endif; ?>

		<?php if ( is_singular() ) : ?>
			<footer class="entry-footer">
				<?php vb_entry_footer(); ?>
			</footer>
		<?php endif; ?>

	</div>
</article>
