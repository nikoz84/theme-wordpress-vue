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
	 * Return a deterministic placeholder image URL for posts without
	 * a featured image. Uses picsum.photos with a stable seed so each
	 * missing-image post gets a different image.
	 *
	 * @param int|null $seed Optional seed (defaults to post ID when
	 *                       called inside the loop).
	 * @return string
	 */
	function vb_placeholder_image( $seed = null ) {
		if ( null === $seed && get_the_ID() ) {
			$seed = get_the_ID();
		}
		$seed = $seed ? absint( $seed ) : absint( wp_rand( 1, 9999 ) );
		return 'https://picsum.photos/seed/vue-blocks/' . $seed . '/480/270';
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
		return $cats[0]->name;
	}
}
