<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Endpoint AJAX untuk CRUD tautan (admin-ajax.php).
 * Semua aksi mewajibkan capability manage_ringkas_links + nonce 'ringkas_ajax'.
 */
class Ringkas_Ajax_Controller {

	public static function register(): void {
		add_action( 'wp_ajax_ringkas_list', array( __CLASS__, 'list_links' ) );
		add_action( 'wp_ajax_ringkas_save', array( __CLASS__, 'save_link' ) );
		add_action( 'wp_ajax_ringkas_delete', array( __CLASS__, 'delete_link' ) );
		add_action( 'wp_ajax_ringkas_feedback', array( __CLASS__, 'send_feedback' ) );
	}

	public static function list_links(): void {
		self::guard();

		$search = sanitize_text_field( wp_unslash( $_POST['s'] ?? '' ) );
		$page   = max( 1, absint( $_POST['paged'] ?? 1 ) );
		$result = Ringkas_Link_Model::paginate( $search, $page );

		wp_send_json_success(
			array(
				'items'    => array_map( array( __CLASS__, 'shape_item' ), $result['items'] ),
				'total'    => $result['total'],
				'pages'    => (int) ceil( $result['total'] / Ringkas_Link_Model::PER_PAGE ),
				'page'     => $page,
				'per_page' => Ringkas_Link_Model::PER_PAGE,
				'stats'    => Ringkas_Link_Model::stats(),
			)
		);
	}

	public static function save_link(): void {
		self::guard();

		$link_id = absint( $_POST['link_id'] ?? 0 );
		$result  = $link_id
			? Ringkas_Link_Service::update_from_request()
			: Ringkas_Link_Service::create_from_request();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$link = Ringkas_Link_Model::find( (int) $result );

		if ( ! $link ) {
			wp_send_json_error( array( 'message' => 'Tautan tidak ditemukan setelah disimpan.' ), 500 );
		}

		wp_send_json_success(
			array(
				'item'    => self::shape_item( $link ),
				'message' => $link_id ? 'Tautan berhasil diperbarui.' : 'Tautan berhasil dibuat.',
			)
		);
	}

	public static function delete_link(): void {
		self::guard();

		$link_id = absint( $_POST['link_id'] ?? 0 );

		if ( ! $link_id || ! Ringkas_Link_Model::find( $link_id ) ) {
			wp_send_json_error( array( 'message' => 'Tautan tidak ditemukan.' ), 404 );
		}

		if ( ! Ringkas_Link_Model::delete( $link_id ) ) {
			wp_send_json_error( array( 'message' => 'Gagal menghapus tautan.' ), 500 );
		}

		wp_send_json_success( array( 'message' => 'Tautan dihapus.' ) );
	}

	public static function send_feedback(): void {
		self::guard();

		$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

		if ( strlen( $message ) < 10 ) {
			wp_send_json_error( array( 'message' => 'Pesan minimal 10 karakter.' ), 400 );
		}

		if ( strlen( $message ) > 2000 ) {
			wp_send_json_error( array( 'message' => 'Pesan maksimal 2000 karakter.' ), 400 );
		}

		$user    = wp_get_current_user();
		$site    = get_bloginfo( 'name' );
		$to      = 'yasir123983@gmail.com';
		$subject = sprintf( '[Ringkas Feedback] %s — %s', $site, $user->display_name ?: $user->user_login );

		$body  = "Pengirim: {$user->display_name} <{$user->user_email}>\n";
		$body .= "Site: {$site} (" . home_url() . ")\n\n";
		$body .= "Pesan:\n{$message}\n";

		$headers = array( 'Content-Type: text/plain; charset=UTF-8', "Reply-To: {$user->user_email}" );

		$sent = wp_mail( $to, $subject, $body, $headers );

		if ( ! $sent ) {
			wp_send_json_error( array( 'message' => 'Gagal mengirim email. Silakan coba lagi.' ), 500 );
		}

		wp_send_json_success( array( 'message' => 'Terima kasih! Feedback Anda telah dikirim.' ) );
	}

	private static function shape_item( object $link ): array {
		return array(
			'id'           => (int) $link->id,
			'short_code'   => $link->short_code,
			'short_url'    => home_url( '/' . $link->short_code ),
			'original_url' => $link->original_url,
			'clicks'       => (int) $link->click_count,
			'expired'      => Ringkas_Link_Model::is_expired( $link ),
			'expiry_text'  => $link->expired_at ? date_i18n( 'd M Y H:i', strtotime( $link->expired_at ) ) : '',
			'expiry_raw'   => $link->expired_at ? str_replace( ' ', 'T', substr( $link->expired_at, 0, 16 ) ) : '',
			'updated_at'   => $link->updated_at,
		);
	}

	private static function guard(): void {
		if ( ! Ringkas_Settings::is_public() && ! current_user_can( 'manage_ringkas_links' ) ) {
			wp_send_json_error( array( 'message' => 'Anda tidak punya akses.' ), 403 );
		}

		check_ajax_referer( 'ringkas_ajax', 'nonce' );
	}
}
