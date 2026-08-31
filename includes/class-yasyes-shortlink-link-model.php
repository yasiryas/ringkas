<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Yasyes_Shortlink_Link_Model {

	public static function table(): string {
		global $wpdb;

		return $wpdb->prefix . 'yasyes_shortlink_links';
	}

	/**
	 * @return object|null
	 */
	public static function find_by_code( string $code ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table() . ' WHERE short_code = %s LIMIT 1',
				$code
			)
		);
	}

	public const PER_PAGE = 20;

	/**
	 * List links with search and pagination.
	 *
	 * @return array{items: object[], total: int}
	 */
	public static function paginate( string $search = '', int $page = 1 ): array {
		global $wpdb;

		$where  = '';
		$params = array();

		if ( '' !== $search ) {
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$where   = 'WHERE short_code LIKE %s OR original_url LIKE %s';
			$params  = array( $like, $like );
		}

		$count_sql = 'SELECT COUNT(*) FROM ' . self::table() . ' ' . $where;
		$total     = $params ? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) ) : (int) $wpdb->get_var( $count_sql );

		if ( 0 === $total ) {
			return array(
				'items' => array(),
				'total' => 0,
			);
		}

		$offset  = ( max( 1, $page ) - 1 ) * self::PER_PAGE;
		$row_sql = 'SELECT * FROM ' . self::table() . ' ' . $where . ' ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d';
		$rows    = array_merge( $params, array( self::PER_PAGE, $offset ) );

		return array(
			'items' => (array) ( $params ? $wpdb->get_results( $wpdb->prepare( $row_sql, ...$rows ) ) : $wpdb->get_results( $wpdb->prepare( $row_sql, self::PER_PAGE, $offset ) ) ),
			'total' => $total,
		);
	}

	/**
	 * Aggregate statistics via SQL — no need to load all rows.
	 * Clock uses PHP time to stay consistent with is_expired().
	 *
	 * @return array{total_links:int, total_clicks:int, active:int}
	 */
	public static function stats(): array {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT
					COUNT(*) AS total_links,
					COALESCE(SUM(click_count), 0) AS total_clicks,
					SUM(CASE WHEN expired_at IS NULL OR expired_at > %s THEN 1 ELSE 0 END) AS active
				FROM ' . self::table(),
				date( 'Y-m-d H:i:s' )
			)
		);

		return array(
			'total_links'  => (int) ( $row->total_links ?? 0 ),
			'total_clicks' => (int) ( $row->total_clicks ?? 0 ),
			'active'       => (int) ( $row->active ?? 0 ),
		);
	}

	public static function find( int $id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id )
		);
	}

	/**
	 * @param array{original_url:string, short_code:string, expired_at:?string} $data
	 * @return int|WP_Error New link ID.
	 */
	public static function create( array $data ) {
		global $wpdb;

		$expired_at = null;
		if ( $data['expired_at'] ) {
			$expired_at = self::normalize_datetime( $data['expired_at'] );
			if ( null === $expired_at ) {
				return new WP_Error( 'yasyes_shortlink_invalid_date', 'Invalid expiry date format.' );
			}
		}

		// expired_at is only set when provided — empty means NULL (never expires).
		$values = array(
			'original_url' => $data['original_url'],
			'short_code'   => $data['short_code'],
			'click_count'  => 0,
			'created_at'   => current_time( 'mysql' ),
			'updated_at'   => current_time( 'mysql' ),
		);
		$format = array( '%s', '%s', '%d', '%s', '%s' );

		if ( $expired_at ) {
			$values['expired_at'] = $expired_at;
			$format[]             = '%s';
		}

		$inserted = $wpdb->insert( self::table(), $values, $format );

		if ( ! $inserted ) {
			return new WP_Error( 'yasyes_shortlink_insert_failed', 'Failed to save the link.' );
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * @param array{original_url?:string, short_code?:string, expired_at:?string} $data
	 *   expired_at null clears the expiry date; key absent means no change.
	 */
	public static function update( int $id, array $data ): bool {
		global $wpdb;

		$set    = array( 'updated_at = %s' );
		$params = array( current_time( 'mysql' ) );

		if ( isset( $data['original_url'] ) ) {
			$set[]    = 'original_url = %s';
			$params[] = $data['original_url'];
		}
		if ( isset( $data['short_code'] ) ) {
			$set[]    = 'short_code = %s';
			$params[] = $data['short_code'];
		}
		if ( array_key_exists( 'expired_at', $data ) ) {
			if ( empty( $data['expired_at'] ) ) {
				$set[] = 'expired_at = NULL';
			} else {
				$expired_at = self::normalize_datetime( $data['expired_at'] );
				if ( ! $expired_at ) {
					return false;
				}
				$set[]    = 'expired_at = %s';
				$params[] = $expired_at;
			}
		}

		$params[] = $id;
		array_unshift( $params, 'UPDATE ' . self::table() . ' SET ' . implode( ', ', $set ) . ' WHERE id = %d' );

		return (bool) $wpdb->query( $wpdb->prepare( ...$params ) );
	}

	public static function delete( int $id ): bool {
		global $wpdb;

		return (bool) $wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
	}

	// Atomic increment at SQL level — race-condition safe.
	public static function increment_click( int $id ): void {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				'UPDATE ' . self::table() . ' SET click_count = click_count + 1 WHERE id = %d',
				$id
			)
		);
	}

	public static function is_expired( object $link ): bool {
		return ! empty( $link->expired_at ) && strtotime( $link->expired_at ) < time();
	}

	private static function normalize_datetime( string $value ): ?string {
		$timestamp = strtotime( $value );

		return false === $timestamp ? null : date( 'Y-m-d H:i:s', $timestamp );
	}
}
