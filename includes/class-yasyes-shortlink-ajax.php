<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX endpoint for link CRUD (admin-ajax.php).
 * All actions require manage_options capability + nonce 'yasyes_shortlink_ajax'.
 */
class Yasyes_Shortlink_Ajax_Controller {

	public static function register(): void {
		add_action( 'wp_ajax_yasyes_shortlink_list', array( __CLASS__, 'list_links' ) );
		add_action( 'wp_ajax_yasyes_shortlink_save', array( __CLASS__, 'save_link' ) );
		add_action( 'wp_ajax_yasyes_shortlink_delete', array( __CLASS__, 'delete_link' ) );
		add_action( 'wp_ajax_yasyes_shortlink_feedback', array( __CLASS__, 'send_feedback' ) );
	}

	public static function list_links(): void {
		self::guard();

		$search = sanitize_text_field( wp_unslash( $_POST['s'] ?? '' ) );
		$page   = max( 1, absint( $_POST['paged'] ?? 1 ) );
		$result = Yasyes_Shortlink_Link_Model::paginate( $search, $page );

		wp_send_json_success(
			array(
				'items'    => array_map( array( __CLASS__, 'shape_item' ), $result['items'] ),
				'total'    => $result['total'],
				'pages'    => (int) ceil( $result['total'] / Yasyes_Shortlink_Link_Model::PER_PAGE ),
				'page'     => $page,
				'per_page' => Yasyes_Shortlink_Link_Model::PER_PAGE,
				'stats'    => Yasyes_Shortlink_Link_Model::stats(),
			)
		);
	}

	public static function save_link(): void {
		self::guard();

		$link_id = absint( $_POST['link_id'] ?? 0 );
		$result  = $link_id
			? Yasyes_Shortlink_Link_Service::update_from_request()
			: Yasyes_Shortlink_Link_Service::create_from_request();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$link = Yasyes_Shortlink_Link_Model::find( (int) $result );

		if ( ! $link ) {
			wp_send_json_error( array( 'message' => 'Link not found after saving.' ), 500 );
		}

		wp_send_json_success(
			array(
				'item'    => self::shape_item( $link ),
				'message' => $link_id ? 'Link updated successfully.' : 'Link created successfully.',
			)
		);
	}

	public static function delete_link(): void {
		self::guard();

		$link_id = absint( $_POST['link_id'] ?? 0 );

		if ( ! $link_id || ! Yasyes_Shortlink_Link_Model::find( $link_id ) ) {
			wp_send_json_error( array( 'message' => 'Link not found.' ), 404 );
		}

		if ( ! Yasyes_Shortlink_Link_Model::delete( $link_id ) ) {
			wp_send_json_error( array( 'message' => 'Failed to delete the link.' ), 500 );
		}

		wp_send_json_success( array( 'message' => 'Link deleted.' ) );
	}

	public static function send_feedback(): void {
		self::guard();

		$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

		if ( strlen( $message ) < 10 ) {
			wp_send_json_error( array( 'message' => 'Message must be at least 10 characters.' ), 400 );
		}

		if ( strlen( $message ) > 2000 ) {
			wp_send_json_error( array( 'message' => 'Message must be at most 2000 characters.' ), 400 );
		}

		$user    = wp_get_current_user();
		$site    = get_bloginfo( 'name' );
		$to      = 'yasir123983@gmail.com';
		$subject = sprintf( '[Yasyes Short Link Feedback] %s — %s', $site, $user->display_name ?: $user->user_login );

		$body  = "Sender: {$user->display_name} <{$user->user_email}>\n";
		$body .= "Site: {$site} (" . home_url() . ")\n\n";
		$body .= "Message:\n{$message}\n";

		$headers = array( 'Content-Type: text/plain; charset=UTF-8', "Reply-To: {$user->user_email}" );

		$sent = wp_mail( $to, $subject, $body, $headers );

		if ( ! $sent ) {
			wp_send_json_error( array( 'message' => 'Failed to send email. Please try again.' ), 500 );
		}

		wp_send_json_success( array( 'message' => 'Thank you! Your feedback has been sent.' ) );
	}

	private static function shape_item( object $link ): array {
		return array(
			'id'           => (int) $link->id,
			'short_code'   => $link->short_code,
			'short_url'    => home_url( '/' . $link->short_code ),
			'original_url' => $link->original_url,
			'clicks'       => (int) $link->click_count,
			'expired'      => Yasyes_Shortlink_Link_Model::is_expired( $link ),
			'expiry_text'  => $link->expired_at ? date_i18n( 'd M Y H:i', strtotime( $link->expired_at ) ) : '',
			'expiry_raw'   => $link->expired_at ? str_replace( ' ', 'T', substr( $link->expired_at, 0, 16 ) ) : '',
			'updated_at'   => $link->updated_at,
		);
	}

	private static function guard(): void {
		if ( ! Yasyes_Shortlink_Settings::is_public() && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have access.' ), 403 );
		}

		check_ajax_referer( 'yasyes_shortlink_ajax', 'nonce' );
	}
}
