<?php
/**
 * Template Name: Últimas Notícias
 *
 * Vue Blocks — news list (layouts-html/02 - Listagem das notícias).
 * Applied automatically to the page with slug `noticias`, or chosen
 * as a page template in the editor.
 *
 * @package Vue_Blocks
 */

get_header();

$vb_paged      = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$vb_news_query = new WP_Query(
	array_merge(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => (int) get_option( 'posts_per_page', 10 ),
			'paged'               => $vb_paged,
			'ignore_sticky_posts' => true,
		),
		vb_sort_query_args( vb_current_sort() )
	)
);

$vb_description = has_excerpt()
	? get_the_excerpt()
	: __( 'Acompanhe em tempo real as principais novidades do mercado de seguros, regulação e finanças.', 'vue-blocks' );
?>

<main id="primary" class="site-main vb-news-page">
	<?php
	get_template_part(
		'template-parts/news-list/layout',
		null,
		array(
			'query'       => $vb_news_query,
			'title'       => get_the_title(),
			'description' => esc_html( $vb_description ),
		)
	);
	?>
</main>

<?php
get_footer();
