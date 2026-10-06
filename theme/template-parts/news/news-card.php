<?php
/**
 * Vue Blocks — Safe Mídia News Card (template part).
 *
 * Single news card markup, rendered inside the news-grid.
 *
 * Expected to be called from inside a `WP_Query` loop. Reads
 * `$post` from the enclosing scope.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<a href="<?php the_permalink(); ?>" class="ncard">
	<div class="ncard-img">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'vb-card', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
		<?php endif; ?>
		<span class="ncard-cat"><?php echo esc_html( vb_first_category() ); ?></span>
	</div>
	<div class="ncard-title"><?php the_title(); ?></div>
</a>