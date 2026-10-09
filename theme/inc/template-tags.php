<?php
/**
 * Funções auxiliares (template tags) usadas dentro dos arquivos de template.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exibe metadados do post (data, autor, categoria) de forma padronizada.
 */
if ( ! function_exists( 'vb_posted_on' ) ) {
	function vb_posted_on() {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);

		printf(
			/* translators: 1: link do autor, 2: data de publicação */
			wp_kses_post( '%2$s por <span class="author vcard">%1$s</span>' ),
			'<a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a>',
			$time_string
		);
	}
}

/**
 * Exibe as categorias e tags de um post.
 */
if ( ! function_exists( 'vb_entry_footer' ) ) {
	function vb_entry_footer() {
		if ( 'post' === get_post_type() ) {
			$categories_list = get_the_category_list( ', ' );
			if ( $categories_list ) {
				printf( '<span class="cat-links">%1$s: %2$s</span> ', esc_html__( 'Categorias', 'vue-blocks' ), wp_kses_post( $categories_list ) );
			}

			$tags_list = get_the_tag_list( '', ', ' );
			if ( $tags_list ) {
				printf( '<span class="tags-links">%1$s: %2$s</span>', esc_html__( 'Tags', 'vue-blocks' ), wp_kses_post( $tags_list ) );
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link"> &middot; ';
			comments_popup_link( esc_html__( 'Deixe um comentário', 'vue-blocks' ), '1 %', '% ' . esc_html__( 'comentários', 'vue-blocks' ) );
			echo '</span>';
		}
	}
}

/**
 * Renderiza a paginação numérica clássica (usada quando não estamos na home via Vue).
 */
if ( ! function_exists( 'vb_pagination' ) ) {
	function vb_pagination() {
		the_posts_pagination(
			array(
				'mid_size'  => 2,
				'prev_text' => __( '&laquo; Anterior', 'vue-blocks' ),
				'next_text' => __( 'Próximo &raquo;', 'vue-blocks' ),
			)
		);
	}
}


/**
 * Safe Mídia home-page helpers.
 *
 * Small functions used by template-parts/hero/, template-parts/news/,
 * and template-parts/header/.
 */

if ( ! function_exists( 'vb_placeholder_image' ) ) {
	/**
	 * Return the skeleton placeholder image URL for posts without a
	 * featured image (local SVG — no external requests).
	 *
	 * @param int|null $seed Unused; kept for backwards compatibility.
	 * @return string
	 */
	function vb_placeholder_image( $seed = null ) {
		return VB_THEME_URI . '/assets/images/skeleton.svg';
	}
}

if ( ! function_exists( 'vb_time_ago_short' ) ) {
	/**
	 * Short Portuguese "time ago" string ("Há 2 horas", "Há 3 dias")
	 * for the hero meta bar and sidebar post labels.
	 *
	 * @param int|null $post_id Optional post ID; defaults to current post.
	 * @return string
	 */
	function vb_time_ago_short( $post_id = null ) {
		$post_id = $post_id ? absint( $post_id ) : get_the_ID();
		if ( ! $post_id ) {
			return '';
		}
		$post_time = get_post_time( 'U', true, $post_id );
		$now       = current_time( 'timestamp' );
		$diff      = (int) ( $now - $post_time );
		if ( $diff < 60 ) {
			return __( 'Agora mesmo', 'vue-blocks' );
		}
		if ( $diff < HOUR_IN_SECONDS ) {
			$m = intdiv( $diff, MINUTE_IN_SECONDS );
			/* translators: %d: minutes */
			return sprintf( _n( 'Há %d minuto', 'Há %d minutos', $m, 'vue-blocks' ), $m );
		}
		if ( $diff < DAY_IN_SECONDS ) {
			$h = intdiv( $diff, HOUR_IN_SECONDS );
			/* translators: %d: hours */
			return sprintf( _n( 'Há %d hora', 'Há %d horas', $h, 'vue-blocks' ), $h );
		}
		if ( $diff < WEEK_IN_SECONDS ) {
			$d = intdiv( $diff, DAY_IN_SECONDS );
			/* translators: %d: days */
			return sprintf( _n( 'Há %d dia', 'Há %d dias', $d, 'vue-blocks' ), $d );
		}
		$w = intdiv( $diff, WEEK_IN_SECONDS );
		/* translators: %d: weeks */
		return sprintf( _n( 'Há %d semana', 'Há %d semanas', $w, 'vue-blocks' ), $w );
	}
}

if ( ! function_exists( 'vb_first_category' ) ) {
	/**
	 * Return the first category name for the current post (or a given post ID).
	 *
	 * @param int|null $post_id Optional post ID; defaults to current post.
	 * @return string Empty string if no category.
	 */
	function vb_first_category( $post_id = null ) {
		$post_id = $post_id ? absint( $post_id ) : get_the_ID();
		if ( ! $post_id ) {
			return '';
		}
		$cats = get_the_category( $post_id );
		if ( empty( $cats ) ) {
			return '';
		}
		// Prefer a real category over the default ("Uncategorized").
		$default = (int) get_option( 'default_category' );
		foreach ( $cats as $cat ) {
			if ( (int) $cat->term_id !== $default ) {
				return $cat->name;
			}
		}
		return $cats[0]->name;
	}
}


/**
 * Colunista cards for the "Colunistas" home section.
 *
 * Returns one card per distinct post_author in the "colunistas"
 * category (per data-model E2 of feature 005). Each card carries
 * the author's display name, avatar (or a placeholder), and the
 * most-recent post title + permalink.
 *
 * Returns an empty array when no posts exist in "colunistas" — the
 * template part renders nothing in that case (per FR-004 empty-state
 * rule).
 *
 * @param int $limit Maximum number of cards to return. Default 3.
 * @return array<int, array<string, mixed>>
 */
if ( ! function_exists( 'vb_get_colunista_cards' ) ) {
	function vb_get_colunista_cards( $limit = 3 ) {
		$cat = get_category_by_slug( 'colunistas' );
		if ( ! $cat ) {
			return array();
		}

		$posts = get_posts(
			array(
				'cat'           => $cat->term_id,
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
		if ( empty( $posts ) ) {
			return array();
		}

		$cards        = array();
		$seen_authors = array();
		foreach ( $posts as $post ) {
			if ( in_array( $post->post_author, $seen_authors, true ) ) {
				continue;
			}
			$seen_authors[] = (int) $post->post_author;

			$cards[] = array(
				'author_id'           => (int) $post->post_author,
				'author_display_name' => get_the_author_meta( 'display_name', $post->post_author ),
				'author_bio'          => get_the_author_meta( 'user_description', $post->post_author ),
				'avatar_url'          => get_avatar_url( $post->post_author, array( 'size' => 52 ) ),
				'latest_post_id'      => (int) $post->ID,
				'latest_post_title'   => get_the_title( $post ),
				'latest_post_permalink' => get_permalink( $post ),
				'latest_post_thumbnail_url' => get_the_post_thumbnail_url( $post, 'vb-card' ),
			);

			if ( count( $cards ) >= $limit ) {
				break;
			}
		}

		return $cards;
	}
}
