<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

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

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( CMSH_FILE ), array( $this, 'add_action_links' ) );
	}

	public function enqueue_assets( $hook ): void {
		if ( 'settings_page_comments-shield' === $hook ) {
			wp_enqueue_style( 'cmsh-admin', CMSH_URL . 'assets/css/admin.css', array(), CMSH_VERSION );
		}
	}

	public function add_admin_menu(): void {
		add_options_page(
			__( 'Comments Shield Settings', 'comments-shield' ),
			__( 'Comments Shield', 'comments-shield' ),
			'manage_options',
			'comments-shield',
			array( $this, 'options_page' )
		);
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
		</div>
		<?php
	}

	public function add_action_links( array $actions ): array {
		$actions[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=comments-shield' ) ) . '">' . esc_html__( 'Settings', 'comments-shield' ) . '</a>';
		return $actions;
	}
}
