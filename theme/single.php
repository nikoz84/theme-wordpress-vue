<?php
/**
 * Vue Blocks — Single post (layouts-html/03 - Interna das notícias).
 *
 * Header (breadcrumb, time/category badge, title, lead), author/date/
 * reading time, share bar, hero image, then body + sidebar:
 *   body:    content, Segurado section, tags, editor box, "Leia também" (2)
 *   sidebar: newsletter, Mais Lidas, Relacionados (4), Publicidade
 *
 * @package Vue_Blocks
 */

get_header();

while ( have_posts() ) :
	the_post();

	$vb_post_id   = get_the_ID();
	$vb_author_id = (int) get_the_author_meta( 'ID' );
	$vb_author    = vb_author_public_name( $vb_author_id );
	$vb_job       = vb_author_job_title( $vb_author_id );
	$vb_bio       = get_the_author_meta( 'description', $vb_author_id );
	$vb_category  = vb_first_category();
	// "Leia também" picks first (tags → category → recent); "Relacionados" gets the rest.
	$vb_read_also = vb_get_read_also_posts( $vb_post_id, 2 );
	$vb_related   = vb_get_related_posts( $vb_post_id, 4, wp_list_pluck( $vb_read_also, 'ID' ) );
	?>

<main id="primary" class="site-main vb-article">

	<header class="article-header">
		<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Navegação', 'vue-blocks' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Início', 'vue-blocks' ); ?></a>
			<span class="breadcrumb-sep" aria-hidden="true"></span>
			<a href="<?php echo esc_url( vb_news_url() ); ?>"><?php esc_html_e( 'Últimas Notícias', 'vue-blocks' ); ?></a>
			<?php if ( $vb_category ) : ?>
				<span class="breadcrumb-sep" aria-hidden="true"></span>
				<span aria-current="page"><?php echo esc_html( $vb_category ); ?></span>
			<?php endif; ?>
		</nav>

		<div class="article-cat">
			<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
			<?php echo esc_html( vb_time_ago_short() . ( $vb_category ? ' · ' . $vb_category : '' ) ); ?>
		</div>

		<h1 class="article-title"><?php the_title(); ?></h1>

		<?php if ( has_excerpt() ) : ?>
			<p class="article-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</header>

	<div class="article-meta">
		<div class="meta-author">
			<?php echo vb_author_avatar( $vb_author_id, 'meta-avatar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<div>
				<div class="meta-name"><?php echo esc_html( $vb_author ); ?></div>
				<?php if ( $vb_job ) : ?>
					<div class="meta-role"><?php echo esc_html( $vb_job ); ?></div>
				<?php endif; ?>
			</div>
		</div>
		<span class="meta-divider" aria-hidden="true"></span>
		<div class="meta-info">
			<div class="meta-date">
				<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</div>
			<div class="meta-readtime">
				<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
				<?php
				/* translators: %d: minutes */
				echo esc_html( sprintf( __( 'Leitura de %d min', 'vue-blocks' ), vb_reading_time() ) );
				?>
			</div>
		</div>
	</div>

	<div class="share-bar">
		<span class="share-label"><?php esc_html_e( 'Compartilhar', 'vue-blocks' ); ?></span>
		<?php
		$vb_share_icons = array(
			'facebook' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>',
			'x'        => '<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
			'linkedin' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>',
			'whatsapp' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
		);
		foreach ( vb_share_links() as $vb_net => $vb_share ) :
			?>
			<a class="share-btn" href="<?php echo esc_url( $vb_share['url'] ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $vb_share['label'] ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: network */ __( 'Compartilhar no %s', 'vue-blocks' ), $vb_share['label'] ) ); ?>">
				<?php echo $vb_share_icons[ $vb_net ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
			</a>
		<?php endforeach; ?>
		<button type="button" class="share-btn copy" data-copy-link="<?php echo esc_url( get_permalink() ); ?>" data-copied-label="<?php esc_attr_e( 'Link copiado!', 'vue-blocks' ); ?>">
			<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
			<span><?php esc_html_e( 'Copiar link', 'vue-blocks' ); ?></span>
		</button>
	</div>

	<figure class="article-hero">
		<div class="article-hero-img">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large' ); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
			<?php endif; ?>
		</div>
		<?php $vb_caption = has_post_thumbnail() ? wp_get_attachment_caption( get_post_thumbnail_id() ) : ''; ?>
		<?php if ( $vb_caption ) : ?>
			<figcaption class="article-hero-caption"><?php echo esc_html( $vb_caption ); ?></figcaption>
		<?php endif; ?>
	</figure>

	<div class="article-layout">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'article-body' ); ?>>

			<div class="article-content entry-content">
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

			<?php
			// Segurado section — display or edit based on user capabilities.
			if ( current_user_can( 'edit_post', $vb_post_id ) ) {
				get_template_part( 'template-parts/segurado/segurado-edit' );
			} else {
				get_template_part( 'template-parts/segurado/segurado-display' );
			}
			?>

			<?php $vb_tags = get_the_tags(); ?>
			<?php if ( $vb_tags ) : ?>
				<div class="article-tags">
					<span class="tags-label"><?php esc_html_e( 'Tags:', 'vue-blocks' ); ?></span>
					<?php foreach ( $vb_tags as $vb_tag ) : ?>
						<a href="<?php echo esc_url( get_tag_link( $vb_tag ) ); ?>" class="tag" rel="tag"><?php echo esc_html( $vb_tag->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="author-box">
				<?php echo vb_author_avatar( $vb_author_id, 'author-box-avatar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<div class="author-box-info">
					<div class="author-box-name"><?php echo esc_html( $vb_author ); ?></div>
					<div class="author-box-role"><?php echo esc_html( implode( ' · ', array_filter( array( $vb_job, get_bloginfo( 'name' ) ) ) ) ); ?></div>
					<?php if ( $vb_bio ) : ?>
						<p class="author-box-bio"><?php echo esc_html( $vb_bio ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $vb_read_also ) : ?>
				<section class="read-also">
					<h2 class="read-also-title"><?php esc_html_e( 'Leia também', 'vue-blocks' ); ?></h2>
					<div class="read-also-grid">
						<?php foreach ( $vb_read_also as $vb_post ) : ?>
							<a href="<?php echo esc_url( get_permalink( $vb_post ) ); ?>" class="ralso-card">
								<div class="ralso-thumb">
									<?php if ( has_post_thumbnail( $vb_post ) ) : ?>
										<?php echo get_the_post_thumbnail( $vb_post, 'thumbnail', array( 'alt' => '' ) ); ?>
									<?php else : ?>
										<img src="<?php echo esc_url( vb_placeholder_image() ); ?>" alt="" />
									<?php endif; ?>
								</div>
								<div>
									<div class="ralso-cat"><?php echo esc_html( vb_first_category( $vb_post->ID ) ); ?></div>
									<div class="ralso-title"><?php echo esc_html( get_the_title( $vb_post ) ); ?></div>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</article>

		<aside class="sidebar" aria-label="<?php esc_attr_e( 'Barra lateral', 'vue-blocks' ); ?>">
			<?php
			get_template_part(
				'template-parts/widgets/newsletter',
				null,
				array(
					'title'     => __( 'Fique por dentro', 'vue-blocks' ),
					'desc'      => __( 'Receba as principais notícias do mercado de seguros todo dia útil de manhã.', 'vue-blocks' ),
					'show_name' => false,
				)
			);
			get_template_part( 'template-parts/widgets/most-read' );
			get_template_part( 'template-parts/widgets/related', null, array( 'posts' => $vb_related ) );
			get_template_part( 'template-parts/widgets/ad' );
			?>
		</aside>
	</div>

</main>

	<?php
endwhile;

get_footer();
