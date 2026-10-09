<?php
/**
 * Vue Blocks — "Relacionados" sidebar widget.
 *
 * @param array $args { @type WP_Post[] $posts Posts to list. }
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $args['posts'] ) ) {
	return;
}
?>
<div class="sidebar-block">
	<div class="sidebar-title"><?php esc_html_e( 'Relacionados', 'vue-blocks' ); ?></div>
	<?php foreach ( $args['posts'] as $vb_post ) : ?>
		<a href="<?php echo esc_url( get_permalink( $vb_post ) ); ?>" class="rel-item">
			<div class="rel-thumb">
				<?php if ( has_post_thumbnail( $vb_post ) ) : ?>
					<?php echo get_the_post_thumbnail( $vb_post, 'thumbnail', array( 'alt' => '' ) ); ?>
				<?php else : ?>
					<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
				<?php endif; ?>
			</div>
			<div>
				<div class="rel-cat"><?php echo esc_html( vb_first_category( $vb_post->ID ) ); ?></div>
				<div class="rel-title"><?php echo esc_html( get_the_title( $vb_post ) ); ?></div>
			</div>
		</a>
	<?php endforeach; ?>
</div>
