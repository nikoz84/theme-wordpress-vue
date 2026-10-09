<?php
/**
 * Vue Blocks — Demo content importer.
 *
 * Small built-in importer for `demo/demo-content.json` (authors,
 * categories, posts, pages and menus). Triggered from
 * Appearance → Customize → "Demo content", or via WP-CLI:
 *
 *   wp vue-blocks demo import|remove|status
 *
 * Everything the importer creates is flagged with `_vb_demo` meta so
 * "Remove" deletes only demo items. Existing content with the same
 * slug is reused and never modified or deleted.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VB_DEMO_FLAG', '_vb_demo' );
define( 'VB_DEMO_STATE_OPTION', 'vb_demo_state' );

/**
 * Load and decode the demo dataset.
 *
 * @return array|WP_Error
 */
function vb_demo_data() {
	$file = VB_THEME_DIR . '/demo/demo-content.json';
	if ( ! is_readable( $file ) ) {
		return new WP_Error( 'vb_demo_missing', __( 'Demo content file not found.', 'vue-blocks' ) );
	}
	$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'vb_demo_invalid', __( 'Demo content file is not valid JSON.', 'vue-blocks' ) );
	}
	return $data;
}

/**
 * Whether the demo content is currently imported.
 *
 * @return bool
 */
function vb_demo_is_imported() {
	return (bool) get_option( VB_DEMO_STATE_OPTION );
}

/**
 * Convert demo content (string or array of paragraphs) to block markup.
 *
 * @param string|array $content Raw content.
 * @return string
 */
function vb_demo_content_html( $content ) {
	if ( ! is_array( $content ) ) {
		return wp_kses_post( (string) $content );
	}
	$html = '';
	foreach ( $content as $block ) {
		$block = wp_kses_post( $block );
		if ( preg_match( '#^<h([2-6])>(.*)</h\1>$#s', $block, $m ) ) {
			$attrs = '2' === $m[1] ? '' : ' {"level":' . $m[1] . '}';
			$html .= "<!-- wp:heading{$attrs} -->\n<h{$m[1]} class=\"wp-block-heading\">{$m[2]}</h{$m[1]}>\n<!-- /wp:heading -->\n\n";
		} elseif ( preg_match( '#^<(ul|ol|blockquote|figure|div)\b#', $block ) ) {
			$html .= "<!-- wp:html -->\n{$block}\n<!-- /wp:html -->\n\n";
		} else {
			$html .= "<!-- wp:paragraph -->\n<p>{$block}</p>\n<!-- /wp:paragraph -->\n\n";
		}
	}
	return $html;
}

/**
 * Import the demo content.
 *
 * @return array|WP_Error Counts of created items, or an error.
 */
