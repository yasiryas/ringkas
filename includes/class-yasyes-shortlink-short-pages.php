<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Yasyes_Shortlink_Short_Pages {

	private const RATE_LOGIN_LIMIT   = 10;
	private const RATE_LOGIN_WINDOW  = 600;

	public static function login(): void {
		if ( Yasyes_Shortlink_Settings::is_public() ) {
			wp_safe_redirect( home_url( '/short/dashboard' ), 302 );
			exit;
		}

		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( home_url( '/short/dashboard' ), 302 );
			exit;
		}

		$error = '';
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			self::assert_not_throttled( 'login', self::RATE_LOGIN_LIMIT, self::RATE_LOGIN_WINDOW );
			self::throttle( 'login' );

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'yasyes_shortlink_login' ) ) {
				$error = 'Invalid session. Please try again.';
			} else {
				$user = wp_signon(
					array(
						'user_login'    => sanitize_text_field( wp_unslash( $_POST['username'] ?? '' ) ),
						'user_password' => $_POST['password'] ?? '',
						'remember'      => true,
					),
					false
				);

				if ( is_wp_error( $user ) || ! user_can( $user, 'manage_options' ) ) {
					$error = is_wp_error( $user )
						? 'Incorrect username or password.'
						: 'This account does not have access to Yasyes Short Link.';
					wp_clear_auth_cookie();
				} else {
					wp_safe_redirect( home_url( '/short/dashboard' ), 302 );
					exit;
				}
			}
		}

		$notice = '';
		if ( isset( $_GET['reset'] ) ) {
			$notice = 'Password has been changed. Please log in with your new password.';
		}

		self::enqueue_assets();
		include YASYES_SHORTLINK_PLUGIN_DIR . 'templates/short-login.php';
		exit;
	}

	public static function forgot_password(): void {
		$message = '';
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			self::assert_not_throttled( 'forgot', self::RATE_LOGIN_LIMIT, self::RATE_LOGIN_WINDOW );
			self::throttle( 'forgot' );

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'yasyes_shortlink_forgot' ) ) {
				$message = 'Invalid session. Please try again.';
			} else {
				$user = get_user_by( 'email', sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ) );

				if ( $user ) {
					$key = get_password_reset_key( $user );

					if ( ! is_wp_error( $key ) ) {
						$reset_url = home_url( '/short/reset-password?key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $user->user_login ) );

						wp_mail(
							$user->user_email,
							'Reset Password — Yasyes Short Link',
							"Click the link below to reset your password:\n\n$reset_url\n\n"
								. 'Ignore this email if you did not request a password reset.'
						);
					}
				}

				$message = 'If the email is registered, a reset link has been sent. Check your inbox and spam folder.';
			}
		}

		self::enqueue_assets();
		include YASYES_SHORTLINK_PLUGIN_DIR . 'templates/short-forgot-password.php';
		exit;
	}

	public static function reset_password(): void {
		$key   = sanitize_text_field( wp_unslash( $_GET['key'] ?? $_POST['key'] ?? '' ) );
		$login = sanitize_user( wp_unslash( $_GET['login'] ?? $_POST['login'] ?? '' ), true );

		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			self::enqueue_assets();
			include YASYES_SHORTLINK_PLUGIN_DIR . 'templates/short-reset-password-invalid.php';
			exit;
		}

		$error = '';
		if ( 'POST' === $_SERVER['REQUEST_METHOD']
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'yasyes_shortlink_reset' ) ) {
			$new_password = $_POST['password'] ?? '';

			if ( strlen( $new_password ) < 8 ) {
				$error = 'Password must be at least 8 characters.';
			} else {
				reset_password( $user, $new_password );
				wp_safe_redirect( home_url( '/short?reset=1' ), 302 );
				exit;
			}
		}

		self::enqueue_assets();
		include YASYES_SHORTLINK_PLUGIN_DIR . 'templates/short-reset-password.php';
		exit;
	}

	public static function logout(): void {
		wp_logout();
		wp_safe_redirect( home_url( '/short' ), 302 );
		exit;
	}

	public static function dashboard(): void {
		if ( ! self::can_access_dashboard() ) {
			wp_safe_redirect( home_url( '/short' ), 302 );
			exit;
		}

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			self::handle_dashboard_post();
		}

		$search = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
		$page   = max( 1, absint( $_GET['paged'] ?? 1 ) );

		$result      = Yasyes_Shortlink_Link_Model::paginate( $search, $page );
		$links       = $result['items'];
		$total       = $result['total'];
		$total_pages = (int) ceil( $total / Yasyes_Shortlink_Link_Model::PER_PAGE );
		$stats       = Yasyes_Shortlink_Link_Model::stats();

		self::enqueue_assets();
		include YASYES_SHORTLINK_PLUGIN_DIR . 'templates/short-dashboard.php';
		exit;
	}

	private static function can_access_dashboard(): bool {
		if ( Yasyes_Shortlink_Settings::is_public() ) {
			return true;
		}

		return is_user_logged_in() && current_user_can( 'manage_options' );
	}

	private static function handle_dashboard_post(): void {
		$action   = sanitize_key( wp_unslash( $_POST['action_type'] ?? '' ) );
		$back_url = home_url( '/short/dashboard' );

		switch ( $action ) {
			case 'create':
				if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'yasyes_shortlink_link_save' ) ) {
					Yasyes_Shortlink_Link_Service::create_from_request();
				}
				break;

			case 'update':
				if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'yasyes_shortlink_link_save' ) ) {
					Yasyes_Shortlink_Link_Service::update_from_request();
				}
				break;

			case 'delete':
				if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'yasyes_shortlink_link_delete' ) ) {
					Yasyes_Shortlink_Link_Model::delete( absint( $_POST['link_id'] ?? 0 ) );
				}
				break;
		}

		wp_safe_redirect( $back_url, 302 );
		exit;
	}

	private static function enqueue_assets(): void {
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'ys-app', YASYES_SHORTLINK_PLUGIN_URL . 'assets/yasyes-shortlink.css', array( 'dashicons' ), YASYES_SHORTLINK_VERSION );
		wp_enqueue_script( 'ys-app', YASYES_SHORTLINK_PLUGIN_URL . 'assets/yasyes-shortlink.js', array(), YASYES_SHORTLINK_VERSION, true );
		wp_add_inline_script(
			'ys-app',
			sprintf(
				'window.YasyesShortlinkConfig = { ajaxUrl: %s, nonce: %s, pollMs: 30000 };',
				wp_json_encode( admin_url( 'admin-ajax.php' ) ),
				wp_json_encode( wp_create_nonce( 'yasyes_shortlink_ajax' ) )
			),
			'before'
		);
	}

	private static function assert_not_throttled( string $bucket, int $limit, int $window ): void {
		$count = (int) get_transient( self::throttle_key( $bucket ) );
		if ( $count >= $limit ) {
			status_header( 429 );
			nocache_headers();
			wp_die(
				'Too many attempts. Please wait a few minutes and try again.',
				'Rate limit reached',
				array( 'response' => 429 )
			);
		}
	}

	private static function throttle( string $bucket ): void {
		$key   = self::throttle_key( $bucket );
		$count = (int) get_transient( $key );
		set_transient( $key, $count + 1, self::RATE_LOGIN_WINDOW );
	}

	private static function throttle_key( string $bucket ): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : 'unknown';

		return 'ys_rl_' . md5( $bucket . '|' . $ip );
	}
}
