<?php
/**
 * Plugin Name: Comments Shield
 * Plugin URI: https://wordpress.org/plugins/comments-shield/
 * Description: Protect your WordPress site from spam comments with simple, safe controls.
 * Version: 1.2.1
 * Requires at least: 6.1
 * Requires PHP: 8.0
 * Author: WordPress Satkhira Community
 * Author URI: https://wpsatkhira.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: comments-shield
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load required WordPress files
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-includes/pluggable.php';
require_once ABSPATH . 'wp-includes/functions.php';
require_once ABSPATH . 'wp-includes/plugin.php';

// Constants.
if ( ! defined( 'CMSH_VERSION' ) ) {
	define( 'CMSH_VERSION', '1.2.1' );
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

// Activation: seed defaults if option doesn't exist.
register_activation_hook( __FILE__, 'cmsh_activate' );
function cmsh_activate() {
	if ( false === get_option( 'cmsh_settings', false ) ) {
		add_option( 'cmsh_settings', cmsh_default_settings() );
	}
}

// Redirect to settings after activation (single site).
add_action( 'activated_plugin', function ( $plugin ) {
	if ( $plugin === plugin_basename( CMSH_FILE ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=comments-shield' ) );
		exit;
	}
} );

// Load required files
require_once dirname(__FILE__) . '/includes/helpers.php';
require_once dirname(__FILE__) . '/includes/class-cmsh-core.php';
require_once dirname(__FILE__) . '/includes/class-cmsh-admin.php';

// Initialize plugin components
function cmsh_init() {
    new CMSH_Core();
    if (is_admin()) {
        new CMSH_Admin();
    }
}

add_action('plugins_loaded', 'cmsh_init');
