<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ringkas_Validator {

	public const CODE_PATTERN = '/^[A-Za-z0-9]{3,20}$/';

	// Path milik WordPress dan plugin ini — tidak boleh dipakai sebagai short_code.
	public const RESERVED = array(
		'short',
		'wp-admin',
		'wp-content',
		'wp-includes',
		'wp-json',
		'wp-login',
		'feed',
		'xmlrpc.php',
		'author',
		'search',
	);

	public static function is_valid_format( string $code ): bool {
		return (bool) preg_match( self::CODE_PATTERN, $code );
	}

	public static function is_reserved( string $code ): bool {
		return in_array( strtolower( $code ), self::RESERVED, true );
	}

	public static function exists_in_links( string $code ): bool {
		return (bool) Ringkas_Link_Model::find_by_code( $code );
	}

	public static function is_slug_taken_by_wp_content( string $code ): bool {
		global $wpdb;

		$post_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_status NOT IN ('trash', 'auto-draft') LIMIT 1",
				$code
			)
		);
		if ( $post_exists ) {
			return true;
		}

		$term_exists = $wpdb->get_var(
			$wpdb->prepare( "SELECT term_id FROM {$wpdb->terms} WHERE slug = %s LIMIT 1", $code )
		);

		return (bool) $term_exists;
	}

	/**
	 * Validasi lengkap untuk short_code custom.
	 * Mengembalikan string kode yang valid atau WP_Error.
	 *
	 * @return string|WP_Error
	 */
	public static function validate_custom_code( string $code ) {
		$code = sanitize_text_field( trim( $code ) );

		if ( '' === $code ) {
			return new WP_Error( 'ringkas_empty_code', 'Alias tidak boleh kosong.' );
		}
		if ( ! self::is_valid_format( $code ) ) {
			return new WP_Error( 'ringkas_invalid_format', 'Alias hanya boleh huruf/angka, 3-20 karakter.' );
		}
		if ( self::is_reserved( $code ) || self::is_slug_taken_by_wp_content( $code ) ) {
			return new WP_Error( 'ringkas_slug_conflict', 'Kode ini bentrok dengan path/slug yang sudah ada di situs.' );
		}
		if ( self::exists_in_links( $code ) ) {
			return new WP_Error( 'ringkas_duplicate', 'Kode ini sudah dipakai tautan lain.' );
		}

		return $code;
	}

	public static function generate_random_code(): string {
		for ( $attempt = 0; $attempt < 5; $attempt++ ) {
			$code = self::random_base62( 6 + $attempt % 2 );

			if ( self::is_reserved( $code )
				|| self::is_slug_taken_by_wp_content( $code )
				|| self::exists_in_links( $code ) ) {
				continue;
			}

			return $code;
		}

		throw new RuntimeException( 'Gagal membuat kode unik setelah 5 percobaan.' );
	}

	private static function random_base62( int $length ): string {
		$chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$max      = strlen( $chars ) - 1;
		$position = random_int( 0, $max );
		$code     = '';

		for ( $i = 0; $i < $length; $i++ ) {
			$code   .= $chars[ $position ];
			$position = random_int( 0, $max );
		}

		return $code;
	}

	/**
	 * Validasi URL tujuan: harus http/https, bukan open-redirect ke skema lain.
	 *
	 * @return string|WP_Error
	 */
	public static function validate_original_url( string $url ) {
		$url = esc_url_raw( trim( $url ), array( 'http', 'https' ) );

		if ( empty( $url ) || ! preg_match( '#^https?://[^\s]+$#i', $url ) ) {
			return new WP_Error( 'ringkas_invalid_url', 'URL tujuan tidak valid. Gunakan format http(s)://...' );
		}

		return $url;
	}
}
