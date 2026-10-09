<?php
/**
 * Vue Blocks — News list + article features.
 *
 *  1. Hide "Uncategorized" posts from front-end lists.
 *  2. Public author names (never an e-mail) + "Cargo" profile field.
 *  3. View counter + "Mais lidas".
 *  4. Related / "Leia também" queries, reading time, share links.
 *  5. "Publicidade" widget area.
 *  6. Newsletter subscriptions (double opt-in).
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================
   1. HIDE UNCATEGORIZED POSTS
   ========================================================= */

/**
 * Whether a query should skip the default ("Uncategorized") category.
 *
 * @param WP_Query $query Query.
 * @return bool
 */
function vb_should_hide_uncategorized( $query ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ! apply_filters( 'vb_hide_uncategorized', true ) ) {
		return false;
	}
	if ( $query->is_singular() || $query->is_category( (int) get_option( 'default_category' ) ) ) {
		return false;
	}
	$post_type = $query->get( 'post_type' );
	return empty( $post_type ) || 'post' === $post_type || ( is_array( $post_type ) && in_array( 'post', $post_type, true ) );
}

/**
 * Excludes the default category from front-end post lists (home
 * sections, archives, search, widgets). Single posts are unaffected.
 *
 * @param WP_Query $query Query.
 */
function vb_hide_uncategorized_posts( $query ) {
	if ( ! vb_should_hide_uncategorized( $query ) ) {
		return;
	}
	$excluded   = (array) $query->get( 'category__not_in' );
	$excluded[] = (int) get_option( 'default_category' );
	$query->set( 'category__not_in', array_values( array_unique( array_filter( $excluded ) ) ) );
}
add_action( 'pre_get_posts', 'vb_hide_uncategorized_posts' );

/**
 * Same rule for the public REST post list (home "Carregar mais", live search).
 *
 * @param array           $args    Query args.
 * @param WP_REST_Request $request Request.
 * @return array
 */
function vb_hide_uncategorized_rest( $args, $request ) {
	if ( 'edit' === $request['context'] || ! empty( $request['categories'] ) || ! apply_filters( 'vb_hide_uncategorized', true ) ) {
		return $args;
	}
	$args['category__not_in'] = array_merge( (array) ( $args['category__not_in'] ?? array() ), array( (int) get_option( 'default_category' ) ) );
	return $args;
}
add_filter( 'rest_post_query', 'vb_hide_uncategorized_rest', 10, 2 );

/* =========================================================
   2. PUBLIC AUTHOR NAMES (NO E-MAILS)
   ========================================================= */

/**
 * Author name safe to print publicly. Never returns an e-mail address:
 * falls back to first/last name, nickname, then "Redação".
 *
 * @param int|null $user_id User ID (defaults to the current post author).
 * @return string
 */
function vb_author_public_name( $user_id = null ) {
	$user = get_userdata( $user_id ? (int) $user_id : (int) get_the_author_meta( 'ID' ) );
	if ( ! $user ) {
		return __( 'Redação', 'vue-blocks' );
	}
	$candidates = array(
		$user->display_name,
		trim( $user->first_name . ' ' . $user->last_name ),
		$user->nickname,
	);
	foreach ( $candidates as $name ) {
		if ( '' !== $name && false === strpos( $name, '@' ) ) {
			return $name;
		}
	}
	return __( 'Redação', 'vue-blocks' );
}

/**
 * Front-end filter: `get_the_author()` / display_name never expose an e-mail.
 *
 * @param string $name    Display name.
 * @param int    $user_id User ID (only for get_the_author_display_name).
 * @return string
 */
function vb_filter_author_name( $name, $user_id = 0 ) {
	if ( is_admin() || false === strpos( (string) $name, '@' ) ) {
		return $name;
	}
	return vb_author_public_name( $user_id ? $user_id : null );
}
add_filter( 'the_author', 'vb_filter_author_name' );
add_filter( 'get_the_author_display_name', 'vb_filter_author_name', 10, 2 );

/**
 * Job title ("Cargo") shown under the author name, e.g. "Editor de Regulação".
 *
 * @param int $user_id User ID.
 * @return string
 */
function vb_author_job_title( $user_id ) {
	return (string) get_user_meta( (int) $user_id, 'vb_job_title', true );
}

