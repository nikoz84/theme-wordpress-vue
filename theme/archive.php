<?php
/**
 * Vue Blocks — Archive template (category / tag / author / date).
 *
 * Same "Últimas Notícias" list layout as page-noticias.php, with the
 * current category pill highlighted.
 *
 * @package Vue_Blocks
 */

get_header();

global $wp_query;
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
?>

<main id="primary" class="site-main vb-news-page vb-archive">
	<?php
	get_template_part(
		'template-parts/news-list/layout',
		null,
		array(
			'query'       => $wp_query,
			'title'       => wp_strip_all_tags( get_the_archive_title() ),
			'description' => get_the_archive_description(),
			'active_cat'  => is_category() ? get_queried_object_id() : 0,
			'is_archive'  => true,
		)
	);
	?>
</main>

<?php
get_footer();
