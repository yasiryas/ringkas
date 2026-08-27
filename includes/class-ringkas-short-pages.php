<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ringkas_Short_Pages {

	private const RATE_LOGIN_LIMIT   = 10;
	private const RATE_LOGIN_WINDOW  = 600;   // 10 menit
	private const RATE_REG_LIMIT     = 5;
	private const RATE_REG_WINDOW    = 3600;  // 1 jam

	public static function login(): void {
		if ( Ringkas_Settings::is_public() ) {
			wp_safe_redirect( home_url( '/short/dashboard' ), 302 );
			exit;
		}

		if ( is_user_logged_in() && current_user_can( 'manage_ringkas_links' ) ) {
			wp_safe_redirect( home_url( '/short/dashboard' ), 302 );
			exit;
		}

		$error = '';
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			self::assert_not_throttled( 'login', self::RATE_LOGIN_LIMIT, self::RATE_LOGIN_WINDOW );
			self::throttle( 'login' );

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'ringkas_login' ) ) {
				$error = 'Sesi tidak valid. Coba lagi.';
			} else {
				$user = wp_signon(
					array(
						'user_login'    => sanitize_text_field( wp_unslash( $_POST['username'] ?? '' ) ),
						'user_password' => $_POST['password'] ?? '',
						'remember'      => true,
					),
					false
				);

				if ( is_wp_error( $user ) || ! user_can( $user, 'manage_ringkas_links' ) ) {
					$error = is_wp_error( $user )
						? 'Username atau password salah.'
						: 'Akun ini belum memiliki akses Ringkas.';
					wp_clear_auth_cookie();
				} else {
					wp_safe_redirect( home_url( '/short/dashboard' ), 302 );
					exit;
				}
			}
		}

		$notice = '';
		if ( isset( $_GET['registered'] ) ) {
			$notice = 'Akun berhasil dibuat. Silakan masuk.';
		}
		if ( isset( $_GET['reset'] ) ) {
			$notice = 'Password sudah diubah. Silakan masuk dengan password baru.';
		}

		include RINGKAS_PLUGIN_DIR . 'templates/short-login.php';
		exit;
	}

	public static function register(): void {
		$error = '';
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			self::assert_not_throttled( 'register', self::RATE_REG_LIMIT, self::RATE_REG_WINDOW );
			self::throttle( 'register' );

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'ringkas_register' ) ) {
				$error = 'Sesi tidak valid. Coba lagi.';
			} else {
				$username = sanitize_user( wp_unslash( $_POST['username'] ?? '' ) );
				$email    = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
				$password = $_POST['password'] ?? '';

				if ( ! $username || username_exists( $username ) ) {
					$error = 'Username kosong atau sudah dipakai.';
				} elseif ( ! is_email( $email ) || email_exists( $email ) ) {
					$error = 'Email tidak valid atau sudah dipakai.';
				} elseif ( strlen( $password ) < 8 ) {
					$error = 'Password minimal 8 karakter.';
				} else {
					$user_id = wp_insert_user(
						array(
							// Role dasar minimal; akses Ringkas diberi lewat capability di bawah.
							'user_login' => $username,
							'user_email' => $email,
							'user_pass'  => $password,
							'role'       => 'subscriber',
						)
					);

					if ( is_wp_error( $user_id ) ) {
						$error = 'Gagal membuat akun. Coba lagi.';
					} else {
						$new_user = new WP_User( $user_id );
						$new_user->add_cap( 'manage_ringkas_links' );

						wp_safe_redirect( home_url( '/short?registered=1' ), 302 );
						exit;
					}
				}
			}
		}

		include RINGKAS_PLUGIN_DIR . 'templates/short-register.php';
		exit;
	}

	public static function forgot_password(): void {
		$message = '';
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			self::assert_not_throttled( 'forgot', self::RATE_LOGIN_LIMIT, self::RATE_LOGIN_WINDOW );
			self::throttle( 'forgot' );

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'ringkas_forgot' ) ) {
				$message = 'Sesi tidak valid. Coba lagi.';
			} else {
				$user = get_user_by( 'email', sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ) );

				if ( $user ) {
					$key       = get_password_reset_key( $user );

					if ( ! is_wp_error( $key ) ) {
						$reset_url = home_url( '/short/reset-password?key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $user->user_login ) );

						wp_mail(
							$user->user_email,
							'Reset Password — Ringkas',
							"Klik tautan berikut untuk mengatur ulang password Anda:\n\n$reset_url\n\n"
								. 'Abaikan email ini jika Anda tidak meminta reset password.'
						);
					}
				}

				// Pesan sama untuk email terdaftar maupun tidak — jangan bocorkan keberadaan akun.
				$message = 'Kalau email terdaftar, tautan reset sudah dikirim. Cek inbox dan folder spam.';
			}
		}

		include RINGKAS_PLUGIN_DIR . 'templates/short-forgot-password.php';
		exit;
	}

	public static function reset_password(): void {
		$key   = sanitize_text_field( wp_unslash( $_GET['key'] ?? $_POST['key'] ?? '' ) );
		$login = sanitize_user( wp_unslash( $_GET['login'] ?? $_POST['login'] ?? '' ), true );

		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			include RINGKAS_PLUGIN_DIR . 'templates/short-reset-password-invalid.php';
			exit;
		}

		$error = '';
		if ( 'POST' === $_SERVER['REQUEST_METHOD']
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'ringkas_reset' ) ) {
			$new_password = $_POST['password'] ?? '';

			if ( strlen( $new_password ) < 8 ) {
				$error = 'Password minimal 8 karakter.';
			} else {
				reset_password( $user, $new_password );
				wp_safe_redirect( home_url( '/short?reset=1' ), 302 );
				exit;
			}
		}

		include RINGKAS_PLUGIN_DIR . 'templates/short-reset-password.php';
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

		$result      = Ringkas_Link_Model::paginate( $search, $page );
		$links       = $result['items'];
		$total       = $result['total'];
		$total_pages = (int) ceil( $total / Ringkas_Link_Model::PER_PAGE );
		$stats       = Ringkas_Link_Model::stats();

		include RINGKAS_PLUGIN_DIR . 'templates/short-dashboard.php';
		exit;
	}

	/**
	 * Mode publik: terbuka untuk siapa pun. Selain itu wajib login + kapabilitas.
	 */
	private static function can_access_dashboard(): bool {
		if ( Ringkas_Settings::is_public() ) {
			return true;
		}

		return is_user_logged_in() && current_user_can( 'manage_ringkas_links' );
	}

	/**
	 * Fallback tanpa JS: proses POST lalu redirect (PRG).
	 */
	private static function handle_dashboard_post(): void {
		$action   = sanitize_key( wp_unslash( $_POST['action_type'] ?? '' ) );
		$back_url = home_url( '/short/dashboard' );

		switch ( $action ) {
			case 'create':
				if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'ringkas_link_save' ) ) {
					Ringkas_Link_Service::create_from_request();
				}
				break;

			case 'update':
				if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'ringkas_link_save' ) ) {
					Ringkas_Link_Service::update_from_request();
				}
				break;

			case 'delete':
				if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'ringkas_link_delete' ) ) {
					Ringkas_Link_Model::delete( absint( $_POST['link_id'] ?? 0 ) );
				}
				break;
		}

		wp_safe_redirect( $back_url, 302 );
		exit;
	}

	private static function assert_not_throttled( string $bucket, int $limit, int $window ): void {
		$count = (int) get_transient( self::throttle_key( $bucket ) );
		if ( $count >= $limit ) {
			status_header( 429 );
			nocache_headers();
			wp_die(
				'Terlalu banyak percobaan. Tunggu beberapa menit lalu coba lagi.',
				'Batas percobaan tercapai',
				array( 'response' => 429 )
			);
		}
	}

	private static function throttle( string $bucket ): void {
		$key   = self::throttle_key( $bucket );
		$count = (int) get_transient( $key );
		set_transient( $key, $count + 1, self::rate_window_for( $bucket ) );
	}

	private static function rate_window_for( string $bucket ): int {
		return 'register' === $bucket ? self::RATE_REG_WINDOW : self::RATE_LOGIN_WINDOW;
	}

	private static function throttle_key( string $bucket ): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : 'unknown';

		return 'ringkas_rl_' . md5( $bucket . '|' . $ip );
	}
}
