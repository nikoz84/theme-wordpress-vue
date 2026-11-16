<?php
/**
 * SEO / Open Graph tags for single posts and pages.
 *
 * Emits Open Graph, Twitter Card and canonical metadata in <head> so
 * shared links (WhatsApp, Facebook, X, LinkedIn) and search engines
 * pick up the post title, description and featured image.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Tell the Hostinger AI Assistant SEO module that this theme handles its
// own SEO/Open Graph tags, so it does not emit a duplicate (and often
// stale) set of og:/twitter: meta tags on top of ours.
add_filter( 'hostinger_ai_assistant_seo_plugin_active', '__return_true' );

/**
 * Favicon / webmanifest tags.
 */
function vb_favicon_tags() {
	echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( VB_THEME_URI . '/assets/apple-touch-icon.png' ) . '" />' . "\n";
	echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( VB_THEME_URI . '/assets/favicon-32x32.png' ) . '" />' . "\n";
	echo '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url( VB_THEME_URI . '/assets/favicon-16x16.png' ) . '" />' . "\n";
	echo '<link rel="icon" href="' . esc_url( VB_THEME_URI . '/assets/favicon.ico' ) . '" />' . "\n";
	echo '<link rel="manifest" href="' . esc_url( VB_THEME_URI . '/assets/site.webmanifest' ) . '" />' . "\n";
}
add_action( 'wp_head', 'vb_favicon_tags', 1 );

/**
 * Let bots/crawlers (WhatsApp, Facebook, etc.) bypass the page cache and
 * trigger a purge of the cached entry for the URL they are visiting, so
 * previews always reflect the latest OG tags instead of stale HTML.
 */
function vb_bot_cache_bypass() {
	if ( is_admin() ) {
		return;
	}

	$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( (string) $_SERVER['HTTP_USER_AGENT'] ) : '';
	$bot_signatures = array(
		'bot',
		'crawl',
		'spider',
		'facebookexternalhit',
		'whatsapp',
		'twitterbot',
		'linkedinbot',
		'slackbot',
		'telegrambot',
		'discordbot',
	);

	$is_bot = false;
	foreach ( $bot_signatures as $signature ) {
		if ( false !== strpos( $user_agent, $signature ) ) {
			$is_bot = true;
			break;
		}
	}

	if ( ! $is_bot ) {
		return;
	}

	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	if ( function_exists( 'litespeed_purge_single_post' ) && is_singular() ) {
		litespeed_purge_single_post( get_the_ID() );
	}
}
add_action( 'template_redirect', 'vb_bot_cache_bypass', 1 );

/**
 * Prints SEO/Open Graph meta tags for singular content.
 */
if ( ! function_exists( 'adicionar_open_graph_whatsapp' ) ) {
	function adicionar_open_graph_whatsapp() {
		if ( ! is_single() && ! is_page() ) {
			return;
		}

		global $post;
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		// Title.
		$title = get_the_title( $post );
		if ( '' === $title ) {
			$title = get_bloginfo( 'name' );
		}

		// Description (excerpt or trimmed content).
		if ( ! empty( $post->post_excerpt ) ) {
			$excerpt = $post->post_excerpt;
		} else {
			$excerpt = wp_trim_words( $post->post_content, 25, '...' );
		}
		$excerpt = wp_strip_all_tags( $excerpt );
		$excerpt = str_replace( '"', "'", $excerpt );
		$excerpt = trim( preg_replace( '/\s+/u', ' ', $excerpt ) );
		if ( '' === $excerpt ) {
			$excerpt = get_bloginfo( 'description' );
		}

		// Featured image (or a sensible default).
		$thumbnail_id = has_post_thumbnail( $post->ID ) ? (int) get_post_thumbnail_id( $post->ID ) : 0;
		if ( $thumbnail_id ) {
			$img_data = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			$img_src  = $img_data ? $img_data[0] : '';
			$img_alt  = (string) get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
		} else {
			$img_src = VB_THEME_URI . '/assets/images/logo.png';
			$img_alt = get_bloginfo( 'name' );
		}
		if ( '' === $img_alt ) {
			$img_alt = $title;
		}

		$permalink = get_permalink( $post );
		$site_name = get_bloginfo( 'name' );

		// Standard SEO description.
		echo '<meta name="description" content="' . esc_attr( $excerpt ) . '" />' . "\n";

		// Open Graph.
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $excerpt ) . '" />' . "\n";
		echo '<meta property="og:type" content="article" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $permalink ) . '" />' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";
		echo '<meta property="og:locale" content="' . esc_attr( str_replace( '-', '_', get_locale() ) ) . '" />' . "\n";
		if ( $img_src ) {
			echo '<meta property="og:image" content="' . esc_url( $img_src ) . '" />' . "\n";
			echo '<meta property="og:image:secure_url" content="' . esc_url( $img_src ) . '" />' . "\n";
			echo '<meta property="og:image:alt" content="' . esc_attr( $img_alt ) . '" />' . "\n";
			echo '<meta property="og:image:width" content="1200" />' . "\n";
			echo '<meta property="og:image:height" content="630" />' . "\n";
		}

		// Article metadata.
		echo '<meta property="article:published_time" content="' . esc_attr( get_post_time( 'c', true, $post ) ) . '" />' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( get_post_modified_time( 'c', true, $post ) ) . '" />' . "\n";
		$author = get_the_author_meta( 'display_name', (int) $post->post_author );
		if ( $author ) {
			echo '<meta property="article:author" content="' . esc_attr( $author ) . '" />' . "\n";
		}
		$categories = get_the_category( $post->ID );
		if ( ! empty( $categories ) ) {
			echo '<meta property="article:section" content="' . esc_attr( $categories[0]->name ) . '" />' . "\n";
		}

		// Twitter Card.
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $excerpt ) . '" />' . "\n";
		if ( $img_src ) {
			echo '<meta name="twitter:image" content="' . esc_url( $img_src ) . '" />' . "\n";
			echo '<meta name="twitter:image:alt" content="' . esc_attr( $img_alt ) . '" />' . "\n";
		}
		echo '<meta name="twitter:url" content="' . esc_url( $permalink ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'adicionar_open_graph_whatsapp', 5 );