<?php
/**
 * Front Page — Safe Mídia visual layout.
 *
 * Renders the four core Safe Mídia sections (per
 * contracts/theme-package.manifest.md and the spec's Out-of-Scope
 * list):
 *   1. Navbar (via header.php's get_template_part call to
 *      theme/template-parts/header/navbar.php)
 *   2. Hero (theme/template-parts/hero/hero.php)
 *   3. News grid (theme/template-parts/news/news-grid.php)
 *   4. Footer (via footer.php's get_template_part call to
 *      theme/template-parts/footer/site-footer.php)
 *
 * The seven non-core sections (newsletter compact, Para o Segurado,
 * Boletim Regulatório, Colunistas, Análise de Mercado, category tabs,
 * Mais Lidas da Semana, newsletter grande) are out of scope for v1.
 *
 * @package Vue_Blocks
 */

get_header();
?>

<main id="primary" class="site-main vb-layout">

	<?php get_template_part( 'template-parts/hero/hero' ); ?>

	<?php get_template_part( 'template-parts/news/news-grid' ); ?>

</main>

<?php
get_footer();