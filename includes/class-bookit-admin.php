<?php
/**
 * Admin settings page for BookIt for Cal.com.
 *
 * @package BookIt_For_CalCom
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BookIt_Admin
 *
 * Registers the settings page using the WordPress Settings API.
 * Handles the AJAX "Refresh event types" button.
 *
 * @since 1.0.0
 */
class BookIt_Admin {

	/**
	 * Option key where all settings are stored.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'bookit_settings';

	/**
	 * Register all hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_bookit_refresh_event_types', array( __CLASS__, 'ajax_refresh_event_types' ) );
	}

	/**
	 * Add the settings page to the WordPress admin menu.
	 *
	 * @return void
	 */
	public static function add_menu_page(): void {
		add_options_page(
			esc_html__( 'BookIt for Cal.com', 'bookit-for-calcom' ),
			esc_html__( 'BookIt', 'bookit-for-calcom' ),
			'manage_options',
			'bookit-for-calcom',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Register settings, sections, and fields.
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		register_setting(
			'bookit_settings_group',
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);

		// Section: Cal.com Account.
		add_settings_section(
			'bookit_section_account',
			esc_html__( 'Cal.com Account', 'bookit-for-calcom' ),
			'__return_false',
			'bookit-for-calcom'
		);

		add_settings_field(
			'bookit_api_key',
			esc_html__( 'API Key', 'bookit-for-calcom' ),
			array( __CLASS__, 'field_api_key' ),
			'bookit-for-calcom',
			'bookit_section_account'
		);

		add_settings_field(
			'bookit_username',
			esc_html__( 'Cal.com Username', 'bookit-for-calcom' ),
			array( __CLASS__, 'field_username' ),
			'bookit-for-calcom',
			'bookit_section_account'
		);

		// Section: Widget Defaults.
		add_settings_section(
			'bookit_section_widget',
			esc_html__( 'Widget Defaults', 'bookit-for-calcom' ),
			'__return_false',
			'bookit-for-calcom'
		);

		add_settings_field(
			'bookit_namespace',
			esc_html__( 'Cal.com JS Namespace', 'bookit-for-calcom' ),
			array( __CLASS__, 'field_namespace' ),
			'bookit-for-calcom',
			'bookit_section_widget'
		);

		add_settings_field(
			'bookit_theme',
			esc_html__( 'Theme', 'bookit-for-calcom' ),
			array( __CLASS__, 'field_theme' ),
			'bookit-for-calcom',
			'bookit_section_widget'
		);

		add_settings_field(
			'bookit_accent_color',
			esc_html__( 'Accent Color', 'bookit-for-calcom' ),
			array( __CLASS__, 'field_accent_color' ),
			'bookit-for-calcom',
			'bookit_section_widget'
		);

		add_settings_field(
			'bookit_hide_branding',
			esc_html__( 'Hide Cal.com Branding', 'bookit-for-calcom' ),
			array( __CLASS__, 'field_hide_branding' ),
			'bookit-for-calcom',
			'bookit_section_widget'
		);

		// Section: Performance.
		add_settings_section(
			'bookit_section_perf',
			esc_html__( 'Performance', 'bookit-for-calcom' ),
			'__return_false',
			'bookit-for-calcom'
		);

		add_settings_field(
			'bookit_load_strategy',
			esc_html__( 'Script Loading Strategy', 'bookit-for-calcom' ),
			array( __CLASS__, 'field_load_strategy' ),
			'bookit-for-calcom',
			'bookit_section_perf'
		);
	}

	/**
	 * Retrieve stored settings with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_settings(): array {
		$defaults = array(
			'api_key'        => '',
			'username'       => '',
			'namespace'      => 'cal',
			'theme'          => 'auto',
			'accent_color'   => '#000000',
			'hide_branding'  => false,
			'load_strategy'  => 'smart',
		);
		$stored = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $stored, $defaults );
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param mixed $raw Raw input from the form.
	 * @return array<string, mixed>
	 */
	public static function sanitize_settings( mixed $raw ): array {
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		$clean = array();

		$clean['api_key']       = sanitize_text_field( $raw['api_key'] ?? '' );
		$clean['username']      = sanitize_text_field( $raw['username'] ?? '' );
		$clean['namespace']     = sanitize_key( $raw['namespace'] ?? 'cal' );
		$clean['theme']         = in_array( $raw['theme'] ?? '', array( 'light', 'dark', 'auto' ), true )
			? $raw['theme']
			: 'auto';
		$clean['accent_color']  = sanitize_hex_color( $raw['accent_color'] ?? '#000000' ) ?? '#000000';
		$clean['hide_branding'] = ! empty( $raw['hide_branding'] );
		$clean['load_strategy'] = in_array( $raw['load_strategy'] ?? '', array( 'smart', 'always' ), true )
			? $raw['load_strategy']
			: 'smart';

		// Flush cache when API key changes.
		if ( $clean['api_key'] !== ( self::get_settings()['api_key'] ?? '' ) ) {
			BookIt_API::flush_cache();
		}

		return $clean;
	}

	// -------------------------------------------------------------------------
	// Field renderers.
	// -------------------------------------------------------------------------

	/**
	 * Render the API Key field.
	 *
	 * @return void
	 */
	public static function field_api_key(): void {
		$settings = self::get_settings();
		?>
		<input
			type="password"
			id="bookit_api_key"
			name="bookit_settings[api_key]"
			value="<?php echo esc_attr( $settings['api_key'] ); ?>"
			class="regular-text"
			autocomplete="off"
		/>
		<p class="description">
			<?php esc_html_e( 'Your Cal.com API key. Found in Cal.com → Settings → Developer → API Keys.', 'bookit-for-calcom' ); ?>
		</p>
		<button
			type="button"
			id="bookit-refresh-event-types"
			class="button"
			style="margin-top:8px;"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'bookit_refresh_event_types' ) ); ?>"
		>
			<?php esc_html_e( 'Refresh event types', 'bookit-for-calcom' ); ?>
		</button>
		<span id="bookit-refresh-status" style="margin-left:10px;"></span>
		<?php
	}

	/**
	 * Render the Username field.
	 *
	 * @return void
	 */
	public static function field_username(): void {
		$settings = self::get_settings();
		?>
		<input
			type="text"
			id="bookit_username"
			name="bookit_settings[username]"
			value="<?php echo esc_attr( $settings['username'] ); ?>"
			class="regular-text"
		/>
		<p class="description">
			<?php esc_html_e( 'Your Cal.com username (used as URL prefix when no API key is set).', 'bookit-for-calcom' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the JS Namespace field.
	 *
	 * @return void
	 */
	public static function field_namespace(): void {
		$settings = self::get_settings();
		?>
		<input
			type="text"
			id="bookit_namespace"
			name="bookit_settings[namespace]"
			value="<?php echo esc_attr( $settings['namespace'] ); ?>"
			class="small-text"
		/>
		<p class="description">
			<?php esc_html_e( 'Cal.com JS namespace (default: cal). Change only if you have conflicts.', 'bookit-for-calcom' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the Theme select field.
	 *
	 * @return void
	 */
	public static function field_theme(): void {
		$settings = self::get_settings();
		$options  = array(
			'auto'  => __( 'Auto (follow browser)', 'bookit-for-calcom' ),
			'light' => __( 'Light', 'bookit-for-calcom' ),
			'dark'  => __( 'Dark', 'bookit-for-calcom' ),
		);
		?>
		<select id="bookit_theme" name="bookit_settings[theme]">
			<?php foreach ( $options as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['theme'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render the Accent Color field.
	 *
	 * @return void
	 */
	public static function field_accent_color(): void {
		$settings = self::get_settings();
		?>
		<input
			type="color"
			id="bookit_accent_color"
			name="bookit_settings[accent_color]"
			value="<?php echo esc_attr( $settings['accent_color'] ); ?>"
		/>
		<?php
	}

	/**
	 * Render the Hide Branding checkbox.
	 *
	 * @return void
	 */
	public static function field_hide_branding(): void {
		$settings = self::get_settings();
		?>
		<label>
			<input
				type="checkbox"
				id="bookit_hide_branding"
				name="bookit_settings[hide_branding]"
				value="1"
				<?php checked( $settings['hide_branding'] ); ?>
			/>
			<?php esc_html_e( 'Hide "Powered by Cal.com" branding (requires Cal.com Pro).', 'bookit-for-calcom' ); ?>
		</label>
		<?php
	}

	/**
	 * Render the Load Strategy select field.
	 *
	 * @return void
	 */
	public static function field_load_strategy(): void {
		$settings = self::get_settings();
		$options  = array(
			'smart'  => __( 'Smart — only on pages with a booking widget', 'bookit-for-calcom' ),
			'always' => __( 'Always — on every frontend page', 'bookit-for-calcom' ),
		);
		?>
		<select id="bookit_load_strategy" name="bookit_settings[load_strategy]">
			<?php foreach ( $options as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['load_strategy'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( '"Smart" reduces page weight by loading the Cal.com script only where needed.', 'bookit-for-calcom' ); ?>
		</p>
		<?php
	}

	// -------------------------------------------------------------------------
	// Page render.
	// -------------------------------------------------------------------------

	/**
	 * Render the settings page HTML.
	 *
	 * @return void
	 */
	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap bookit-admin-wrap">
			<h1><?php esc_html_e( 'BookIt for Cal.com', 'bookit-for-calcom' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'bookit_settings_group' );
				do_settings_sections( 'bookit-for-calcom' );
				submit_button( esc_html__( 'Save Settings', 'bookit-for-calcom' ) );
				?>
			</form>
		</div>
		<?php
	}

	// -------------------------------------------------------------------------
	// Assets.
	// -------------------------------------------------------------------------

	/**
	 * Enqueue admin CSS and JS on the plugin settings page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_admin_assets( string $hook ): void {
		if ( 'settings_page_bookit-for-calcom' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'bookit-admin',
			BOOKIT_PLUGIN_URL . 'assets/css/bookit-admin.css',
			array(),
			BOOKIT_VERSION
		);

		wp_enqueue_script(
			'bookit-admin',
			BOOKIT_PLUGIN_URL . 'assets/js/bookit-admin.js',
			array( 'jquery' ),
			BOOKIT_VERSION,
			true
		);

		wp_localize_script(
			'bookit-admin',
			'bookitAdminData',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'refreshNonce' => wp_create_nonce( 'bookit_refresh_event_types' ),
				'msgSuccess'   => __( 'Event types refreshed successfully.', 'bookit-for-calcom' ),
				'msgError'     => __( 'Could not refresh event types.', 'bookit-for-calcom' ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// AJAX.
	// -------------------------------------------------------------------------

	/**
	 * AJAX handler: refresh and return fresh event types from Cal.com API.
	 *
	 * @return void
	 */
	public static function ajax_refresh_event_types(): void {
		check_ajax_referer( 'bookit_refresh_event_types', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'bookit-for-calcom' ), 403 );
		}

		BookIt_API::flush_cache();

		$settings    = self::get_settings();
		$event_types = BookIt_API::get_event_types( $settings['api_key'] );

		if ( is_wp_error( $event_types ) ) {
			wp_send_json_error( $event_types->get_error_message() );
		}

		wp_send_json_success(
			array(
				'count'  => count( $event_types ),
				'events' => $event_types,
			)
		);
	}
}
