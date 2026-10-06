<?php
/**
 * Vue Blocks - Custom Functionality (functions.php)
 *
 * Segue o padrão descrito em:
 * https://developer.wordpress.org/themes/core-concepts/custom-functionality/
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Impede acesso direto ao arquivo.
}

define( 'VB_VERSION', '1.0.0' );
define( 'VB_THEME_DIR', get_template_directory() );
define( 'VB_THEME_URI', get_template_directory_uri() );

/**
 * Opt-in switch for the Vite build path (Constitution Principle V
 * as amended at v1.1.0: CDN-First Distribution with Optional Build
 * Path).
 *
 * Defaults to `false` so site owners who do NOT build the theme get the
 * CDN-first enqueue behavior. Contributors who want the bundled
 * artifacts set this to `true` AFTER running `npm run build`.
 */
if ( ! defined( 'VB_USE_BUNDLED_ASSETS' ) ) {
	define( 'VB_USE_BUNDLED_ASSETS', false );
}

/**
 * 1. THEME SETUP
 * Registra suporte a recursos do WordPress (title-tag, thumbnails, menus, etc.)
 */
function vb_setup() {

	// Traduções.
	load_theme_textdomain( 'vue-blocks', VB_THEME_DIR . '/languages' );

	// Deixa o WordPress gerenciar o <title>.
	add_theme_support( 'title-tag' );

	// Imagens destacadas.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 800, 450, true );
	add_image_size( 'vb-card', 480, 270, true );

	// HTML5 para formulários de busca, comentários, galerias, etc.
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// Logo customizado.
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// Feeds automáticos no <head>.
	add_theme_support( 'automatic-feed-links' );

	// Suporte à edição de blocos no editor (mesmo em tema clássico).
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	// Menus de navegação.
	register_nav_menus(
		array(
			'primary' => __( 'Menu Principal', 'vue-blocks' ),
			'footer'  => __( 'Menu do Rodapé', 'vue-blocks' ),
		)
	);
}
add_action( 'after_setup_theme', 'vb_setup' );

/**
 * 2. LARGURA DE CONTEÚDO (usada por embeds/imagens)
 */
function vb_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'vb_content_width', 780 );
}
add_action( 'after_setup_theme', 'vb_content_width', 0 );

/**
 * 3. ASSETS (CSS + JS) - Vue.js 3 via CDN + script principal do tema.
 * https://developer.wordpress.org/themes/core-concepts/including-assets/
 */
function vb_enqueue_assets() {

	// ---- Opt-in: Vite build path --------------------------------------
	//
	// If VB_USE_BUNDLED_ASSETS is true, redirect the JS enqueue to the
	// hashed artifacts emitted into `dist/` (read from dist/manifest.json).
	// The CDN-first path remains the default; the bundled path is strictly
	// opt-in and is gated on a present, readable manifest.
	if ( VB_USE_BUNDLED_ASSETS ) {
		$manifest = vb_read_build_manifest();
		if ( null === $manifest ) {
			vb_loud_fail_missing_manifest();
			return; // Loud failure per FR-011 — do not silently fall back.
		}
		vb_enqueue_bundled_assets( $manifest );
		// Comments still need comment-reply below.
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
		return;
	}

	// ---- Default: CDN-first (Vue from unpkg + un-built app.js) ---------

	// Stylesheet principal (style.css também registra o tema).
	wp_enqueue_style( 'vue-blocks-style', get_stylesheet_uri(), array(), VB_VERSION );

	// Vue.js 3 (build de produção, global) via CDN oficial.
	wp_enqueue_script(
		'vue-js',
		'https://unpkg.com/vue@3.4.31/dist/vue.global.prod.js',
		array(),
		'3.4.31',
		true
	);

	// Comentários encadeados precisam do script nativo do WP.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// App Vue do tema (menu mobile, dark mode, busca ao vivo, feed de posts).
	wp_enqueue_script(
		'vue-blocks-app',
		VB_THEME_URI . '/assets/js/app.js',
		array( 'vue-js' ),
		VB_VERSION,
		true
	);

	// Dados que o PHP entrega ao Vue (REST API, nonce, textos i18n, contexto da página).
	wp_localize_script(
		'vue-blocks-app',
		'vbData',
		array(
			'restUrl'      => esc_url_raw( rest_url( 'wp/v2' ) ),
			'nonce'        => wp_create_nonce( 'wp_rest' ),
			'homeUrl'      => esc_url( home_url( '/' ) ),
			'isFrontPage'  => is_front_page(),
			'currentQuery' => is_search() ? get_search_query() : '',
			'postsPerPage' => (int) get_option( 'posts_per_page' ),
			'i18n'         => array(
				'searchPlaceholder' => __( 'Pesquisar artigos…', 'vue-blocks' ),
				'noResults'         => __( 'Nenhum resultado encontrado.', 'vue-blocks' ),
				'loading'           => __( 'Carregando…', 'vue-blocks' ),
				'loadMore'          => __( 'Carregar mais posts', 'vue-blocks' ),
				'noMorePosts'       => __( 'Não há mais posts.', 'vue-blocks' ),
				'readMore'          => __( 'Ler mais', 'vue-blocks' ),
				'toggleMenu'        => __( 'Abrir/Fechar menu', 'vue-blocks' ),
				'toggleTheme'       => __( 'Alternar modo escuro', 'vue-blocks' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'vb_enqueue_assets' );

/**
 * 4. ÁREAS DE WIDGETS (sidebar e rodapé)
 */
function vb_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Barra Lateral', 'vue-blocks' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Widgets exibidos na barra lateral do blog.', 'vue-blocks' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Rodapé', 'vue-blocks' ),
			'id'            => 'footer-1',
			'description'   => __( 'Widgets exibidos no rodapé do site.', 'vue-blocks' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'vb_widgets_init' );

/**
 * 5. EXPÕE CAMPOS ÚTEIS NA REST API PARA O VUE CONSUMIR
 * (URL da imagem destacada e trecho já processado em cada post).
 */
function vb_register_rest_fields() {
	register_rest_field(
		'post',
		'vb_thumbnail',
		array(
			'get_callback' => function ( $post ) {
				$id = $post['id'];
				if ( has_post_thumbnail( $id ) ) {
					return get_the_post_thumbnail_url( $id, 'vb-card' );
				}
				return null;
			},
		)
	);

	register_rest_field(
		'post',
		'vb_author_name',
		array(
			'get_callback' => function ( $post ) {
				return get_the_author_meta( 'display_name', $post['author'] );
			},
		)
	);
}
add_action( 'rest_api_init', 'vb_register_rest_fields' );

/**
 * 6. FALLBACK DE MENU CASO NENHUM MENU TENHA SIDO CRIADO NO PAINEL
 */
function vb_fallback_menu() {
	echo '<ul id="primary-menu" class="menu">';
	wp_list_pages(
		array(
			'title_li' => '',
			'depth'    => 1,
		)
	);
	echo '</ul>';
}

/**
 * 7. TAMANHO DE EXCERPT E "..." CUSTOMIZADO
 */
function vb_excerpt_length( $length ) {
	return 24;
}
add_filter( 'excerpt_length', 'vb_excerpt_length' );

function vb_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'vb_excerpt_more' );

/**
 * 8. LIMPEZA DE <head> (boas práticas de segurança/performance)
 */
remove_action( 'wp_head', 'wp_generator' );

/**
 * 9. ARQUIVOS AUXILIARES (mantém functions.php organizado)
 */
require VB_THEME_DIR . '/inc/template-tags.php';
require VB_THEME_DIR . '/inc/build-manifest.php';
