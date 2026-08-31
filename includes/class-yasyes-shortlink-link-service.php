<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save/update logic for links from $_POST — used by both PHP fallback and AJAX.
 */
class Yasyes_Shortlink_Link_Service {

	/**
	 * @return int|WP_Error New link ID.
	 */
	public static function create_from_request() {
		$url = Yasyes_Shortlink_Validator::validate_original_url( wp_unslash( $_POST['original_url'] ?? '' ) );
		if ( is_wp_error( $url ) ) {
			return $url;
		}

		$custom_alias = trim( wp_unslash( $_POST['alias'] ?? '' ) );

		try {
			$code = '' !== $custom_alias
				? Yasyes_Shortlink_Validator::validate_custom_code( $custom_alias )
				: Yasyes_Shortlink_Validator::generate_random_code();
		} catch ( RuntimeException $e ) {
			return new WP_Error( 'yasyes_shortlink_generate_failed', 'Failed to generate a unique code. Try again.' );
		}

		if ( is_wp_error( $code ) ) {
			return $code;
		}

		return Yasyes_Shortlink_Link_Model::create(
			array(
				'original_url' => $url,
				'short_code'   => $code,
				'expired_at'   => sanitize_text_field( wp_unslash( $_POST['expired_at'] ?? '' ) ) ?: null,
			)
		);
	}

	/**
	 * @return int|WP_Error Updated link ID.
	 */
	public static function update_from_request() {
		$link_id = absint( $_POST['link_id'] ?? 0 );
		$link    = Yasyes_Shortlink_Link_Model::find( $link_id );

		if ( ! $link ) {
			return new WP_Error( 'yasyes_shortlink_not_found', 'Link not found.' );
		}

		$url = Yasyes_Shortlink_Validator::validate_original_url( wp_unslash( $_POST['original_url'] ?? '' ) );
		if ( is_wp_error( $url ) ) {
			return $url;
		}

		$data = array(
			'original_url' => $url,
			'expired_at'   => sanitize_text_field( wp_unslash( $_POST['expired_at'] ?? '' ) ) ?: null,
		);

		$new_alias = sanitize_text_field( wp_unslash( $_POST['alias'] ?? '' ) );

		if ( '' === $new_alias ) {
			return new WP_Error( 'yasyes_shortlink_empty_code', 'Alias cannot be empty.' );
		}

		if ( $new_alias !== $link->short_code ) {
			$validated = Yasyes_Shortlink_Validator::validate_custom_code( $new_alias );
			if ( is_wp_error( $validated ) ) {
				return $validated;
			}
			$data['short_code'] = $validated;
		}

		if ( ! Yasyes_Shortlink_Link_Model::update( $link_id, $data ) ) {
			return new WP_Error( 'yasyes_shortlink_update_failed', 'Failed to update the link.' );
		}

		return $link_id;
	}
}
