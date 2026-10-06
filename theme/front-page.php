<?php
/**
 * Front Page — Safe Mídia visual layout (full 12 sections).
 *
 * Renders the four v1 sections (navbar via header.php, hero, news
 * grid, footer via footer.php) PLUS the eight v2 sections (newsletter
 * compact, Para o Segurado, newsletter grande, Análise de Mercado,
 * category tabs, Mais Lidas, Boletim Regulatório, Colunistas) in the
 * documented order. See `specs/005-home-v2-and-page-templates/contracts/home-sections.contract.md`.
 *
 * @package Vue_Blocks
 */

get_header();
?>

<main id="primary" class="site-main vb-layout">

	<?php
	/*
	 * Section 2 — Hero.
	 * The hero template part itself consumes the featured + sidebar
	 * posts via its own WP_Queries.
	 */
	get_template_part( 'template-parts/hero/hero' );

	/*
	 * Section 3 — News grid.
	 * The news grid excludes the featured + sidebar posts via the
	 * `vb_news_grid_excluded_post_ids` filter. We populate that filter
	 * from the hero's queries (see theme/inc/template-tags.php for
	 * the cache of the most-recent post IDs).
	 */
	get_template_part( 'template-parts/news/news-grid' );

	/*
	 * Sections 4–11 — the eight v2 home sections (per
	 * contracts/home-sections-contract.md). Each template part is
	 * self-contained: it sources its own data and renders nothing when
	 * the data is empty (per FR-004).
	 */
	get_template_part( 'template-parts/home/newsletter-compact' );
	get_template_part( 'template-parts/home/segurado' );
	get_template_part( 'template-parts/home/newsletter-grande' );
	get_template_part( 'template-parts/home/analise' );
	get_template_part( 'template-parts/home/category-tabs' );
	get_template_part( 'template-parts/home/mais-lidas' );
	get_template_part( 'template-parts/home/boletim' );
	get_template_part( 'template-parts/home/colunistas' );

	/*
	 * Section 12 — Footer (rendered by footer.php's get_template_part).
	 * The Safe Mídia footer is theme/template-parts/footer/site-footer.php.
	 */
	?>

</main>

<?php
get_footer();