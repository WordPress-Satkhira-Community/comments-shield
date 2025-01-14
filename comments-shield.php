<?php
/**
 * Plugin Name: Comments Shield
 * Plugin URI: https://wordpress.org/plugins/comments-shield/
 * Description: A plugin to protect your WordPress site from spam comments.
 * Version: 1.0.1
 * Requires at least: 6.1
 * Requires PHP:  7.4
 * Author: WordPress Satkhira Community
 * Author URI: https://wpsatkhira.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: comments-shield
 */


// Avoiding Direct File Access

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define('cmsh_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Register activation hook to initialize options
function cmsh_activate() {
	add_option('cmsh_settings', [
		'cmsh_disable_comments_support' => 1,
		'cmsh_close_comments' => 1,
		'cmsh_hide_existing_comments' => 1,
		'cmsh_remove_comments_menu' => 1,
		'cmsh_remove_dashboard_widget' => 1,
	]);
}
register_activation_hook(__FILE__, 'cmsh_activate');

// Add settings page to the admin menu
function cmsh_add_admin_menu() {
	add_options_page('Comments Shield Settings', 'Comments Shield', 'manage_options', 'comments-shield', 'cmsh_options_page');
}
add_action('admin_menu', 'cmsh_add_admin_menu');

// Initialize settings
function cmsh_settings_init() {
	register_setting('commentShield', 'cmsh_settings', [
		'type' => 'array', 
		'sanitize_callback' => 'cmsh_sanitize_array'
	]);

	add_settings_section(
		'comments_shield_commentShield_section',
		__('Manage Comments Settings', 'comments-shield'),
		'cmsh_settings_section_callback',
		'commentShield'
	);

	add_settings_field(
		'cmsh_disable_comments_support',
		__('Disable Comments Support', 'comments-shield'),
		'cmsh_cmsh_disable_comments_support_render',
		'commentShield',
		'comments_shield_commentShield_section'
	);

	add_settings_field(
		'cmsh_close_comments',
		__('Close Comments on Frontend', 'comments-shield'),
		'cmsh_cmsh_close_comments_render',
		'commentShield',
		'comments_shield_commentShield_section'
	);

	add_settings_field(
		'cmsh_hide_existing_comments',
		__('Hide Existing Comments', 'comments-shield'),
		'cmsh_cmsh_hide_existing_comments_render',
		'commentShield',
		'comments_shield_commentShield_section'
	);

	add_settings_field(
		'cmsh_remove_comments_menu',
		__('Remove Comments Menu', 'comments-shield'),
		'cmsh_cmsh_remove_comments_menu_render',
		'commentShield',
		'comments_shield_commentShield_section'
	);

	add_settings_field(
		'cmsh_remove_dashboard_widget',
		__('Remove Dashboard Widget', 'comments-shield'),
		'cmsh_cmsh_remove_dashboard_widget_render',
		'commentShield',
		'comments_shield_commentShield_section'
	);
}
add_action('admin_init', 'cmsh_settings_init');

// Sanitize the array before saving
function cmsh_sanitize_array( $input ) {
	return is_array($input) ? array_map('sanitize_text_field', $input) : [];
}

// Render functions for each setting field
function cmsh_cmsh_disable_comments_support_render() {
	$options = get_option('cmsh_settings');
?>
<input type='checkbox' name='cmsh_settings[cmsh_disable_comments_support]' <?php checked(isset($options['cmsh_disable_comments_support']), 1); ?> value='1'>
<?php
}

function cmsh_cmsh_close_comments_render() {
	$options = get_option('cmsh_settings');
?>
<input type='checkbox' name='cmsh_settings[cmsh_close_comments]' <?php checked(isset($options['cmsh_close_comments']), 1); ?> value='1'>
<?php
}

function cmsh_cmsh_hide_existing_comments_render() {
	$options = get_option('cmsh_settings');
?>
<input type='checkbox' name='cmsh_settings[cmsh_hide_existing_comments]' <?php checked(isset($options['cmsh_hide_existing_comments']), 1); ?> value='1'>
<?php
}

function cmsh_cmsh_remove_comments_menu_render() {
	$options = get_option('cmsh_settings');
?>
<input type='checkbox' name='cmsh_settings[cmsh_remove_comments_menu]' <?php checked(isset($options['cmsh_remove_comments_menu']), 1); ?> value='1'>
<?php
}

function cmsh_cmsh_remove_dashboard_widget_render() {
	$options = get_option('cmsh_settings');
?>
<input type='checkbox' name='cmsh_settings[cmsh_remove_dashboard_widget]' <?php checked(isset($options['cmsh_remove_dashboard_widget']), 1); ?> value='1'>
<?php
}

// Section callback function
function cmsh_settings_section_callback() {
	echo esc_html__('Select options to manage comments on your site.', 'comments-shield');
}

// Options page HTML
function cmsh_options_page() {
?>
<form action='options.php' method='post'>
	<h2><?php echo esc_html__('Comments Shield', 'comments-shield'); ?></h2>
	<?php
		settings_fields('commentShield');
		do_settings_sections('commentShield');
		submit_button();
	?>
</form>
<?php
}

// Implement logic based on settings
$options = get_option('cmsh_settings');

if (!empty($options['cmsh_disable_comments_support'])) {
	function cmsh_disable_comments_post_types_support() {
		$post_types = get_post_types();
		foreach ($post_types as $post_type) {
			if (post_type_supports($post_type, 'comments')) {
				remove_post_type_support($post_type, 'comments');
				remove_post_type_support($post_type, 'trackbacks');
			}
		}
	}
	add_action('admin_init', 'cmsh_disable_comments_post_types_support');
}

if (!empty($options['cmsh_close_comments'])) {
	function cmsh_disable_comments_status() {
		return false;
	}
	add_filter('comments_open', 'cmsh_disable_comments_status', 20, 2);
	add_filter('pings_open', 'cmsh_disable_comments_status', 20, 2);
}

if (!empty($options['cmsh_hide_existing_comments'])) {
	function cmsh_disable_comments_cmsh_hide_existing_comments($comments) {
		return array();
	}
	add_filter('comments_array', 'cmsh_disable_comments_cmsh_hide_existing_comments', 10, 2);
}

if (!empty($options['cmsh_remove_comments_menu'])) {
	function cmsh_disable_comments_admin_menu() {
		remove_menu_page('edit-comments.php');
	}
	add_action('admin_menu', 'cmsh_disable_comments_admin_menu');

	function cmsh_disable_comments_admin_menu_redirect() {
		global $pagenow;
		if ($pagenow === 'edit-comments.php') {
			wp_redirect(admin_url());
			exit;
		}
	}
	add_action('admin_init', 'cmsh_disable_comments_admin_menu_redirect');

	function cmsh_disable_comments_admin_bar() {
		if (is_admin_bar_showing()) {
			remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
		}
	}
	add_action('init', 'cmsh_disable_comments_admin_bar');
}

if (!empty($options['cmsh_remove_dashboard_widget'])) {
	function cmsh_disable_comments_dashboard() {
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	}
	add_action('admin_init', 'cmsh_disable_comments_dashboard');
}


// Comments Shield Option Links

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'cmsh_add_action_links' );

function cmsh_add_action_links ( $actions ) {
	$mylinks = array(
		'<a href="' . esc_url(admin_url( 'options-general.php?page=comments-shield' )) . '">' . esc_html__('Settings', 'comments-shield') . '</a>',
	);
	$actions = array_merge( $actions, $mylinks );
	return $actions;
}

// Redirect to settings page once the plugin is activated

function cmsh_activation_redirect( $plugin ) {
	if( $plugin == plugin_basename( __FILE__ ) ) {
		wp_safe_redirect( admin_url( 'options-general.php?page=comments-shield' ) );
		exit;
	}
}
add_action( 'activated_plugin', 'cmsh_activation_redirect' );