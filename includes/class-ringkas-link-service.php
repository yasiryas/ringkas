<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logika simpan/ubah tautan dari $_POST — dipakai fallback PHP maupun AJAX.
 */
class Ringkas_Link_Service {

	/**
	 * @return int|WP_Error ID tautan baru.
	 */
	public static function create_from_request() {
		$url = Ringkas_Validator::validate_original_url( wp_unslash( $_POST['original_url'] ?? '' ) );
		if ( is_wp_error( $url ) ) {
			return $url;
		}

		$custom_alias = trim( wp_unslash( $_POST['alias'] ?? '' ) );

		try {
			$code = '' !== $custom_alias
				? Ringkas_Validator::validate_custom_code( $custom_alias )
				: Ringkas_Validator::generate_random_code();
		} catch ( RuntimeException $e ) {
			return new WP_Error( 'ringkas_generate_failed', 'Gagal membuat kode unik. Coba lagi.' );
		}

		if ( is_wp_error( $code ) ) {
			return $code;
		}

		return Ringkas_Link_Model::create(
			array(
				'original_url' => $url,
				'short_code'   => $code,
				'expired_at'   => sanitize_text_field( wp_unslash( $_POST['expired_at'] ?? '' ) ) ?: null,
			)
		);
	}

	/**
	 * @return int|WP_Error ID tautan yang diubah.
	 */
	public static function update_from_request() {
		$link_id = absint( $_POST['link_id'] ?? 0 );
		$link    = Ringkas_Link_Model::find( $link_id );

		if ( ! $link ) {
			return new WP_Error( 'ringkas_not_found', 'Tautan tidak ditemukan.' );
		}

		$url = Ringkas_Validator::validate_original_url( wp_unslash( $_POST['original_url'] ?? '' ) );
		if ( is_wp_error( $url ) ) {
			return $url;
		}

		$data = array(
			'original_url' => $url,
			'expired_at'   => sanitize_text_field( wp_unslash( $_POST['expired_at'] ?? '' ) ) ?: null,
		);

		$new_alias = sanitize_text_field( wp_unslash( $_POST['alias'] ?? '' ) );

		if ( '' === $new_alias ) {
			return new WP_Error( 'ringkas_empty_code', 'Alias tidak boleh kosong.' );
		}

		if ( $new_alias !== $link->short_code ) {
			$validated = Ringkas_Validator::validate_custom_code( $new_alias );
			if ( is_wp_error( $validated ) ) {
				return $validated;
			}
			$data['short_code'] = $validated;
		}

		if ( ! Ringkas_Link_Model::update( $link_id, $data ) ) {
			return new WP_Error( 'ringkas_update_failed', 'Gagal memperbarui tautan.' );
		}

		return $link_id;
	}
}
