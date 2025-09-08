<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CMSH_Core {
	private array $options;

	public function __construct() {
		$this->options = cmsh_get_settings();

		if ( ! empty( $this->options['cmsh_disable_comments_support'] ) ) {
			add_action( 'admin_init', array( $this, 'disable_comments_support' ) );
		}

		if ( ! empty( $this->options['cmsh_close_comments'] ) ) {
			add_filter( 'comments_open', '__return_false', 20, 2 );
			add_filter( 'pings_open', '__return_false', 20, 2 );
		}

		if ( ! empty( $this->options['cmsh_hide_existing_comments'] ) ) {
			add_filter( 'comments_array', '__return_empty_array', 10, 2 );
		}

		if ( ! empty( $this->options['cmsh_remove_comments_menu'] ) ) {
			add_action( 'admin_menu', array( $this, 'remove_comments_menu' ) );
			add_action( 'admin_init', array( $this, 'redirect_comments_page' ) );
			add_action( 'init', array( $this, 'remove_admin_bar_comments' ) );
		}

		if ( ! empty( $this->options['cmsh_remove_dashboard_widget'] ) ) {
			add_action( 'admin_init', array( $this, 'remove_dashboard_widget' ) );
		}
	}

	public function disable_comments_support(): void {
		$post_types = get_post_types();
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}

	public function remove_comments_menu(): void {
		remove_menu_page( 'edit-comments.php' );
	}

	public function redirect_comments_page(): void {
		global $pagenow;
		if ( 'edit-comments.php' === $pagenow ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
	}

	public function remove_admin_bar_comments(): void {
		if ( is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	}

	public function remove_dashboard_widget(): void {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}

	/**
	 * Delete all comments from the database
	 *
	 * @return array Response with status and message
	 */
	public function delete_all_comments(): array {
		global $wpdb;

		// Get all comments except WooCommerce product reviews and notes
		$args = array(
			'type__not_in' => array('review', 'order_note', 'webhook_delivery'),
			'status'       => array('approve', 'spam', 'trash'),
			'fields'       => 'ids',
			'no_found_rows' => true,
		);
		
		$comments = get_comments($args);

		if ( empty( $comments ) ) {
			return array(
				'success' => false,
				'message' => __( 'No comments found to delete.', 'comments-shield' ),
			);
		}

		// Count comments before deletion
		$comments_count = count($comments);

		// Delete comments and their meta
		foreach ( $comments as $comment_id ) {
			wp_delete_comment( $comment_id, true );
		}

		// Clear comment cache
		wp_cache_delete( 'comments-0', 'counts' );

		return array(
			'success' => true,
			'message' => sprintf(
				__( '%d comments have been permanently deleted (excluding WooCommerce reviews and notes).', 'comments-shield' ),
				$comments_count
			),
		);
	}
}
