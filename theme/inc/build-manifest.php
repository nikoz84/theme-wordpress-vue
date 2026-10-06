<?php
/**
 * Vue Blocks — Build Manifest Reader
 *
 * Helpers for the opt-in Vite build path (Constitution Principle V
 * as amended at v1.1.0: CDN-First Distribution with Optional Build
 * Path).
 *
 * The opt-in is controlled by the constant `VB_USE_BUNDLED_ASSETS`,
 * defined in `functions.php` and defaulting to `false`. When `true`,
 * `vb_enqueue_bundled_assets()` reads `dist/manifest.json` (emitted by
 * Vite's build) and enqueues the bundled JS and CSS via the standard
 * WordPress enqueue APIs, resolving hashed filenames from the manifest.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'vb_read_build_manifest' ) ) {

	/**
	 * Read and decode the Vite build manifest emitted at dist/manifest.json.
	 *
	 * Returns the decoded manifest as an associative array, or `null` if the
	 * file is missing or unreadable. The opt-in switch must handle `null`
	 * loudly per FR-011 (no silent fallback to CDN-first).
	 *
	 * @return array|null
	 */
	function vb_read_build_manifest() {
		$path = VB_THEME_DIR . '/dist/manifest.json';
		if ( ! is_readable( $path ) ) {
			return null;
		}
		$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $raw ) {
			return null;
		}
		$manifest = json_decode( $raw, true );
		if ( ! is_array( $manifest ) ) {
			return null;
		}
		return $manifest;
	}

	/**
	 * Emit an actionable error when the opt-in switch is on but the
	 * build's manifest is missing or unreadable.
	 *
	 * Per FR-011, the failure is loud. We use `trigger_error` so that the
	 * site renders nothing broken when WP_DEBUG is on (and so a developer
	 * can wire wp_die in production via a filter). Per the spec, no silent
	 * fallback to the CDN-first path is permitted.
	 */
	function vb_loud_fail_missing_manifest() {
		$msg = '[vue-blocks] VB_USE_BUNDLED_ASSETS is true but dist/manifest.json is missing or unreadable. Either run `npm run build` to populate dist/ or set VB_USE_BUNDLED_ASSETS to false in functions.php.';
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error( $msg, E_USER_ERROR );
	}

	/**
	 * Enqueue the bundled JS and CSS artifacts from `dist/`.
	 *
	 * Resolves hashed filenames from the build manifest and enqueues them
	 * via `wp_enqueue_script` / `wp_enqueue_style`. Source-map files
	 * (`*.map`) MUST NOT be enqueued (per FR-012).
	 *
	 * @param array $manifest Decoded manifest from `vb_read_build_manifest()`.
	 */
	function vb_enqueue_bundled_assets( $manifest ) {
		// JS — entry: `app`.
		if ( isset( $manifest['app']['file'] ) ) {
			$file = (string) $manifest['app']['file'];
			if ( substr( $file, -3 ) === '.js' ) {
				$src = VB_THEME_URI . '/dist/' . $file;
				wp_enqueue_script(
					'vue-blocks-app',
					$src,
					array( 'vue-js' ),
					VB_VERSION,
					true
				);
			}
		}

		// CSS — entry: `style`.
		if ( isset( $manifest['style']['file'] ) ) {
			$file = (string) $manifest['style']['file'];
			if ( substr( $file, -4 ) === '.css' ) {
				$src = VB_THEME_URI . '/dist/' . $file;
				wp_enqueue_style(
					'vue-blocks-style',
					$src,
					array(),
					VB_VERSION
				);
			}
		}
	}
}