/**
 * Initials avatar — no Gravatar request (the Gravatar URL contains a hash
 * of the author's e-mail).
 *
 * @param int    $user_id User ID.
 * @param string $class   CSS class.
 * @return string HTML.
 */
function vb_author_avatar( $user_id, $class = 'vb-avatar' ) {
	$name     = vb_author_public_name( $user_id );
	$words    = preg_split( '/\s+/u', trim( $name ) );
	$initials = mb_strtoupper( mb_substr( $words[0], 0, 1 ) . ( count( $words ) > 1 ? mb_substr( end( $words ), 0, 1 ) : '' ) );
	return '<span class="' . esc_attr( $class ) . '" aria-hidden="true">' . esc_html( $initials ) . '</span>';
}

/**
 * "Cargo" field on the user profile screen.
 *
 * @param WP_User $user User being edited.
 */
function vb_user_job_title_field( $user ) {
	?>
	<h2><?php esc_html_e( 'Safe Mídia', 'vue-blocks' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="vb_job_title"><?php esc_html_e( 'Cargo', 'vue-blocks' ); ?></label></th>
			<td>
				<input type="text" name="vb_job_title" id="vb_job_title" class="regular-text" value="<?php echo esc_attr( vb_author_job_title( $user->ID ) ); ?>" />
				<p class="description"><?php esc_html_e( 'Exibido abaixo do nome nas matérias, ex.: "Editor de Regulação".', 'vue-blocks' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'vb_user_job_title_field' );
add_action( 'edit_user_profile', 'vb_user_job_title_field' );

/**
 * Save the "Cargo" field (core already verified the profile nonce).
 *
 * @param int $user_id User ID.
 */
function vb_save_user_job_title( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['vb_job_title'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}
	update_user_meta( $user_id, 'vb_job_title', sanitize_text_field( wp_unslash( $_POST['vb_job_title'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
}
add_action( 'personal_options_update', 'vb_save_user_job_title' );
add_action( 'edit_user_profile_update', 'vb_save_user_job_title' );

/* =========================================================
   3. VIEW COUNTER + MAIS LIDAS
   ========================================================= */

/**
 * Count a view of a single post (skips editors, previews and bots).
 */
function vb_count_post_view() {
	if ( ! is_singular( 'post' ) || is_preview() || current_user_can( 'edit_posts' ) ) {
		return;
	}
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	if ( '' === $agent || preg_match( '/bot|crawl|spider|slurp|facebookexternalhit|preview/i', $agent ) ) {
		return;
	}
	$post_id = get_queried_object_id();
	update_post_meta( $post_id, 'vb_views', (int) get_post_meta( $post_id, 'vb_views', true ) + 1 );
}
add_action( 'template_redirect', 'vb_count_post_view' );

/**
 * Most-read posts; filled with the most recent ones while there are
 * not enough posts with views yet.
 *
 * @param int $limit Number of posts.
 * @return WP_Post[]
 */
function vb_get_most_read( $limit = 5 ) {
	$posts = get_posts(
		array(
			'posts_per_page' => $limit,
			'meta_key'       => 'vb_views', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'        => array(
				'meta_value_num' => 'DESC',
				'date'           => 'DESC',
			),
		)
	);
	if ( count( $posts ) < $limit ) {
		$posts = array_merge(
			$posts,
			get_posts(
				array(
					'posts_per_page' => $limit - count( $posts ),
					'post__not_in'   => wp_list_pluck( $posts, 'ID' ),
				)
			)
		);
	}
	return $posts;
}

/* =========================================================
   4. RELATED, READING TIME, SHARE
   ========================================================= */

/**
 * Posts in the same categories as $post_id ("Relacionados").
 *
 * @param int   $post_id Post ID.
 * @param int   $limit   Number of posts.
 * @param int[] $exclude Extra post IDs to skip.
 * @return WP_Post[]
 */
function vb_get_related_posts( $post_id, $limit = 4, $exclude = array() ) {
	$exclude = array_merge( array( $post_id ), $exclude );
	$cats    = wp_get_post_categories( $post_id );
	$posts   = $cats ? get_posts(
		array(
			'posts_per_page'      => $limit,
			'category__in'        => $cats,
			'post__not_in'        => $exclude,
			'ignore_sticky_posts' => true,
		)
	) : array();
	return vb_fill_with_recent( $posts, $limit, $exclude );
}

/**
 * Top up a list with the most recent posts not already listed/excluded.
 *
 * @param WP_Post[] $posts   Posts so far.
 * @param int       $limit   Wanted count.
 * @param int[]     $exclude Post IDs to skip.
 * @return WP_Post[]
 */
function vb_fill_with_recent( $posts, $limit, $exclude ) {
	if ( count( $posts ) >= $limit ) {
		return $posts;
	}
	return array_merge(
		$posts,
		get_posts(
			array(
				'posts_per_page'      => $limit - count( $posts ),
				'post__not_in'        => array_merge( $exclude, wp_list_pluck( $posts, 'ID' ) ),
				'ignore_sticky_posts' => true,
			)
		)
	);
}

/**
 * "Leia também": posts sharing tags with $post_id, then same categories.
 *
 * @param int   $post_id Post ID.
 * @param int   $limit   Number of posts.
 * @param int[] $exclude Post IDs to skip (e.g. the "Relacionados" ones).
 * @return WP_Post[]
 */
function vb_get_read_also_posts( $post_id, $limit = 2, $exclude = array() ) {
	$exclude = array_merge( array( $post_id ), $exclude );
	$posts   = array();
	$tags    = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );
	if ( $tags ) {
		$posts = get_posts(
			array(
				'posts_per_page' => $limit,
				'tag__in'        => $tags,
				'post__not_in'   => $exclude,
			)
		);
	}
	if ( count( $posts ) < $limit ) {
		// Same categories, then most recent (vb_get_related_posts fills).
		$posts = array_merge( $posts, vb_get_related_posts( $post_id, $limit - count( $posts ), array_merge( $exclude, wp_list_pluck( $posts, 'ID' ) ) ) );
	}
	return $posts;
}

/**
 * Estimated reading time in minutes (200 words/min).
 *
 * @param int|null $post_id Post ID.
 * @return int
 */
function vb_reading_time( $post_id = null ) {
	$text  = wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post_id ? $post_id : get_the_ID() ) ) );
	$words = count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Share URLs for the current post.
 *
 * @return array<string, array{label: string, url: string}>
 */
function vb_share_links() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( wp_strip_all_tags( get_the_title() ) );
	return array(
		'facebook' => array( 'label' => 'Facebook', 'url' => "https://www.facebook.com/sharer/sharer.php?u={$url}" ),
		'x'        => array( 'label' => 'X', 'url' => "https://x.com/intent/tweet?url={$url}&text={$title}" ),
		'linkedin' => array( 'label' => 'LinkedIn', 'url' => "https://www.linkedin.com/sharing/share-offsite/?url={$url}" ),
		'whatsapp' => array( 'label' => 'WhatsApp', 'url' => "https://api.whatsapp.com/send?text={$title}%20{$url}" ),
	);
}

/* =========================================================
   5. NEWS PAGE URL, CATEGORY PILLS, SORT, PAGINATION
   ========================================================= */

/**
 * URL of the "Últimas Notícias" list.
 *
 * @return string
 */
function vb_news_url() {
	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page ) {
		return get_permalink( $posts_page );
	}
	$page = get_page_by_path( 'noticias' );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	return home_url( '/' );
}

