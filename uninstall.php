<?php
/**
 * Uninstall Comments Shield.
 *
 * @package CommentsShield
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

delete_option( 'cmsh_settings' );
delete_option( 'cmsh_delete_message' );
delete_site_option( 'cmsh_settings' );
delete_site_option( 'cmsh_delete_message' );

delete_transient( 'cmsh_activation_redirect' );
