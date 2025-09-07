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
		if ( ! current_user_can( 'manage_options' ) ) {
			return array(
				'success' => false,
				'message' => __( 'You do not have permission to perform this action.', 'comments-shield' )
			);
		}

		global $wpdb;
		
		// Delete all comments
		$comments_deleted = $wpdb->query( "TRUNCATE TABLE {$wpdb->comments}" );
		$meta_deleted = $wpdb->query( "TRUNCATE TABLE {$wpdb->commentmeta}" );
		
		if ( $comments_deleted !== false && $meta_deleted !== false ) {
			// Update comment count for all posts
			$wpdb->query( "UPDATE {$wpdb->posts} SET comment_count = 0" );
			
			wp_cache_flush();
			
			return array(
				'success' => true,
				'message' => __( 'All comments have been deleted successfully.', 'comments-shield' )
			);
		}
		
		return array(
			'success' => false,
			'message' => __( 'An error occurred while deleting comments.', 'comments-shield' )
		);
	}
}
