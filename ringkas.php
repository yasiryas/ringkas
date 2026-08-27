<?php
/**
 * Plugin Name:       Ringkas
 * Description:       Buat short link di root domain (domain.com/kode) tanpa plugin pihak ketiga. Kelola tautan dari dashboard wp-admin atau halaman /short/dashboard dengan UI minimalis, pencarian instan, dan auto-expire.
 * Version:           1.4.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Yasyes Studio
 * Author URI:        https://yasyes.id
 * License:           GPL v2 or later
 * Text Domain:       ringkas
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RINGKAS_PLUGIN_FILE', __FILE__ );
define( 'RINGKAS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RINGKAS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RINGKAS_VERSION', '1.4.0' );

require_once RINGKAS_PLUGIN_DIR . 'includes/class-ringkas-activator.php';
require_once RINGKAS_PLUGIN_DIR . 'includes/class-ringkas-validator.php';
require_once RINGKAS_PLUGIN_DIR . 'includes/class-ringkas-link-model.php';
require_once RINGKAS_PLUGIN_DIR . 'includes/class-ringkas-link-service.php';
require_once RINGKAS_PLUGIN_DIR . 'includes/class-ringkas-settings.php';
require_once RINGKAS_PLUGIN_DIR . 'includes/class-ringkas-short-pages.php';
require_once RINGKAS_PLUGIN_DIR . 'includes/class-ringkas-ajax.php';
require_once RINGKAS_PLUGIN_DIR . 'includes/class-ringkas-admin-menu.php';
require_once RINGKAS_PLUGIN_DIR . 'includes/class-ringkas-router.php';

register_activation_hook( __FILE__, array( 'Ringkas_Activator', 'activate' ) );
add_action( 'parse_request', array( 'Ringkas_Router', 'dispatch' ) );
Ringkas_Ajax_Controller::register();
Ringkas_Settings::register();
Ringkas_Admin_Menu::register();
