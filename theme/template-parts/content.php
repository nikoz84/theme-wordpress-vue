<?php
/**
 * Vue Blocks — Content template part.
 *
 * Used by the news-grid archive loop AND by `single.php` for the
 * single-post editorial layout (per Contracts/single-post.contract.md).
 *
 * In single-post context: emits a Safe Mídia post-hero (featured image
 * + category chip + byline + Merriweather title) followed by the body
 * content in `.entry-content` (Inter typography).
 *
 * In archive-loop context: emits a Safe Mídia `.ncard` (image +
 * category chip + title link).
 *
 * @package Vue_Blocks
 */
?>

<?php if ( is_singular( 'post' ) ) : ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'post' ); ?>>

		<header class="post-hero">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large', array( 'class' => 'post-hero-image' ) ); ?>
			<?php else : ?>
				<img class="post-hero-image" src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
			<?php endif; ?>
			<div class="post-meta">
				<?php
				$vb_post_cats = get_the_category();
				if ( ! empty( $vb_post_cats ) ) :
					?>
					<span class="post-cat-chip"><?php echo esc_html( $vb_post_cats[0]->name ); ?></span>
				<?php endif; ?>
				<span class="post-byline">
					<?php
					/* translators: %s: post author display name. */
					printf( esc_html__( 'por %s', 'vue-blocks' ), esc_html( get_the_author() ) );
					?>
					· <?php echo esc_html( get_the_date() ); ?>
				</span>
			</div>
			<h1 class="post-title"><?php the_title(); ?></h1>
		</header>

		<div class="entry-content">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Páginas:', 'vue-blocks' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>

		<footer class="entry-footer">
			<?php vb_entry_footer(); ?>
		</footer>

	</article>

<?php else : ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'ncard' ); ?>>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="ncard-img">
				<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
					<?php the_post_thumbnail( 'vb-card', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
				</a>
				<span class="ncard-cat"><?php echo esc_html( vb_first_category() ); ?></span>
			</div>
		<?php else : ?>
			<div class="ncard-img">
				<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
					<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
				</a>
				<span class="ncard-cat"><?php echo esc_html( vb_first_category() ); ?></span>
			</div>
		<?php endif; ?>
		<a href="<?php the_permalink(); ?>" class="ncard-title"><?php the_title(); ?></a>

	</article>

<?php endif; ?>