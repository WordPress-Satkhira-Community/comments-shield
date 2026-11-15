<?php
/**
 * Plugin Name: Comments Shield
 * Plugin URI: https://wordpress.org/plugins/comments-shield/
 * Description: Protect your WordPress site from spam comments with simple, safe controls.
 * Version: 1.3.1
 * Requires at least: 6.1
 * Requires PHP: 8.0
 * Author: WordPress Satkhira Community
 * Author URI: https://wpsatkhira.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: comments-shield
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CMSH_VERSION' ) ) {
	define( 'CMSH_VERSION', '1.3.1' );
}
if ( ! defined( 'CMSH_FILE' ) ) {
	define( 'CMSH_FILE', __FILE__ );
}
if ( ! defined( 'CMSH_DIR' ) ) {
	define( 'CMSH_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'CMSH_URL' ) ) {
	define( 'CMSH_URL', plugin_dir_url( __FILE__ ) );
}

register_activation_hook( __FILE__, 'cmsh_activate' );
function cmsh_activate() {
	if ( false === get_option( 'cmsh_settings', false ) ) {
		add_option( 'cmsh_settings', cmsh_default_settings() );
	}

	set_transient( 'cmsh_activation_redirect', admin_url( 'admin.php?page=comments-shield' ), 60 );
}

add_action( 'admin_init', 'cmsh_maybe_redirect_after_activation' );
function cmsh_maybe_redirect_after_activation() {
	if ( ! is_admin() || wp_doing_ajax() || is_network_admin() ) {
		return;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}

	if ( isset( $_GET['activate-multi'] ) ) {
		return;
	}

	$redirect_url = get_transient( 'cmsh_activation_redirect' );
	if ( empty( $redirect_url ) ) {
		return;
	}

	delete_transient( 'cmsh_activation_redirect' );

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_safe_redirect( $redirect_url );
	exit;
}

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/class-cmsh-core.php';
require_once __DIR__ . '/includes/class-cmsh-admin.php';

function cmsh_init() {
	new CMSH_Core();
	if ( is_admin() ) {
		new CMSH_Admin();
	}
}

add_action( 'plugins_loaded', 'cmsh_init' );
