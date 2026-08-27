<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ringkas_Activator {

	public static function activate(): void {
		self::create_table();
		self::grant_admin_capability();
		self::seed_default_options();
	}

	private static function create_table(): void {
		global $wpdb;

		$table           = $wpdb->prefix . 'ringkas_links';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			original_url TEXT NOT NULL,
			short_code VARCHAR(20) NOT NULL,
			click_count BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			expired_at DATETIME NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY short_code (short_code),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	private static function seed_default_options(): void {
		if ( false === get_option( Ringkas_Settings::OPTION_SITE_LABEL, false ) ) {
			update_option(
				Ringkas_Settings::OPTION_SITE_LABEL,
				(string) wp_parse_url( home_url(), PHP_URL_HOST )
			);
		}
	}

	private static function grant_admin_capability(): void {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'manage_ringkas_links' );
		}
	}
}
