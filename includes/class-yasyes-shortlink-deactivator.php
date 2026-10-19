<?php
/**
 * Fired during plugin deactivation.
 *
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Yasyes_Shortlink_Deactivator {

	/**
	 * Deactivate plugin.
	 */
	public static function deactivate(): void {
		delete_transient( 'yasyes_shortlink_flush_rules' );
	}
}
