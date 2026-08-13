<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Return default settings array.
 */
function cmsh_default_settings(): array {
	return array(
		'cmsh_disable_comments_support' => 1,
		'cmsh_close_comments'           => 1,
		'cmsh_hide_existing_comments'   => 1,
		'cmsh_remove_comments_menu'     => 0,
		'cmsh_remove_dashboard_widget'  => 1,
		'cmsh_move_shield_to_settings'  => 0,
	);
}

/**
 * Sanitize the settings array: only allow known keys, cast to 0/1.
 *
 * @param mixed $input Raw input from settings form.
 * @return array       Clean settings.
 */
function cmsh_sanitize_array( $input ): array {
	$defaults   = cmsh_default_settings();
	$sanitized  = array();
	$input      = is_array( $input ) ? $input : array();
	foreach ( $defaults as $key => $default_val ) {
		$val               = isset( $input[ $key ] ) ? $input[ $key ] : 0;
		$sanitized[ $key ] = $val ? 1 : 0; // strictly 0/1.
	}
	return $sanitized;
}

/**
 * Helper to fetch merged settings (db over defaults).
 */
function cmsh_get_settings(): array {
	$defaults = cmsh_default_settings();
	$stored   = get_option( 'cmsh_settings', array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}
	$merged = wp_parse_args( $stored, $defaults );
	// Ensure 0/1.
	foreach ( $merged as $k => $v ) {
		$merged[ $k ] = $v ? 1 : 0;
	}
	return $merged;
}

/**
 * Whether the plugin pages are relocated under Settings / Tools.
 */
function cmsh_is_moved_to_settings(): bool {
	$options = cmsh_get_settings();
	return ! empty( $options['cmsh_move_shield_to_settings'] );
}

/**
 * Build the admin URL for a plugin page, honouring the relocation setting.
 *
 * @param string $page Page slug: 'comments-shield' or 'comments-shield-delete'.
 * @return string      Absolute admin URL.
 */
function cmsh_admin_page_url( string $page = 'comments-shield' ): string {
	if ( cmsh_is_moved_to_settings() ) {
		$parent = ( 'comments-shield-delete' === $page ) ? 'tools.php' : 'options-general.php';
	} else {
		$parent = 'admin.php';
	}
	return admin_url( $parent . '?page=' . $page );
}