function vb_demo_import() {
	if ( vb_demo_is_imported() ) {
		return new WP_Error( 'vb_demo_already', __( 'Demo content is already imported.', 'vue-blocks' ) );
	}

	$data = vb_demo_data();
	if ( is_wp_error( $data ) ) {
		return $data;
	}

	$counts = array(
		'authors'    => 0,
		'categories' => 0,
		'posts'      => 0,
		'pages'      => 0,
		'menus'      => 0,
	);

	$fallback_author = get_current_user_id();
	if ( ! $fallback_author ) {
		$admins          = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
		$fallback_author = $admins ? (int) $admins[0] : 1;
	}

	// 1. Authors (columnists). Random password, no real e-mail.
	$authors = array();
	foreach ( (array) ( $data['authors'] ?? array() ) as $author ) {
		$login   = 'vb-demo-' . sanitize_user( $author['key'], true );
		$user_id = username_exists( $login );
		if ( ! $user_id ) {
			$user_id = wp_insert_user(
				array(
					'user_login'   => $login,
					'user_pass'    => wp_generate_password( 32, true, true ),
					'user_email'   => $login . '@example.invalid',
					'display_name' => $author['display_name'] ?? $login,
					'first_name'   => $author['first_name'] ?? '',
					'last_name'    => $author['last_name'] ?? '',
					'description'  => $author['description'] ?? '',
					'role'         => 'author',
				)
			);
			if ( is_wp_error( $user_id ) ) {
				continue;
			}
			update_user_meta( $user_id, VB_DEMO_FLAG, 1 );
			if ( ! empty( $author['job_title'] ) ) {
				update_user_meta( $user_id, 'vb_job_title', sanitize_text_field( $author['job_title'] ) );
			}
			$counts['authors']++;
		}
		$authors[ $author['key'] ] = (int) $user_id;
	}

	// 2. Categories.
	$categories = array();
	foreach ( (array) ( $data['categories'] ?? array() ) as $cat ) {
		$term = get_term_by( 'slug', $cat['slug'], 'category' );
		if ( $term ) {
			$categories[ $cat['slug'] ] = (int) $term->term_id;
			continue;
		}
		$result = wp_insert_term(
			$cat['name'],
			'category',
			array(
				'slug'        => $cat['slug'],
				'description' => $cat['description'] ?? '',
			)
		);
		if ( is_wp_error( $result ) ) {
			continue;
		}
		update_term_meta( $result['term_id'], VB_DEMO_FLAG, 1 );
		$categories[ $cat['slug'] ] = (int) $result['term_id'];
		$counts['categories']++;
	}

	// 2b. Tags used by the demo posts.
	$tags = array();
	foreach ( (array) ( $data['posts'] ?? array() ) as $item ) {
		foreach ( (array) ( $item['tags'] ?? array() ) as $tag_name ) {
			if ( isset( $tags[ $tag_name ] ) ) {
				continue;
			}
			$term = get_term_by( 'name', $tag_name, 'post_tag' );
			if ( $term ) {
				$tags[ $tag_name ] = (int) $term->term_id;
				continue;
			}
			$result = wp_insert_term( $tag_name, 'post_tag' );
			if ( is_wp_error( $result ) ) {
				continue;
			}
			update_term_meta( $result['term_id'], VB_DEMO_FLAG, 1 );
			$tags[ $tag_name ] = (int) $result['term_id'];
		}
	}

	// 3. Posts and pages (no featured images — the theme shows skeleton placeholders).
	$now = time();
	foreach ( array( 'post' => 'posts', 'page' => 'pages' ) as $post_type => $key ) {
		foreach ( (array) ( $data[ $key ] ?? array() ) as $item ) {
			if ( get_page_by_path( $item['slug'], OBJECT, $post_type ) ) {
				continue;
			}

			$date_gmt = gmdate( 'Y-m-d H:i:s', $now - (int) ( $item['hours_ago'] ?? 0 ) * HOUR_IN_SECONDS );
			$meta     = array( VB_DEMO_FLAG => 1 );
			foreach ( (array) ( $item['meta'] ?? array() ) as $meta_key => $meta_value ) {
				$meta[ sanitize_key( $meta_key ) ] = $meta_value;
			}

			$post_id = wp_insert_post(
				array(
					'post_type'     => $post_type,
					'post_status'   => 'publish',
					'post_title'    => $item['title'],
					'post_name'     => $item['slug'],
					'post_excerpt'  => $item['excerpt'] ?? '',
					'post_content'  => vb_demo_content_html( $item['content'] ?? '' ),
					'post_author'   => $authors[ $item['author'] ?? '' ] ?? $fallback_author,
					'post_date'     => get_date_from_gmt( $date_gmt ),
					'post_date_gmt' => $date_gmt,
					'post_category' => array_values(
						array_filter(
							array_map(
								function ( $slug ) use ( $categories ) {
									return $categories[ $slug ] ?? 0;
								},
								(array) ( $item['categories'] ?? array() )
							)
						)
					),
					'meta_input'    => $meta,
				),
				true
			);
			if ( is_wp_error( $post_id ) ) {
				continue;
			}
			$post_tags = array_values( array_filter( array_map( function ( $name ) use ( $tags ) {
				return $tags[ $name ] ?? 0;
			}, (array) ( $item['tags'] ?? array() ) ) ) );
			if ( $post_tags ) {
				wp_set_post_terms( $post_id, $post_tags, 'post_tag' );
			}
			$counts[ $key ]++;
		}
	}

	// 4. Menus — only for locations that don't already have a menu.
	$locations = get_nav_menu_locations();
	$assigned  = array();
	foreach ( (array) ( $data['menus'] ?? array() ) as $location => $menu ) {
		if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) {
			continue;
		}
		$menu_id = wp_create_nav_menu( $menu['name'] );
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		update_term_meta( $menu_id, VB_DEMO_FLAG, 1 );

		foreach ( (array) ( $menu['items'] ?? array() ) as $position => $menu_item ) {
			$args = array(
				'menu-item-status'   => 'publish',
				'menu-item-position' => $position + 1,
			);
			if ( 'category' === $menu_item['type'] ) {
				$term = get_term_by( 'slug', $menu_item['slug'], 'category' );
				if ( ! $term ) {
					continue;
				}
				$args += array(
					'menu-item-type'      => 'taxonomy',
					'menu-item-object'    => 'category',
					'menu-item-object-id' => $term->term_id,
				);
			} elseif ( 'page' === $menu_item['type'] ) {
				$page = get_page_by_path( $menu_item['slug'], OBJECT, 'page' );
				if ( ! $page || 'publish' !== $page->post_status ) {
					continue;
				}
				$args += array(
					'menu-item-type'      => 'post_type',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page->ID,
				);
			} else {
				$args += array(
					'menu-item-type' => 'custom',
					'menu-item-url'  => preg_match( '#^https?://#', $menu_item['url'] ?? '' ) ? $menu_item['url'] : home_url( $menu_item['url'] ?? '/' ),
				);
			}
			if ( ! empty( $menu_item['title'] ) ) {
				$args['menu-item-title'] = $menu_item['title'];
			}
			wp_update_nav_menu_item( $menu_id, 0, $args );
		}

		$locations[ $location ] = $menu_id;
		$assigned[]             = $location;
		$counts['menus']++;
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	update_option(
		VB_DEMO_STATE_OPTION,
		array(
			'imported_at' => $now,
			'version'     => (int) ( $data['version'] ?? 1 ),
			'locations'   => $assigned,
			'counts'      => $counts,
		),
		false
	);

	return $counts;
}

