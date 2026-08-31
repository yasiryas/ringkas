<?php
/**
 * Plugin Name:       Yasyes Short Link
 * Description:       Create short links on your root domain (domain.com/code) without third-party plugins. Manage links from the wp-admin dashboard or /short/dashboard with a minimal UI, instant search, and auto-expire.
 * Version:           1.4.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Yasyes Studio
 * Author URI:        https://yasyes.id
 * License:           GPL v2 or later
 * Text Domain:       yasyes-shortlink
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YASYES_SHORTLINK_PLUGIN_FILE', __FILE__ );
define( 'YASYES_SHORTLINK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'YASYES_SHORTLINK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'YASYES_SHORTLINK_VERSION', '1.4.0' );

require_once YASYES_SHORTLINK_PLUGIN_DIR . 'includes/class-yasyes-shortlink-activator.php';
require_once YASYES_SHORTLINK_PLUGIN_DIR . 'includes/class-yasyes-shortlink-validator.php';
require_once YASYES_SHORTLINK_PLUGIN_DIR . 'includes/class-yasyes-shortlink-link-model.php';
require_once YASYES_SHORTLINK_PLUGIN_DIR . 'includes/class-yasyes-shortlink-link-service.php';
require_once YASYES_SHORTLINK_PLUGIN_DIR . 'includes/class-yasyes-shortlink-settings.php';
require_once YASYES_SHORTLINK_PLUGIN_DIR . 'includes/class-yasyes-shortlink-short-pages.php';
require_once YASYES_SHORTLINK_PLUGIN_DIR . 'includes/class-yasyes-shortlink-ajax.php';
require_once YASYES_SHORTLINK_PLUGIN_DIR . 'includes/class-yasyes-shortlink-admin-menu.php';
require_once YASYES_SHORTLINK_PLUGIN_DIR . 'includes/class-yasyes-shortlink-router.php';

register_activation_hook( __FILE__, array( 'Yasyes_Shortlink_Activator', 'activate' ) );
add_action( 'parse_request', array( 'Yasyes_Shortlink_Router', 'dispatch' ) );
Yasyes_Shortlink_Ajax_Controller::register();
Yasyes_Shortlink_Settings::register();
Yasyes_Shortlink_Admin_Menu::register();
