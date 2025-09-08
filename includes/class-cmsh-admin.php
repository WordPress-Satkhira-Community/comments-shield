<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Ensure WordPress admin functions are available
require_once ABSPATH . 'wp-admin/includes/plugin.php';

class CMSH_Admin {
	/** @var array List of setting fields => labels */
	private array $fields;

	public function __construct() {
		$this->fields = array(
			'cmsh_disable_comments_support' => __( 'Disable Comments Support', 'comments-shield' ),
			'cmsh_close_comments'           => __( 'Close Comments on Frontend', 'comments-shield' ),
			'cmsh_hide_existing_comments'   => __( 'Hide Existing Comments', 'comments-shield' ),
			'cmsh_remove_comments_menu'     => __( 'Remove Comments Menu', 'comments-shield' ),
			'cmsh_remove_dashboard_widget'  => __( 'Remove Dashboard Widget', 'comments-shield' ),
		);

		$this->add_admin_menu();
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_init', array( $this, 'handle_delete_comments' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( CMSH_FILE ), array( $this, 'add_action_links' ) );
	}

	public function add_admin_menu() {
		add_menu_page(
			__( 'Comments Shield', 'comments-shield' ),
			__( 'Comments Shield', 'comments-shield' ),
			'manage_options',
			'comments-shield',
			array( $this, 'render_disable_page' ),
			'dashicons-shield',
			26
		);

		add_submenu_page(
			'comments-shield',
			__( 'Disable Comments', 'comments-shield' ),
			__( 'Disable Comments', 'comments-shield' ),
			'manage_options',
			'comments-shield',
			array( $this, 'render_disable_page' )
		);

		add_submenu_page(
			'comments-shield',
			__( 'Delete Comments', 'comments-shield' ),
			__( 'Delete Comments', 'comments-shield' ),
			'manage_options',
			'comments-shield-delete',
			array( $this, 'render_delete_page' )
		);
	}

	public function enqueue_assets( $hook ): void {
		if ( strpos($hook, 'comments-shield') !== false ) {
			wp_enqueue_style( 'cmsh-admin', CMSH_URL . 'assets/css/admin.css', array(), CMSH_VERSION );
		}
	}

	public function render_disable_page(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Disable Comments', 'comments-shield' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'commentShield' );
				do_settings_sections( 'commentShield' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function render_delete_page(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Delete Comments', 'comments-shield' ); ?></h1>
			<div class="cmsh-delete-comments-section">
				<p class="description"><?php echo esc_html__( 'This action will permanently delete all comments from your WordPress site. This cannot be undone.', 'comments-shield' ); ?></p>
				<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete all comments? This action cannot be undone.', 'comments-shield' ) ); ?>');">
					<?php wp_nonce_field( 'cmsh_delete_comments' ); ?>
					<input type="submit" name="cmsh_delete_comments" class="button button-danger" value="<?php echo esc_attr__( 'Delete All Comments', 'comments-shield' ); ?>" />
				</form>
			</div>
		</div>
		<?php
	}

	public function render_settings_page(): void {
		$this->options_page();
	}

	public function settings_init(): void {
		register_setting(
			'commentShield',
			'cmsh_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'cmsh_sanitize_array',
			)
		);

		add_settings_section(
			'comments_shield_section',
			__( 'Manage Comments Settings', 'comments-shield' ),
			function () {
				echo esc_html__( 'Select options to manage comments on your site.', 'comments-shield' );
			},
			'commentShield'
		);

		foreach ( $this->fields as $id => $label ) {
			add_settings_field(
				$id,
				esc_html( $label ),
				function () use ( $id ) {
					$this->render_toggle( $id );
				},
				'commentShield',
				'comments_shield_section'
			);
		}
	}

	private function render_toggle( string $id ): void {
		$options = cmsh_get_settings();
		$checked = ! empty( $options[ $id ] );
		$input_id = 'cmsh_' . esc_attr( $id );
		?>
		<div class="cmsh-field">
			<label class="cmsh-switch">
				<input id="<?php echo esc_attr( $input_id ); ?>" type="checkbox" name="cmsh_settings[<?php echo esc_attr( $id ); ?>]" value="1" <?php checked( $checked ); ?> />
				<span class="cmsh-slider round" aria-hidden="true"></span>
			</label>
		</div>
		<?php
	}

	public function options_page(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Comments Shield', 'comments-shield' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'commentShield' );
				do_settings_sections( 'commentShield' );
				submit_button();
				?>
			</form>

			<div class="cmsh-delete-comments-section">
				<h2><?php echo esc_html__( 'Delete All Comments', 'comments-shield' ); ?></h2>
				<p class="description"><?php echo esc_html__( 'This action will permanently delete all comments from your WordPress site. This cannot be undone.', 'comments-shield' ); ?></p>
				<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete all comments? This action cannot be undone.', 'comments-shield' ) ); ?>');">
					<?php wp_nonce_field( 'cmsh_delete_comments' ); ?>
					<input type="submit" name="cmsh_delete_comments" class="button button-danger" value="<?php echo esc_attr__( 'Delete All Comments', 'comments-shield' ); ?>" />
				</form>
			</div>
		</div>
		<?php
	}

	public function add_action_links( array $actions ): array {
		$actions[] = '<a href="' . esc_url( admin_url( 'admin.php?page=comments-shield' ) ) . '">' . esc_html__( 'Settings', 'comments-shield' ) . '</a>';
		return $actions;
	}

	/**
	 * Handle the delete comments form submission
	 */
	public function handle_delete_comments(): void {
		if ( ! isset( $_POST['cmsh_delete_comments'] ) || ! isset( $_POST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'cmsh_delete_comments' ) ) {
			add_settings_error(
				'comments-shield',
				'cmsh_nonce_error',
				__( 'Security check failed.', 'comments-shield' ),
				'error'
			);
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error(
				'comments-shield',
				'cmsh_permission_error',
				__( 'You do not have permission to perform this action.', 'comments-shield' ),
				'error'
			);
			return;
		}

		$core = new CMSH_Core();
		$result = $core->delete_all_comments();

		if ( $result['success'] ) {
			add_settings_error(
				'comments-shield',
				'cmsh_delete_success',
				$result['message'],
				'success'
			);
		} else {
			add_settings_error(
				'comments-shield',
				'cmsh_delete_error',
				$result['message'],
				'error'
			);
		}
	}
}