/**
 * Categories for the filter pills: the categories in the primary menu
 * (same order), otherwise the most-used ones.
 *
 * @return WP_Term[]
 */
function vb_filter_categories() {
	$default   = (int) get_option( 'default_category' );
	$locations = get_nav_menu_locations();
	$terms     = array();
	if ( ! empty( $locations['primary'] ) ) {
		foreach ( (array) wp_get_nav_menu_items( $locations['primary'] ) as $item ) {
			if ( 'taxonomy' === $item->type && 'category' === $item->object && (int) $item->object_id !== $default ) {
				$term = get_term( (int) $item->object_id, 'category' );
				if ( $term && ! is_wp_error( $term ) ) {
					$terms[] = $term;
				}
			}
		}
	}
	if ( ! $terms ) {
		$terms = get_categories(
			array(
				'orderby' => 'count',
				'order'   => 'DESC',
				'number'  => 10,
				'exclude' => array( $default ),
			)
		);
	}
	return $terms;
}

/**
 * Sort options for the list (`?ordem=`).
 *
 * @return array<string, string>
 */
function vb_sort_options() {
	return array(
		'recentes'   => __( 'Mais recentes', 'vue-blocks' ),
		'lidas'      => __( 'Mais lidas', 'vue-blocks' ),
		'comentadas' => __( 'Mais comentadas', 'vue-blocks' ),
	);
}

