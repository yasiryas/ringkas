<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save/update logic for links from $_POST — used by both PHP fallback and AJAX.
 */
class Yasyes_Shortlink_Link_Service {

	/**
	 * Handle a non-AJAX create/update/delete submit.
	 * Redirects back on success (PRG) and never returns; returns the failure message otherwise.
	 */
	public static function handle_post_request( string $back_url ): string {
		$action          = sanitize_key( wp_unslash( $_POST['action_type'] ?? '' ) );
		$nonce           = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );
		$invalid_session = 'Your session expired. Refresh the page and try again.';
		$result          = null;
		$notice          = '';

		switch ( $action ) {
			case 'create':
				if ( ! wp_verify_nonce( $nonce, 'yasyes_shortlink_link_save' ) ) {
					return $invalid_session;
				}
				$result = self::create_from_request();
				$notice = 'saved';
				break;

			case 'update':
				if ( ! wp_verify_nonce( $nonce, 'yasyes_shortlink_link_save' ) ) {
					return $invalid_session;
				}
				$result = self::update_from_request();
				$notice = 'saved';
				break;

			case 'delete':
				if ( ! wp_verify_nonce( $nonce, 'yasyes_shortlink_link_delete' ) ) {
					return $invalid_session;
				}
				$result = Yasyes_Shortlink_Link_Model::delete( absint( $_POST['link_id'] ?? 0 ) )
					? null
					: new WP_Error( 'yasyes_shortlink_delete_failed', 'Failed to delete the link.' );
				$notice = 'deleted';
				break;

			default:
				wp_safe_redirect( $back_url, 302 );
				exit;
		}

		if ( is_wp_error( $result ) ) {
			return $result->get_error_message();
		}

		wp_safe_redirect( add_query_arg( 'ys_notice', $notice, $back_url ), 302 );
		exit;
	}

	/**
	 * Feedback for the app pages: PRG success codes from ?ys_notice plus the non-AJAX failure text.
	 * Rendered inline (works without JS) and turned into toasts by the front-end script.
	 *
	 * @return array<int, array{type: string, message: string}>
	 */
	public static function notices( string $error = '' ): array {
		$messages = array(
			'saved'   => 'Link saved.',
			'deleted' => 'Link deleted.',
		);
		$code     = sanitize_key( wp_unslash( $_GET['ys_notice'] ?? '' ) );
		$notices  = array();

		if ( isset( $messages[ $code ] ) ) {
			$notices[] = array(
				'type'    => 'success',
				'message' => $messages[ $code ],
			);
		}

		if ( '' !== $error ) {
			$notices[] = array(
				'type'    => 'error',
				'message' => $error,
			);
		}

		return $notices;
	}

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
