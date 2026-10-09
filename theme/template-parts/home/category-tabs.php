<?php
/**
 * Vue Blocks — Category Tabs (Safe Mídia home section).
 *
 * 4 most-populated categories, each rendered as a tab; each tab
 * shows 4 latest posts in that category. Hidden per FR-004 if fewer
 * than 2 categories exist.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_tabs_cats = get_categories(
	array(
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 4,
		'hide_empty' => true,
		'exclude'    => array( get_cat_ID( 'Uncategorized' ) ),
	)
);
if ( ! is_array( $vb_tabs_cats ) ) {
	$vb_tabs_cats = array();
}
if ( count( $vb_tabs_cats ) < 2 ) {
	return;
}

$vb_tabs_first = $vb_tabs_cats[0];
$vb_tabs_rest  = array_slice( $vb_tabs_cats, 1 );
?>

<section class="tabs-section">
	<div class="vb-container">
		<nav class="tabs-nav" role="tablist" aria-label="<?php esc_attr_e( 'Categorias', 'vue-blocks' ); ?>">
			<a href="<?php echo esc_url( get_category_link( $vb_tabs_first->term_id ) ); ?>" class="tab-btn active" role="tab" aria-selected="true">
				<?php echo esc_html( $vb_tabs_first->name ); ?>
			</a>
			<?php foreach ( $vb_tabs_rest as $vb_cat ) : ?>
				<a href="<?php echo esc_url( get_category_link( $vb_cat->term_id ) ); ?>" class="tab-btn" role="tab">
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