/**
 * Current sort key.
 *
 * @return string
 */
function vb_current_sort() {
	$sort = isset( $_GET['ordem'] ) ? sanitize_key( wp_unslash( $_GET['ordem'] ) ) : 'recentes'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return array_key_exists( $sort, vb_sort_options() ) ? $sort : 'recentes';
}

/**
 * Query args for a sort key.
 *
 * @param string $sort Sort key.
 * @return array
 */
function vb_sort_query_args( $sort ) {
	if ( 'lidas' === $sort ) {
		// Posts without views are kept (LEFT JOIN via NOT EXISTS clause).
		return array(
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				'vb_views' => array(
					'key'     => 'vb_views',
					'type'    => 'NUMERIC',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'vb_views',
					'compare' => 'NOT EXISTS',
				),
			),
			'orderby'    => array(
				'vb_views' => 'DESC',
				'date'     => 'DESC',
			),
		);
	}
	if ( 'comentadas' === $sort ) {
		return array(
			'orderby' => array(
				'comment_count' => 'DESC',
				'date'          => 'DESC',
			),
		);
	}
	return array();
}

/**
 * Apply `?ordem=` to archive main queries.
 *
 * @param WP_Query $query Query.
 */
function vb_apply_archive_sort( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! ( $query->is_archive() || $query->is_home() ) ) {
		return;
	}
	foreach ( vb_sort_query_args( vb_current_sort() ) as $key => $value ) {
		$query->set( $key, $value );
	}
}
add_action( 'pre_get_posts', 'vb_apply_archive_sort' );

/**
 * Safe Mídia pagination (`.pagination` / `.page-btn`).
 *
 * @param WP_Query|null $query Query (defaults to the main query).
 */
