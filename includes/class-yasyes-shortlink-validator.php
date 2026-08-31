<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Yasyes_Shortlink_Validator {

	public const CODE_PATTERN = '/^[A-Za-z0-9]{3,20}$/';

	// WordPress and plugin paths — cannot be used as short_code.
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
		return (bool) Yasyes_Shortlink_Link_Model::find_by_code( $code );
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
	 * Full validation for a custom short_code.
	 * Returns the valid code string or WP_Error.
	 *
	 * @return string|WP_Error
	 */
	public static function validate_custom_code( string $code ) {
		$code = sanitize_text_field( trim( $code ) );

		if ( '' === $code ) {
			return new WP_Error( 'yasyes_shortlink_empty_code', 'Alias cannot be empty.' );
		}
		if ( ! self::is_valid_format( $code ) ) {
			return new WP_Error( 'yasyes_shortlink_invalid_format', 'Alias may only contain letters/numbers, 3-20 characters.' );
		}
		if ( self::is_reserved( $code ) || self::is_slug_taken_by_wp_content( $code ) ) {
			return new WP_Error( 'yasyes_shortlink_slug_conflict', 'This code conflicts with an existing page/slug on the site.' );
		}
		if ( self::exists_in_links( $code ) ) {
			return new WP_Error( 'yasyes_shortlink_duplicate', 'This code is already used by another link.' );
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

		throw new RuntimeException( 'Failed to generate a unique code after 5 attempts.' );
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
	 * Validate destination URL: must be http/https, no open-redirect to other schemes.
	 *
	 * @return string|WP_Error
	 */
	public static function validate_original_url( string $url ) {
		$url = esc_url_raw( trim( $url ), array( 'http', 'https' ) );

		if ( empty( $url ) || ! preg_match( '#^https?://[^\s]+$#i', $url ) ) {
			return new WP_Error( 'yasyes_shortlink_invalid_url', 'Destination URL is invalid. Use the http(s):// format.' );
		}

		return $url;
	}
}
