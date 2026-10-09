<?php
/**
 * Vue Blocks — "Últimas Notícias" list layout (layouts-html/02).
 *
 * Page hero + category pills/sort bar + list + pagination + sidebar
 * (newsletter, Mais Lidas, Publicidade). Used by page-noticias.php
 * (custom query) and archive.php (main query).
 *
 * @param array $args {
 *     @type WP_Query $query       Query to list.
 *     @type string   $title       Page title.
 *     @type string   $description Optional description (HTML allowed).
 *     @type int      $active_cat  Active category ID (0 = "Todas").
 * }
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_query  = $args['query'];
$vb_active = (int) ( $args['active_cat'] ?? 0 );
$vb_sort   = vb_current_sort();
?>

<div class="page-hero">
	<div class="page-hero-inner">
		<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Navegação', 'vue-blocks' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'vue-blocks' ); ?></a>
			<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
			<?php if ( $vb_active || ! empty( $args['is_archive'] ) ) : ?>
				<a href="<?php echo esc_url( vb_news_url() ); ?>"><?php esc_html_e( 'Últimas Notícias', 'vue-blocks' ); ?></a>
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
			<?php endif; ?>
			<span aria-current="page"><?php echo esc_html( $args['title'] ); ?></span>
		</nav>
		<h1 class="page-title"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<div class="page-meta"><?php echo wp_kses_post( $args['description'] ); ?></div>
		<?php endif; ?>
	</div>
</div>

<div class="filter-bar">
	<div class="filter-inner">
		<nav class="filter-pills" aria-label="<?php esc_attr_e( 'Editorias', 'vue-blocks' ); ?>">
			<a href="<?php echo esc_url( vb_news_url() ); ?>" class="pill<?php echo $vb_active ? '' : ' active'; ?>"<?php echo $vb_active ? '' : ' aria-current="page"'; ?>><?php esc_html_e( 'Todas', 'vue-blocks' ); ?></a>
			<?php foreach ( vb_filter_categories() as $vb_cat ) : ?>
				<?php $vb_is_active = (int) $vb_cat->term_id === $vb_active; ?>
				<a href="<?php echo esc_url( get_category_link( $vb_cat ) ); ?>" class="pill<?php echo $vb_is_active ? ' active' : ''; ?>"<?php echo $vb_is_active ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $vb_cat->name ); ?></a>
			<?php endforeach; ?>
		</nav>
		<span class="filter-sep" aria-hidden="true"></span>
		<form class="sort-form" method="get" action="<?php echo esc_url( remove_query_arg( 'ordem', get_pagenum_link( 1 ) ) ); ?>">
			<label class="screen-reader-text" for="vb-sort"><?php esc_html_e( 'Ordenar por', 'vue-blocks' ); ?></label>
			<select class="sort-select" id="vb-sort" name="ordem" data-autosubmit>
				<?php foreach ( vb_sort_options() as $vb_key => $vb_label ) : ?>
					<option value="<?php echo esc_attr( $vb_key ); ?>" <?php selected( $vb_sort, $vb_key ); ?>><?php echo esc_html( $vb_label ); ?></option>
				<?php endforeach; ?>
			</select>
			<noscript><button type="submit" class="pill"><?php esc_html_e( 'Ordenar', 'vue-blocks' ); ?></button></noscript>
		</form>
	</div>
</div>

<div class="list-layout">
	<div class="list-main">
		<?php if ( $vb_query->have_posts() ) : ?>
			<div class="news-list">
				<?php
				while ( $vb_query->have_posts() ) :
					$vb_query->the_post();
					get_template_part( 'template-parts/news-list/item' );
				endwhile;
				?>
			</div>
			<?php vb_news_pagination( $vb_query ); ?>
		<?php else : ?>
			<div class="archive-empty">
				<h2 class="sec-title"><?php esc_html_e( 'Nenhuma notícia encontrada', 'vue-blocks' ); ?></h2>
				<p><?php esc_html_e( 'Tente outra editoria ou volte mais tarde.', 'vue-blocks' ); ?></p>
			</div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>

	<aside class="sidebar" aria-label="<?php esc_attr_e( 'Barra lateral', 'vue-blocks' ); ?>">
		<?php get_template_part( 'template-parts/widgets/newsletter' ); ?>
		<?php get_template_part( 'template-parts/widgets/most-read' ); ?>
		<?php get_template_part( 'template-parts/widgets/ad' ); ?>
	</aside>
</div>
