<?php
/**
 * Vue Blocks — Safe Mídia Hero (template part).
 *
 * Renders the homepage hero region:
 *   - main featured sticky post (else most-recent published post)
 *   - 4-post sidebar on the right
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Main featured: sticky post, else most recent.
$main_q = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 1,
		'ignore_sticky_posts' => 0,
		'post__in'            => get_option( 'sticky_posts' ),
	)
);
if ( empty( $main_q->posts ) ) {
	wp_reset_postdata();
	$main_q = new WP_Query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 1,
		)
	);
}
$main_post = $main_q->have_posts() ? $main_q->next_post() : null;
wp_reset_postdata();

// Sidebar: next 4 most-recent posts excluding the featured.
$sidebar_exclude = array();
if ( $main_post ) {
	$sidebar_exclude[] = $main_post->ID;
}
$sidebar_q = new WP_Query(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 4,
		'post__not_in'   => $sidebar_exclude,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
?>

<section class="hero">
	<?php if ( $main_post ) : ?>
		<a href="<?php echo esc_url( get_permalink( $main_post ) ); ?>" class="hero-main">
			<?php if ( has_post_thumbnail( $main_post ) ) : ?>
				<?php echo get_the_post_thumbnail( $main_post, 'vb-card', array( 'alt' => esc_attr( get_the_title( $main_post ) ) ) ); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
			<?php endif; ?>
			<div class="hero-overlay"></div>
			<div class="hero-content">
				<div class="hero-meta-bar">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
					<?php
					/* translators: %s: category name. */
					printf( '%s / <span class="hero-cat">%s</span>',
						esc_html( vb_time_ago_short() ),
						esc_html( vb_first_category( $main_post ) )
					);
					?>
				</div>
				<div class="hero-title-wrap">
					<h1 class="hero-title"><?php echo esc_html( get_the_title( $main_post ) ); ?></h1>
				</div>
			</div>
		</a>
	<?php endif; ?>

	<aside>
		<?php if ( $sidebar_q->have_posts() ) : ?>
			<?php
			while ( $sidebar_q->have_posts() ) :
				$sidebar_q->the_post();
				?>
				<a href="<?php the_permalink(); ?>" class="spost">
					<div class="spost-thumb">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'thumbnail', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
						<?php else : ?>
							<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
						<?php endif; ?>
					</div>
					<div>
						<div class="spost-cat">
							<?php echo esc_html( vb_first_category() ); ?> · <?php echo esc_html( vb_time_ago_short() ); ?>
						</div>
						<div class="spost-title"><?php the_title(); ?></div>
					</div>
				</a>
			<?php endwhile; ?>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</aside>
</section>