/**
 * Remove everything the importer created (items flagged `_vb_demo`).
 *
 * @return array Counts of removed items.
 */
function vb_demo_remove() {
	$counts = array(
		'posts'      => 0,
		'menus'      => 0,
		'categories' => 0,
		'tags'       => 0,
		'authors'    => 0,
	);

	$post_ids = get_posts(
		array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => VB_DEMO_FLAG, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);
	foreach ( $post_ids as $post_id ) {
		if ( wp_delete_post( $post_id, true ) ) {
			$counts['posts']++;
		}
	}

	// wp_delete_nav_menu() also deletes the items and unassigns the locations.
	foreach ( wp_get_nav_menus() as $menu ) {
		if ( get_term_meta( $menu->term_id, VB_DEMO_FLAG, true ) && ! is_wp_error( wp_delete_nav_menu( $menu->term_id ) ) ) {
			$counts['menus']++;
		}
	}

	$term_ids = get_terms(
		array(
			'taxonomy'   => array( 'category', 'post_tag' ),
			'hide_empty' => false,
			'fields'     => 'ids',
			'meta_key'   => VB_DEMO_FLAG, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);
	foreach ( is_array( $term_ids ) ? $term_ids : array() as $term_id ) {
		$term = get_term( $term_id );
		if ( $term && ! is_wp_error( $term ) && true === wp_delete_term( $term_id, $term->taxonomy ) ) {
			$counts['category' === $term->taxonomy ? 'categories' : 'tags']++;
		}
	}

	$user_ids = get_users(
		array(
			'meta_key' => VB_DEMO_FLAG, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'fields'   => 'ID',
		)
	);
	if ( $user_ids ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
		// Reassign anything written later by a demo author instead of deleting it.
		$admins   = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
		$reassign = get_current_user_id() ? get_current_user_id() : ( $admins ? (int) $admins[0] : null );
		foreach ( $user_ids as $user_id ) {
			if ( wp_delete_user( (int) $user_id, $reassign ) ) {
				$counts['authors']++;
			}
		}
	}

	delete_option( VB_DEMO_STATE_OPTION );

	return $counts;
}

/**
 * Human-readable summary of a counts array.
 *
 * @param array $counts Counts from vb_demo_import() / vb_demo_remove().
 * @return string
 */
function vb_demo_summary( $counts ) {
	$parts = array();
	foreach ( $counts as $type => $count ) {
		$parts[] = $count . ' ' . $type;
	}
	return implode( ', ', $parts );
}

/**
 * AJAX handlers for the Customizer buttons.
 */
function vb_demo_ajax() {
	check_ajax_referer( 'vb_demo', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'vue-blocks' ) ), 403 );
	}

	$action = isset( $_POST['demo_action'] ) ? sanitize_key( wp_unslash( $_POST['demo_action'] ) ) : '';
	if ( 'import' === $action ) {
		$result = vb_demo_import();
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		/* translators: %s: summary of created items */
		wp_send_json_success( array( 'imported' => true, 'message' => sprintf( __( 'Demo content imported: %s.', 'vue-blocks' ), vb_demo_summary( $result ) ) ) );
	}
	if ( 'remove' === $action ) {
		$result = vb_demo_remove();
		/* translators: %s: summary of removed items */
		wp_send_json_success( array( 'imported' => false, 'message' => sprintf( __( 'Demo content removed: %s.', 'vue-blocks' ), vb_demo_summary( $result ) ) ) );
	}
	wp_send_json_error( array( 'message' => __( 'Unknown action.', 'vue-blocks' ) ), 400 );
}
add_action( 'wp_ajax_vb_demo', 'vb_demo_ajax' );

