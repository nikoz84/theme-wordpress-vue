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
 * Prints SEO/Open Graph meta tags for singular content and front page.
 */
if ( ! function_exists( 'adicionar_open_graph_whatsapp' ) ) {
	function adicionar_open_graph_whatsapp() {
		if ( ! is_single() && ! is_page() && ! is_front_page() ) {
			return;
		}

		global $post;

		$imagem_padrao = 'https://seusite.com.br'; // ALTERE AQUI

		if ( ! is_front_page() && isset( $post->ID ) && has_post_thumbnail( $post->ID ) ) {
			$img_data = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			$img_src  = $img_data ? $img_data[0] : $imagem_padrao;
		} else {
			$img_src = $imagem_padrao;
		}

		if ( is_front_page() ) {
			$titulo    = get_bloginfo( 'name' );
			$descricao = get_bloginfo( 'description' );
			$url       = home_url( '/' );
			$tipo      = 'website';
		} else {
			$titulo    = get_the_title();
			$excerpt   = isset( $post->post_excerpt ) && $post->post_excerpt ? $post->post_excerpt : wp_trim_words( $post->post_content, 25, '...' );
			$descricao = str_replace( '"', "'", wp_strip_all_tags( $excerpt ) );
			$url       = get_permalink();
			$tipo      = 'article';
		}

		echo '<meta property="og:title" content="' . esc_attr( $titulo ) . '" />' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $descricao ) . '" />' . "\n";
		echo '<meta property="og:type" content="' . esc_attr( $tipo ) . '" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";
		echo '<meta property="og:image" content="' . esc_url( $img_src ) . '" />' . "\n";
		echo '<meta property="og:image:width" content="1200" />' . "\n";
		echo '<meta property="og:image:height" content="630" />' . "\n";

		// Keep Twitter Card tags in sync too.
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $titulo ) . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $descricao ) . '" />' . "\n";
		echo '<meta name="twitter:image" content="' . esc_url( $img_src ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'adicionar_open_graph_whatsapp', 5 );


/**
 * 2. FUNÇÃO PROGRAMÁTICA PARA LIMPAR O LITESPEED CACHE
 */
if ( ! function_exists( 'limpar_cache_litespeed' ) ) {
	function limpar_cache_litespeed( $tipo = 'all', $alvo = '' ) {
		if ( ! defined( 'LSCWP_V' ) ) {
			return false;
		}

		switch ( $tipo ) {
			case 'post':
				if ( ! empty( $alvo ) && is_numeric( $alvo ) ) {
					do_action( 'litespeed_purge_post', (int) $alvo );
					return true;
				}
				break;

			case 'url':
				if ( ! empty( $alvo ) && filter_var( $alvo, FILTER_VALIDATE_URL ) ) {
					do_action( 'litespeed_purge_url', esc_url_raw( $alvo ) );
					return true;
				}
				break;

			case 'all':
			default:
				do_action( 'litespeed_purge_all' );
				return true;
		}

		return false;
	}
}

/**
 * 3. GATILHO AUTOMÁTICO AO SALVAR POSTS OU PÁGINAS
 */
add_action( 'save_post', function ( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	limpar_cache_litespeed( 'post', $post_id );
} );


/**
 * 4. WEBHOOK (URL SECRETA PARA LIMPEZA REMOTA)
 * Uso: https://seusite.com.br/?limpar_cache_token=SEU_TOKEN
 */
add_action( 'init', function () {
	$token_secreto = '6985465asd56488asd464.kuiasu'; // ALTERE AQUI

	if ( isset( $_GET['limpar_cache_token'] ) && $_GET['limpar_cache_token'] === $token_secreto ) {
		$tipo = isset( $_GET['tipo'] ) ? sanitize_text_field( wp_unslash( $_GET['tipo'] ) ) : 'all';
		$alvo = isset( $_GET['alvo'] ) ? sanitize_text_field( wp_unslash( $_GET['alvo'] ) ) : '';

		$sucesso = limpar_cache_litespeed( $tipo, $alvo );

		wp_send_json(
			array(
				'status'   => $sucesso ? 'sucesso' : 'erro',
				'mensagem' => $sucesso ? 'Cache do LiteSpeed limpo com sucesso.' : 'Plugin inativo ou parâmetros inválidos.',
				'tipo'     => $tipo,
			)
		);
	}
} );