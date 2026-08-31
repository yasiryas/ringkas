<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Intercept request di parse_request — sebelum WP_Query utama jalan.
 *
 * Kenapa parse_request, bukan rewrite rule 'top': rule root-level `^([A-Za-z0-9]{3,20})/?$`
 * will intercept a single-word slug belonging to an existing post/page (e.g. /about) and
 * preventing 404 errors. With parse_request we only stop if the short_code exists in the table;
 * selain itu WordPress lanjut normal (FR-5).
 */
class Yasyes_Shortlink_Router {

	public static function dispatch( WP $wp ): void {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		$path = trim( $wp->request ?? '', '/' );

		if ( '' === $path ) {
			return;
		}

		if ( self::starts_with_short( $path ) ) {
			show_admin_bar( false );
			self::route_short_page( $path );
			return;
		}

		// Path multi-segmen di luar /short/* pasti milik WordPress (permalink bertingkat).
		if ( false !== strpos( $path, '/' ) ) {
			return;
		}

		show_admin_bar( false );
		self::try_redirect_code( $path );
	}

	private static function starts_with_short( string $path ): bool {
		return 'short' === $path || 0 === strpos( $path, 'short/' );
	}

	private static function route_short_page( string $path ): void {
		$sub = trim( substr( $path, strlen( 'short' ) ), '/' );

		switch ( $sub ) {
			case '':
				Yasyes_Shortlink_Short_Pages::login();
				break;
			case 'forgot-password':
				Yasyes_Shortlink_Short_Pages::forgot_password();
				break;
			case 'reset-password':
				Yasyes_Shortlink_Short_Pages::reset_password();
				break;
			case 'logout':
				Yasyes_Shortlink_Short_Pages::logout();
				break;
			case 'dashboard':
				Yasyes_Shortlink_Short_Pages::dashboard();
				break;
			default:
				wp_safe_redirect( home_url( '/short' ), 302 );
				exit;
		}
	}

	private static function try_redirect_code( string $code ): void {
		if ( ! Yasyes_Shortlink_Validator::is_valid_format( $code ) || Yasyes_Shortlink_Validator::is_reserved( $code ) ) {
			return;
		}

		$link = Yasyes_Shortlink_Link_Model::find_by_code( $code );
		if ( ! $link ) {
			return; // Bukan short link → biarkan WordPress resolve normal.
		}

		if ( Yasyes_Shortlink_Link_Model::is_expired( $link ) ) {
			status_header( 410 );
			nocache_headers();
			include YASYES_SHORTLINK_PLUGIN_DIR . 'templates/link-expired.php';
			exit;
		}

		Yasyes_Shortlink_Link_Model::increment_click( (int) $link->id );
		wp_redirect( esc_url_raw( $link->original_url ), 302 );
		exit;
	}
}
