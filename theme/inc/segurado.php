<?php
/**
 * Vue Blocks — Segurado helper functions.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize segurado document type.
 *
 * @param string $value The document type value.
 * @return string Sanitized document type.
 */
function vb_sanitize_segurado_document_type( $value ) {
	$vb_allowed = array( 'cpf', 'cnpj', 'passport', 'other' );
	$value      = strtolower( sanitize_text_field( $value ) );
	return in_array( $value, $vb_allowed, true ) ? $value : 'other';
}

/**
 * Sanitize segurado phone number.
 *
 * @param string $value The phone number value.
 * @return string Sanitized phone number.
 */
function vb_sanitize_segurado_phone( $value ) {
	$value = sanitize_text_field( $value );
	$value = preg_replace( '/[^0-9+\-\s()]/', '', $value );
	return mb_substr( $value, 0, 20 );
}

/**
 * Validate segurado data.
 *
 * @param array $data The segurado data to validate.
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function vb_validate_segurado_data( $data ) {
	$vb_errors = new WP_Error();

	if ( empty( $data['vb_segurado_full_name'] ) ) {
		$vb_errors->add( 'vb_segurado_full_name', __( 'Full name is required.', 'vue-blocks' ) );
	} elseif ( mb_strlen( $data['vb_segurado_full_name'] ) > 200 ) {
		$vb_errors->add( 'vb_segurado_full_name', __( 'Full name must be 200 characters or less.', 'vue-blocks' ) );
	}

	if ( empty( $data['vb_segurado_document_type'] ) ) {
		$vb_errors->add( 'vb_segurado_document_type', __( 'Document type is required.', 'vue-blocks' ) );
	} elseif ( ! in_array( $data['vb_segurado_document_type'], array( 'cpf', 'cnpj', 'passport', 'other' ), true ) ) {
		$vb_errors->add( 'vb_segurado_document_type', __( 'Invalid document type.', 'vue-blocks' ) );
	}

	if ( empty( $data['vb_segurado_document_number'] ) ) {
		$vb_errors->add( 'vb_segurado_document_number', __( 'Document number is required.', 'vue-blocks' ) );
	} else {
		$vb_doc_type = isset( $data['vb_segurado_document_type'] ) ? $data['vb_segurado_document_type'] : '';
		$vb_doc_num  = sanitize_text_field( $data['vb_segurado_document_number'] );

		if ( 'cpf' === $vb_doc_type && ! preg_match( '/^\d{11}$/', $vb_doc_num ) ) {
			$vb_errors->add( 'vb_segurado_document_number', __( 'CPF must be 11 digits.', 'vue-blocks' ) );
		} elseif ( 'cnpj' === $vb_doc_type && ! preg_match( '/^\d{14}$/', $vb_doc_num ) ) {
			$vb_errors->add( 'vb_segurado_document_number', __( 'CNPJ must be 14 digits.', 'vue-blocks' ) );
		}
	}

	if ( ! empty( $data['vb_segurado_email'] ) && ! is_email( $data['vb_segurado_email'] ) ) {
		$vb_errors->add( 'vb_segurado_email', __( 'Invalid email address.', 'vue-blocks' ) );
	}

	if ( ! empty( $data['vb_segurado_phone'] ) && mb_strlen( $data['vb_segurado_phone'] ) > 20 ) {
		$vb_errors->add( 'vb_segurado_phone', __( 'Phone must be 20 characters or less.', 'vue-blocks' ) );
	}

	if ( ! empty( $data['vb_segurado_address_1'] ) && mb_strlen( $data['vb_segurado_address_1'] ) > 200 ) {
		$vb_errors->add( 'vb_segurado_address_1', __( 'Address line 1 must be 200 characters or less.', 'vue-blocks' ) );
	}

	if ( ! empty( $data['vb_segurado_address_2'] ) && mb_strlen( $data['vb_segurado_address_2'] ) > 200 ) {
		$vb_errors->add( 'vb_segurado_address_2', __( 'Address line 2 must be 200 characters or less.', 'vue-blocks' ) );
	}

	if ( ! empty( $data['vb_segurado_city'] ) && mb_strlen( $data['vb_segurado_city'] ) > 100 ) {
		$vb_errors->add( 'vb_segurado_city', __( 'City must be 100 characters or less.', 'vue-blocks' ) );
	}

	if ( ! empty( $data['vb_segurado_state'] ) && mb_strlen( $data['vb_segurado_state'] ) > 100 ) {
		$vb_errors->add( 'vb_segurado_state', __( 'State must be 100 characters or less.', 'vue-blocks' ) );
	}

	if ( ! empty( $data['vb_segurado_postal_code'] ) && mb_strlen( $data['vb_segurado_postal_code'] ) > 20 ) {
		$vb_errors->add( 'vb_segurado_postal_code', __( 'Postal code must be 20 characters or less.', 'vue-blocks' ) );
	}

	return $vb_errors->has_errors() ? $vb_errors : true;
}

/**
 * Get segurado data for a post.
 *
 * @param int $post_id The post ID.
 * @return array|null Segurado data array or null if no data exists.
 */