function vb_news_pagination( $query = null ) {
	global $wp_query;
	$query = $query ? $query : $wp_query;
	$total = (int) $query->max_num_pages;
	if ( $total < 2 ) {
		return;
	}
	$current = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$args    = array( 'ordem' => 'recentes' !== vb_current_sort() ? vb_current_sort() : false );

	$links = paginate_links(
		array(
			'total'     => $total,
			'current'   => $current,
			'type'      => 'array',
			'mid_size'  => 1,
			'prev_next' => false,
			'add_args'  => array_filter( $args ),
		)
	);

	$arrow_l = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>';
	$arrow_r = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
	$page_url = function ( $n ) use ( $args ) {
		return add_query_arg( array_filter( $args ), get_pagenum_link( $n ) );
	};

	echo '<nav class="pagination" aria-label="' . esc_attr__( 'Paginação', 'vue-blocks' ) . '">';
	if ( $current > 1 ) {
		echo '<a class="page-btn prev-next" href="' . esc_url( $page_url( $current - 1 ) ) . '">' . $arrow_l . esc_html__( 'Anterior', 'vue-blocks' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		echo '<span class="page-btn prev-next is-disabled" aria-disabled="true">' . $arrow_l . esc_html__( 'Anterior', 'vue-blocks' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	foreach ( (array) $links as $link ) {
		if ( false !== strpos( $link, 'dots' ) ) {
			echo '<span class="page-dots">…</span>';
		} elseif ( false !== strpos( $link, 'current' ) ) {
			echo '<span class="page-btn active" aria-current="page">' . esc_html( wp_strip_all_tags( $link ) ) . '</span>';
		} else {
			echo str_replace( 'class="page-numbers"', 'class="page-btn"', $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped paginate_links() markup.
		}
	}
	/* translators: %d: total pages */
	echo '<span class="pagination-mobile-label">' . esc_html( sprintf( __( 'de %d', 'vue-blocks' ), $total ) ) . '</span>';
	if ( $current < $total ) {
		echo '<a class="page-btn prev-next" href="' . esc_url( $page_url( $current + 1 ) ) . '">' . esc_html__( 'Próxima', 'vue-blocks' ) . $arrow_r . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		echo '<span class="page-btn prev-next is-disabled" aria-disabled="true">' . esc_html__( 'Próxima', 'vue-blocks' ) . $arrow_r . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</nav>';
}

/* =========================================================
   6. PUBLICIDADE WIDGET AREA
   ========================================================= */

/**
 * Sidebar ad slot (300×600). Shows a placeholder while empty.
 */
function vb_register_ad_sidebar() {
	register_sidebar(
		array(
			'name'          => __( 'Publicidade – lateral', 'vue-blocks' ),
			'id'            => 'vb-ad-sidebar',
			'description'   => __( 'Anúncio 300×600 na lateral da lista e das matérias. Vazio = espaço reservado.', 'vue-blocks' ),
			'before_widget' => '<div id="%1$s" class="vb-ad-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<span class="screen-reader-text">',
			'after_title'   => '</span>',
		)
	);
}
add_action( 'widgets_init', 'vb_register_ad_sidebar' );

/* =========================================================
   7. NEWSLETTER (DOUBLE OPT-IN)
   ========================================================= */

/**
 * Private subscriber list (Painel → Newsletter). Admins only.
 */
function vb_register_subscriber_type() {
	$cap = 'manage_options';
	register_post_type(
		'vb_subscriber',
		array(
			'labels'          => array(
				'name'          => __( 'Newsletter', 'vue-blocks' ),
				'singular_name' => __( 'Assinante', 'vue-blocks' ),
				'all_items'     => __( 'Assinantes', 'vue-blocks' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 26,
			'supports'        => array( 'title' ),
			'map_meta_cap'    => false,
			'capabilities'    => array(
				'edit_post'          => $cap,
				'read_post'          => $cap,
				'delete_post'        => $cap,
				'edit_posts'         => $cap,
				'edit_others_posts'  => $cap,
				'delete_posts'       => $cap,
				'publish_posts'      => $cap,
				'read_private_posts' => $cap,
				'create_posts'       => 'do_not_allow',
			),
		)
	);
}
add_action( 'init', 'vb_register_subscriber_type' );

/**
 * Admin list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function vb_subscriber_columns( $columns ) {
	return array(
		'cb'        => $columns['cb'],
		'title'     => __( 'E-mail', 'vue-blocks' ),
		'vb_name'   => __( 'Nome', 'vue-blocks' ),
		'vb_status' => __( 'Status', 'vue-blocks' ),
		'date'      => __( 'Data', 'vue-blocks' ),
	);
}
add_filter( 'manage_vb_subscriber_posts_columns', 'vb_subscriber_columns' );

/**
 * Admin list column values.
 *
 * @param string $column  Column.
 * @param int    $post_id Subscriber ID.
 */
function vb_subscriber_column_value( $column, $post_id ) {
	if ( 'vb_name' === $column ) {
		echo esc_html( get_post_meta( $post_id, 'vb_name', true ) );
	} elseif ( 'vb_status' === $column ) {
		echo 'confirmed' === get_post_meta( $post_id, 'vb_status', true ) ? esc_html__( 'Confirmado', 'vue-blocks' ) : esc_html__( 'Aguardando confirmação', 'vue-blocks' );
	}
}
add_action( 'manage_vb_subscriber_posts_custom_column', 'vb_subscriber_column_value', 10, 2 );

/**
 * Redirect back to the form's page with a status flag.
 *
 * @param string $status Status key.
 */
function vb_newsletter_redirect( $status ) {
	// Which form was used (its element id) — the message shows only there.
	$source = isset( $_POST['vb_source'] ) ? sanitize_key( wp_unslash( $_POST['vb_source'] ) ) : 'newsletter'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$back   = wp_get_referer();
	$back   = $back ? $back : home_url( '/' );
	$url    = add_query_arg(
		array(
			'newsletter' => $status,
			'nl'         => $source,
		),
		remove_query_arg( array( 'newsletter', 'nl' ), $back )
	);
	wp_safe_redirect( $url . '#' . $source );
	exit;
}

/**
 * Hidden fields every newsletter form needs (action, source id, honeypot).
 *
 * @param string $source The form wrapper's element id.
 */
function vb_newsletter_hidden_fields( $source ) {
	?>
	<input type="hidden" name="action" value="vb_newsletter_subscribe" />
	<input type="hidden" name="vb_source" value="<?php echo esc_attr( $source ); ?>" />
	<span class="vb-hp" aria-hidden="true"><input type="text" name="vb_website" tabindex="-1" autocomplete="off" /></span>
	<?php
}

/**
 * Handle a subscription (logged-out friendly: honeypot + per-IP rate
 * limit instead of a nonce, so cached pages keep working).
 */
function vb_newsletter_subscribe() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( ! empty( $_POST['vb_website'] ) ) { // Honeypot.
		vb_newsletter_redirect( 'ok' );
	}
	$email = isset( $_POST['vb_email'] ) ? sanitize_email( wp_unslash( $_POST['vb_email'] ) ) : '';
	$name  = isset( $_POST['vb_name'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_name'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	if ( ! is_email( $email ) ) {
		vb_newsletter_redirect( 'invalid' );
	}

	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key  = 'vb_nl_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 5 ) {
		vb_newsletter_redirect( 'limit' );
	}
	set_transient( $key, $hits + 1, HOUR_IN_SECONDS );

	$email    = strtolower( $email );
	$existing = get_posts(
		array(
			'post_type'   => 'vb_subscriber',
			'post_status' => 'private',
			'title'       => $email,
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	if ( $existing ) {
		vb_newsletter_redirect( 'exists' );
	}

	$token = wp_generate_password( 32, false );
	$id    = wp_insert_post(
		array(
			'post_type'   => 'vb_subscriber',
			'post_status' => 'private',
			'post_title'  => $email,
			'meta_input'  => array(
				'vb_name'   => $name,
				'vb_status' => 'pending',
				'vb_token'  => wp_hash_password( $token ),
			),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		vb_newsletter_redirect( 'error' );
	}

	$confirm = add_query_arg(
		array(
			'action' => 'vb_newsletter_confirm',
			'id'     => $id,
			'token'  => $token,
		),
		admin_url( 'admin-post.php' )
	);
	wp_mail(
		$email,
		/* translators: %s: site name */
		sprintf( __( 'Confirme sua inscrição — %s', 'vue-blocks' ), get_bloginfo( 'name' ) ),
		/* translators: %s: confirmation URL */
		sprintf( __( "Olá!\n\nPara confirmar sua inscrição na newsletter, acesse:\n%s\n\nSe você não pediu a inscrição, ignore este e-mail.", 'vue-blocks' ), $confirm )
	);

	vb_newsletter_redirect( 'ok' );
}
add_action( 'admin_post_nopriv_vb_newsletter_subscribe', 'vb_newsletter_subscribe' );
add_action( 'admin_post_vb_newsletter_subscribe', 'vb_newsletter_subscribe' );

/**
 * Confirmation link from the e-mail.
 */
function vb_newsletter_confirm() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- token-based link.
	$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	$hash = $id ? (string) get_post_meta( $id, 'vb_token', true ) : '';
	$ok   = 'vb_subscriber' === get_post_type( $id ) && '' !== $hash && wp_check_password( $token, $hash );
	if ( $ok ) {
		update_post_meta( $id, 'vb_status', 'confirmed' );
		delete_post_meta( $id, 'vb_token' );
	}
	wp_safe_redirect( add_query_arg( array( 'newsletter' => $ok ? 'confirmed' : 'error', 'nl' => 'footer-newsletter' ), home_url( '/' ) ) . '#footer-newsletter' );
	exit;
}
add_action( 'admin_post_nopriv_vb_newsletter_confirm', 'vb_newsletter_confirm' );
add_action( 'admin_post_vb_newsletter_confirm', 'vb_newsletter_confirm' );

/**
 * Feedback message for `?newsletter=` (empty when none, or when the
 * submission came from another form on the page).
 *
 * @param string $source The form wrapper's element id.
 * @return string
 */
function vb_newsletter_message( $source ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$from   = isset( $_GET['nl'] ) ? sanitize_key( wp_unslash( $_GET['nl'] ) ) : '';
	$status = isset( $_GET['newsletter'] ) ? sanitize_key( wp_unslash( $_GET['newsletter'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	if ( $from !== $source ) {
		return '';
	}
	$messages = array(
		'ok'        => __( 'Quase lá! Enviamos um e-mail para confirmar sua inscrição.', 'vue-blocks' ),
		'confirmed' => __( 'Inscrição confirmada. Obrigado!', 'vue-blocks' ),
		'exists'    => __( 'Este e-mail já está inscrito.', 'vue-blocks' ),
		'invalid'   => __( 'Informe um e-mail válido.', 'vue-blocks' ),
		'limit'     => __( 'Muitas tentativas. Tente novamente mais tarde.', 'vue-blocks' ),
		'error'     => __( 'Não foi possível concluir a inscrição. Tente novamente.', 'vue-blocks' ),
	);
	return $messages[ $status ] ?? '';
}
