<?php
/**
 * Vue Blocks — export the current site as theme demo content.
 *
 * Writes JSON in the format read by theme/inc/demo-import.php to stdout.
 * Run inside the Docker harness (see `npm run demo:export`):
 *
 *   wp eval-file /bootstrap/bin/export-demo.php > theme/demo/demo-content.json
 *
 * Exports published posts and pages, categories (except Uncategorized),
 * non-admin authors of exported posts, and the menus assigned to the
 * `primary` / `footer` locations. Featured images are not exported —
 * the theme shows skeleton placeholders instead.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$vb_now  = time();
$vb_data = array(
	'version'    => 1,
	'authors'    => array(),
	'categories' => array(),
	'posts'      => array(),
	'pages'      => array(),
	'menus'      => array(),
);

foreach ( get_categories( array( 'hide_empty' => false ) ) as $vb_cat ) {
	if ( 'uncategorized' === $vb_cat->slug ) {
		continue;
	}
	$vb_data['categories'][] = array(
		'slug'        => $vb_cat->slug,
		'name'        => $vb_cat->name,
		'description' => $vb_cat->description,
	);
}

$vb_author_keys = array();
foreach ( array( 'post' => 'posts', 'page' => 'pages' ) as $vb_type => $vb_key ) {
	$vb_items = get_posts(
		array(
			'post_type'      => $vb_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'exclude'        => array( (int) get_option( 'wp_page_for_privacy_policy' ) ),
		)
	);
	foreach ( $vb_items as $vb_post ) {
		$vb_item = array(
			'slug'      => $vb_post->post_name,
			'title'     => $vb_post->post_title,
			'hours_ago' => max( 0, (int) round( ( $vb_now - strtotime( $vb_post->post_date_gmt . ' UTC' ) ) / HOUR_IN_SECONDS ) ),
			'excerpt'   => $vb_post->post_excerpt,
			'content'   => $vb_post->post_content,
		);

		if ( 'post' === $vb_type ) {
			$vb_item['categories'] = array_values(
				array_diff( wp_get_post_categories( $vb_post->ID, array( 'fields' => 'slugs' ) ), array( 'uncategorized' ) )
			);

			$vb_user = get_userdata( (int) $vb_post->post_author );
			if ( $vb_user && ! in_array( 'administrator', (array) $vb_user->roles, true ) ) {
				$vb_author_key            = preg_replace( '/^vb-demo-/', '', $vb_user->user_login );
				$vb_item['author']        = $vb_author_key;
				$vb_author_keys[ $vb_author_key ] = $vb_user;
			}

			$vb_item['tags'] = wp_get_post_tags( $vb_post->ID, array( 'fields' => 'names' ) );

			$vb_meta = array();
			foreach ( get_post_meta( $vb_post->ID ) as $vb_meta_key => $vb_values ) {
				if ( ( 0 === strpos( $vb_meta_key, 'vb_segurado_' ) || 'vb_views' === $vb_meta_key ) && '' !== $vb_values[0] ) {
					$vb_meta[ $vb_meta_key ] = $vb_values[0];
				}
			}
			if ( $vb_meta ) {
				$vb_item['meta'] = $vb_meta;
			}
		}

		$vb_data[ $vb_key ][] = $vb_item;
	}
}

foreach ( $vb_author_keys as $vb_author_key => $vb_user ) {
	$vb_data['authors'][] = array(
		'key'          => $vb_author_key,
		'display_name' => $vb_user->display_name,
		'first_name'   => $vb_user->first_name,
		'last_name'    => $vb_user->last_name,
		'description'  => $vb_user->description,
		'job_title'    => (string) get_user_meta( $vb_user->ID, 'vb_job_title', true ),
	);
}

$vb_locations = get_nav_menu_locations();
foreach ( array( 'primary', 'footer' ) as $vb_location ) {
	if ( empty( $vb_locations[ $vb_location ] ) ) {
		continue;
	}
	$vb_menu = wp_get_nav_menu_object( $vb_locations[ $vb_location ] );
	if ( ! $vb_menu ) {
		continue;
	}
	$vb_menu_items = array();
	foreach ( (array) wp_get_nav_menu_items( $vb_menu->term_id ) as $vb_menu_item ) {
		if ( 'taxonomy' === $vb_menu_item->type && 'category' === $vb_menu_item->object ) {
			$vb_term = get_term( (int) $vb_menu_item->object_id, 'category' );
			if ( $vb_term && ! is_wp_error( $vb_term ) ) {
				$vb_menu_items[] = array( 'type' => 'category', 'slug' => $vb_term->slug, 'title' => $vb_menu_item->title );
			}
		} elseif ( 'post_type' === $vb_menu_item->type && 'page' === $vb_menu_item->object ) {
			$vb_page = get_post( (int) $vb_menu_item->object_id );
			if ( $vb_page ) {
				$vb_menu_items[] = array( 'type' => 'page', 'slug' => $vb_page->post_name, 'title' => $vb_menu_item->title );
			}
		} else {
			// Store site-relative URLs so the demo works on any domain.
			$vb_url          = str_replace( untrailingslashit( home_url() ), '', $vb_menu_item->url );
			$vb_menu_items[] = array( 'type' => 'custom', 'url' => $vb_url, 'title' => $vb_menu_item->title );
		}
	}
	$vb_data['menus'][ $vb_location ] = array(
		'name'  => $vb_menu->name,
		'items' => $vb_menu_items,
	);
}

echo wp_json_encode( $vb_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n";