function vb_get_segurado( $post_id ) {
	$vb_full_name = get_post_meta( $post_id, 'vb_segurado_full_name', true );

	if ( empty( $vb_full_name ) ) {
		return null;
	}

	return array(
		'full_name'        => $vb_full_name,
		'document_type'    => get_post_meta( $post_id, 'vb_segurado_document_type', true ),
		'document_number'  => get_post_meta( $post_id, 'vb_segurado_document_number', true ),
		'email'            => get_post_meta( $post_id, 'vb_segurado_email', true ),
		'phone'            => get_post_meta( $post_id, 'vb_segurado_phone', true ),
		'address'          => array(
			'line_1'     => get_post_meta( $post_id, 'vb_segurado_address_1', true ),
			'line_2'     => get_post_meta( $post_id, 'vb_segurado_address_2', true ),
			'city'       => get_post_meta( $post_id, 'vb_segurado_city', true ),
			'state'      => get_post_meta( $post_id, 'vb_segurado_state', true ),
			'postal_code' => get_post_meta( $post_id, 'vb_segurado_postal_code', true ),
			'country'    => get_post_meta( $post_id, 'vb_segurado_country', true ) ?: 'BR',
		),
	);
}

/**
 * Update segurado data for a post.
 *
 * @param int   $post_id The post ID.
 * @param array $data    The segurado data to save.
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function vb_update_segurado( $post_id, $data ) {
	$vb_validation = vb_validate_segurado_data( $data );

	if ( is_wp_error( $vb_validation ) ) {
		return $vb_validation;
	}

	$vb_fields = array(
		'vb_segurado_full_name',
		'vb_segurado_document_type',
		'vb_segurado_document_number',
		'vb_segurado_email',
		'vb_segurado_phone',
		'vb_segurado_address_1',
		'vb_segurado_address_2',
		'vb_segurado_city',
		'vb_segurado_state',
		'vb_segurado_postal_code',
		'vb_segurado_country',
	);

	foreach ( $vb_fields as $vb_field ) {
		if ( isset( $data[ $vb_field ] ) ) {
			update_post_meta( $post_id, $vb_field, $data[ $vb_field ] );
		}
	}

	return true;
}

/**
 * Handle segurado form submission (non-JS fallback).
 *
 * Hooked to admin_post_vb_update_segurado and admin_post_nopriv_vb_update_segurado.
 */
function vb_handle_segurado_form_submission() {
	$vb_post_id = isset( $_POST['vb_segurado_post_id'] ) ? absint( $_POST['vb_segurado_post_id'] ) : 0;

	if ( ! $vb_post_id ) {
		wp_die( esc_html__( 'Invalid post ID.', 'vue-blocks' ) );
	}

	if ( ! isset( $_POST['vb_segurado_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vb_segurado_nonce'] ) ), 'vb_update_segurado' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'vue-blocks' ) );
	}

	if ( ! current_user_can( 'edit_post', $vb_post_id ) ) {
		wp_die( esc_html__( 'You do not have permission to edit this post.', 'vue-blocks' ) );
	}

	$vb_data = array(
		'vb_segurado_full_name'        => isset( $_POST['vb_segurado_full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_segurado_full_name'] ) ) : '',
		'vb_segurado_document_type'    => isset( $_POST['vb_segurado_document_type'] ) ? vb_sanitize_segurado_document_type( wp_unslash( $_POST['vb_segurado_document_type'] ) ) : '',
		'vb_segurado_document_number'  => isset( $_POST['vb_segurado_document_number'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_segurado_document_number'] ) ) : '',
		'vb_segurado_email'            => isset( $_POST['vb_segurado_email'] ) ? sanitize_email( wp_unslash( $_POST['vb_segurado_email'] ) ) : '',
		'vb_segurado_phone'            => isset( $_POST['vb_segurado_phone'] ) ? vb_sanitize_segurado_phone( wp_unslash( $_POST['vb_segurado_phone'] ) ) : '',
		'vb_segurado_address_1'        => isset( $_POST['vb_segurado_address_1'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_segurado_address_1'] ) ) : '',
		'vb_segurado_address_2'        => isset( $_POST['vb_segurado_address_2'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_segurado_address_2'] ) ) : '',
		'vb_segurado_city'             => isset( $_POST['vb_segurado_city'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_segurado_city'] ) ) : '',
		'vb_segurado_state'            => isset( $_POST['vb_segurado_state'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_segurado_state'] ) ) : '',
		'vb_segurado_postal_code'      => isset( $_POST['vb_segurado_postal_code'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_segurado_postal_code'] ) ) : '',
		'vb_segurado_country'          => isset( $_POST['vb_segurado_country'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_segurado_country'] ) ) : 'BR',
	);

	$vb_result = vb_update_segurado( $vb_post_id, $vb_data );

	$vb_redirect = get_permalink( $vb_post_id );

	if ( is_wp_error( $vb_result ) ) {
		$vb_redirect = add_query_arg( 'vb_segurado_error', '1', $vb_redirect );
	} else {
		$vb_redirect = add_query_arg( 'vb_segurado_success', '1', $vb_redirect );
	}

	wp_safe_redirect( $vb_redirect );
	exit;
}
add_action( 'admin_post_vb_update_segurado', 'vb_handle_segurado_form_submission' );
add_action( 'admin_post_nopriv_vb_update_segurado', 'vb_handle_segurado_form_submission' );