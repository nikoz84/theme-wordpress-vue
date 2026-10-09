<?php
/**
 * Vue Blocks — Nav Walker for the Safe Mídia navbar.
 *
 * Renders a flat list of links (no <ul>/<li> wrapper) so the Safe Mídia
 * CSS at `.nav-links > a` / `.mobile-menu > a` binds directly without
 * extra selectors. Sub-items (if any) are flattened into the same list.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Vue_Blocks_Nav_Walker' ) ) {

	/**
	 * Custom walker that emits a flat list of <a> elements.
	 *
	 * Used by the Safe Mídia navbar's `wp_nav_menu()` calls in
	 * `theme/template-parts/header/navbar.php`.
	 */
	class Vue_Blocks_Nav_Walker extends Walker_Nav_Menu {

		public function start_lvl( &$output, $depth = 0, $args = null ) {}

		public function end_lvl( &$output, $depth = 0, $args = null ) {}

		public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
			$item    = $data_object;
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;

			$atts           = array();
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target ) ? $item->target : '';
			$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
			$atts['href']   = ! empty( $item->url ) ? $item->url : '#';

			if ( in_array( 'current-menu-item', $classes, true ) ) {
				$atts['class']        = 'active';
				$atts['aria-current'] = 'page';
			}

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';
			foreach ( $atts as $attr_name => $value ) {
				if ( '' === $value || null === $value ) {
					continue;
				}
				$value       = ( 'href' === $attr_name ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr_name . '="' . $value . '"';
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			$item_output = '<a' . $attributes . '>' . $title . '</a>';

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}

		public function end_el( &$output, $data_object, $depth = 0, $args = null ) {}

	}
}
