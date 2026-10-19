<?php
/**
 * Uninstall Yasyes Short Link.
 *
 * - Drops the plugin table
 * - Removes plugin options
 * - Removes capabilities from roles
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

require_once plugin_dir_path( __FILE__ ) . 'includes/class-yasyes-shortlink-link-model.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-yasyes-shortlink-settings.php';

// Drop table.
$table = $wpdb->prefix . 'yasyes_shortlink_links';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

// Delete options.
delete_option( Yasyes_Shortlink_Settings::OPTION_SITE_LABEL );
delete_option( Yasyes_Shortlink_Settings::OPTION_PUBLIC_ACCESS );

// Remove capability from administrator role.
$role = get_role( 'administrator' );
if ( $role ) {
	$role->remove_cap( 'manage_yasyes_shortlink_links' );
}

// Remove rate-limit transients (optional, but clean).
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		'%ys_rl_%'
	)
);