/**
 * Customizer section "Demo content" with Import / Remove buttons.
 *
 * @param WP_Customize_Manager $wp_customize The Customizer manager.
 */
function vb_customize_register_demo( $wp_customize ) {
	if ( ! class_exists( 'VB_Demo_Content_Control' ) ) {
		/**
		 * Button control — not bound to a setting (it acts via AJAX).
		 */
		class VB_Demo_Content_Control extends WP_Customize_Control {
			public $type = 'vb_demo_content';

			public function render_content() {
				$imported = vb_demo_is_imported();
				?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<p class="description customize-control-description"><?php echo esc_html( $this->description ); ?></p>
				<p class="vb-demo-status" aria-live="polite">
					<?php echo $imported ? esc_html__( 'Status: demo content is imported.', 'vue-blocks' ) : esc_html__( 'Status: not imported.', 'vue-blocks' ); ?>
				</p>
				<p>
					<button type="button" class="button button-primary vb-demo-btn" data-demo-action="import" <?php disabled( $imported ); ?>><?php esc_html_e( 'Import demo content', 'vue-blocks' ); ?></button>
					<button type="button" class="button vb-demo-btn" data-demo-action="remove" <?php disabled( ! $imported ); ?>><?php esc_html_e( 'Remove demo content', 'vue-blocks' ); ?></button>
				</p>
				<?php
			}
		}
	}

	$wp_customize->add_section(
		'vb_demo',
		array(
			'title'      => __( 'Demo content', 'vue-blocks' ),
			'priority'   => 200,
			'capability' => 'manage_options',
		)
	);

	$wp_customize->add_control(
		new VB_Demo_Content_Control(
			$wp_customize,
			'vb_demo_content',
			array(
				'section'     => 'vb_demo',
				'settings'    => array(),
				'capability'  => 'manage_options',
				'label'       => __( 'Testing data', 'vue-blocks' ),
				'description' => __( 'Loads sample categories, posts, columnists, pages and menus so every home section has content. Existing content is never changed, and "Remove" deletes only the demo items.', 'vue-blocks' ),
			)
		)
	);
}
add_action( 'customize_register', 'vb_customize_register_demo' );

/**
 * Script for the Customizer buttons.
 */
function vb_demo_customizer_scripts() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$file = '/assets/js/customizer-demo.js';
	wp_enqueue_script( 'vb-customizer-demo', VB_THEME_URI . $file, array( 'customize-controls' ), (string) filemtime( VB_THEME_DIR . $file ), true );
	wp_localize_script(
		'vb-customizer-demo',
		'vbDemo',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'vb_demo' ),
			'i18n'    => array(
				'working'       => __( 'Working…', 'vue-blocks' ),
				'confirmRemove' => __( 'Remove all demo content? Your own content is not affected.', 'vue-blocks' ),
				'imported'      => __( 'Status: demo content is imported.', 'vue-blocks' ),
				'notImported'   => __( 'Status: not imported.', 'vue-blocks' ),
				'error'         => __( 'Request failed. Please try again.', 'vue-blocks' ),
			),
		)
	);
}
add_action( 'customize_controls_enqueue_scripts', 'vb_demo_customizer_scripts' );

/**
 * WP-CLI: `wp vue-blocks demo import|remove|status`.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'vue-blocks demo',
		function ( $args ) {
			$sub = $args[0] ?? 'status';
			if ( 'import' === $sub ) {
				$result = vb_demo_import();
				if ( is_wp_error( $result ) ) {
					WP_CLI::warning( $result->get_error_message() );
					return;
				}
				WP_CLI::success( 'Imported: ' . vb_demo_summary( $result ) );
			} elseif ( 'remove' === $sub ) {
				WP_CLI::success( 'Removed: ' . vb_demo_summary( vb_demo_remove() ) );
			} else {
				WP_CLI::log( vb_demo_is_imported() ? 'imported' : 'not imported' );
			}
		},
		array( 'shortdesc' => 'Import, remove or check the Vue Blocks demo content (import|remove|status).' )
	);
}
