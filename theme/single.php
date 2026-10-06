<?php
/**
 * Vue Blocks — Single post template.
 *
 * Renders the Safe Mídia editorial layout per Q2 / Contracts/single-post-contract.md:
 *   - post-hero (featured image + category chip + byline + title)
 *   - entry-content (the body)
 *   - related-posts rail ("Leia também", 3 most recent in same category)
 *
 * The refined `template-parts/content.php` (T013) emits the post-hero
 * + entry-content; this template wraps it in a single layout and
 * appends the related-posts rail.
 *
 * @package Vue_Blocks
 */

get_header();
?>

<main id="primary" class="site-main vb-layout vb-single">

	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content' );

		// Related posts rail — 3 most recent posts in the same category,
		// excluding the current post. Per data-model E3.
		$vb_related_cats = wp_get_post_terms( get_the_ID(), 'category', array( 'fields' => 'ids' ) );
		if ( ! empty( $vb_related_cats ) && ! is_wp_error( $vb_related_cats ) ) {
			$vb_related_q = new WP_Query(
				array(
					'category__in'   => $vb_related_cats,
					'post__not_in'    => array( get_the_ID() ),
					'posts_per_page'  => 3,
					'orderby'         => 'date',
					'order'           => 'DESC',
					'ignore_sticky_posts' => 1,
				)
			);
			if ( $vb_related_q->have_posts() ) :
				?>
				<section class="related-posts">
					<h2 class="related-posts-title"><?php esc_html_e( 'Leia também', 'vue-blocks' ); ?></h2>
					<div class="related-grid">
						<?php
						while ( $vb_related_q->have_posts() ) :
							$vb_related_q->the_post();
							?>
							<a href="<?php the_permalink(); ?>" class="related-card">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'vb-card', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
								<?php else : ?>
									<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
								<?php endif; ?>
								<span class="related-card-cat"><?php echo esc_html( vb_first_category() ); ?></span>
								<span class="related-card-title"><?php the_title(); ?></span>
							</a>
						<?php endwhile; ?>
					</div>
				</section>
				<?php
			endif;
			wp_reset_postdata();
		}
	endwhile;
	?>

</main>

<?php
get_footer();