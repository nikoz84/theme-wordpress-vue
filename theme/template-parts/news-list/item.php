<?php
/**
 * Vue Blocks — News list row (`.list-item`), used inside the loop.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_cat = vb_first_category();
?>
<a href="<?php the_permalink(); ?>" class="list-item">
	<div class="list-thumb">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'vb-card', array( 'alt' => '' ) ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
		<?php endif; ?>
		<?php if ( $vb_cat ) : ?>
			<span class="list-cat-badge"><?php echo esc_html( $vb_cat ); ?></span>
		<?php endif; ?>
	</div>
	<div class="list-body">
		<div class="list-body-top">
			<h2 class="list-title"><?php the_title(); ?></h2>
			<?php if ( has_excerpt() || get_the_content() ) : ?>
				<p class="list-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="list-foot">
			<span class="list-author">
				<?php
				/* translators: %s: author name */
				echo esc_html( sprintf( __( 'Por %s', 'vue-blocks' ), vb_author_public_name() ) );
				?>
			</span>
			<span class="list-foot-sep" aria-hidden="true"></span>
			<span class="list-date">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j M. Y' ) ); ?></time>
			</span>
			<span class="list-foot-sep" aria-hidden="true"></span>
			<span class="list-time">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
				<?php echo esc_html( get_the_time( 'H:i' ) ); ?>
			</span>
		</div>
	</div>
</a>
