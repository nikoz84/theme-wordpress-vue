<?php
/**
 * Vue Blocks — Category Tabs (Safe Mídia home section).
 *
 * One tab per category in the primary menu (same order, empty
 * categories included — vb_filter_categories()); falls back to the
 * most-used categories when the menu has none. The active tab is the
 * first category that has posts and shows its 4 latest posts. Hidden
 * per FR-004 if fewer than 2 categories exist.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_tabs_cats = array_values( (array) vb_filter_categories() );
if ( count( $vb_tabs_cats ) < 2 ) {
	return;
}

// Active tab: first category with posts (so the grid isn't empty).
$vb_tabs_first = $vb_tabs_cats[0];
foreach ( $vb_tabs_cats as $vb_cat ) {
	if ( (int) $vb_cat->count > 0 ) {
		$vb_tabs_first = $vb_cat;
		break;
	}
}
?>

<section class="tabs-section">
	<div class="vb-container">
		<nav class="tabs-nav" role="tablist" aria-label="<?php esc_attr_e( 'Categorias', 'vue-blocks' ); ?>">
			<?php foreach ( $vb_tabs_cats as $vb_cat ) : ?>
				<?php $vb_is_active = ( (int) $vb_cat->term_id === (int) $vb_tabs_first->term_id ); ?>
				<a href="<?php echo esc_url( get_category_link( $vb_cat->term_id ) ); ?>" class="tab-btn<?php echo $vb_is_active ? ' active' : ''; ?>" role="tab" aria-selected="<?php echo $vb_is_active ? 'true' : 'false'; ?>">
					<?php echo esc_html( $vb_cat->name ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<div class="tabs-grid">
			<?php
			$vb_tabs_q = new WP_Query(
				array(
					'cat'           => $vb_tabs_first->term_id,
					'posts_per_page' => 4,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
			if ( $vb_tabs_q->have_posts() ) :
				while ( $vb_tabs_q->have_posts() ) :
					$vb_tabs_q->the_post();
					?>
					<a href="<?php the_permalink(); ?>" class="tab-card">
						<div class="tab-card-img-wrap">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'vb-card', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
							<?php else : ?>
								<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
							<?php endif; ?>
						</div>
						<div class="tab-card-body">
							<div class="tab-card-cat"><?php echo esc_html( vb_first_category() ); ?></div>
							<div class="tab-card-title"><?php the_title(); ?></div>
						</div>
					</a>
				<?php endwhile;
			endif;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>