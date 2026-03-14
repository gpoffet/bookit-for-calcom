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
			'bookit_api_base',
			esc_html__( 'API Base URL', 'bookit-for-calcom' ),
			array( __CLASS__, 'field_api_base' ),
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
			'api_base'       => 'https://api.cal.com/v2',
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

		$preset_urls = array(
			'global' => 'https://api.cal.com/v2',
			'eu'     => 'https://api.cal.eu/v2',
		);
		$api_instance = sanitize_key( $raw['api_instance'] ?? 'global' );
		if ( isset( $preset_urls[ $api_instance ] ) ) {
			$clean['api_base'] = $preset_urls[ $api_instance ];
		} else {
			$clean['api_base'] = esc_url_raw( rtrim( $raw['api_base'] ?? 'https://api.cal.com/v2', '/' ) ) ?: 'https://api.cal.com/v2';
		}
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

		// Flush cache when API key or base URL changes.
		$prev = self::get_settings();
		if ( $clean['api_key'] !== $prev['api_key'] || $clean['api_base'] !== $prev['api_base'] ) {
			BookIt_API::flush_cache();
		}

		// Auto-populate username when API key is set but username is empty.
		// Uses event-types fetch (which we know works) to warm the username cache,
		// then retrieves it. Runs only once — when username is blank.
		if ( ! empty( $clean['api_key'] ) && empty( $clean['username'] ) ) {
			BookIt_API::get_event_types( $clean['api_key'], $clean['api_base'] );
			$auto = BookIt_API::get_username( $clean['api_key'], $clean['api_base'] );
			if ( ! empty( $auto ) ) {
				$clean['username'] = $auto;
			}
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
	 * Render the API Base URL field.
	 *
	 * @return void
	 */
	public static function field_api_base(): void {
		$settings = self::get_settings();
		$api_base = $settings['api_base'];

		$presets = array(
			'global' => 'https://api.cal.com/v2',
			'eu'     => 'https://api.cal.eu/v2',
		);

		$current_preset = 'custom';
		foreach ( $presets as $key => $url ) {
			if ( $api_base === $url ) {
				$current_preset = $key;
				break;
			}
		}
		$is_custom = 'custom' === $current_preset;
		?>

		<!-- Instance selector — name attribute lets PHP derive api_base on save -->
		<select id="bookit_api_instance" name="bookit_settings[api_instance]">
			<option value="global" <?php selected( $current_preset, 'global' ); ?>>
				<?php esc_html_e( 'Global — app.cal.com', 'bookit-for-calcom' ); ?>
			</option>
			<option value="eu" <?php selected( $current_preset, 'eu' ); ?>>
				<?php esc_html_e( 'Europe — app.cal.eu', 'bookit-for-calcom' ); ?>
			</option>
			<option value="custom" <?php selected( $current_preset, 'custom' ); ?>>
				<?php esc_html_e( 'Custom&hellip;', 'bookit-for-calcom' ); ?>
			</option>
		</select>

		<!-- Hidden URL input — always submitted via Settings API -->
		<input
			type="url"
			id="bookit_api_base"
			name="bookit_settings[api_base]"
			value="<?php echo esc_attr( $api_base ); ?>"
			class="regular-text"
			placeholder="https://api.cal.com/v2"
			<?php if ( ! $is_custom ) : ?>style="display:none;"<?php endif; ?>
		/>

		<p class="description">
			<?php esc_html_e( 'Select your Cal.com instance. To find out which one you use: log in to Cal.com — if your URL starts with app.cal.eu, choose "Europe", otherwise choose "Global".', 'bookit-for-calcom' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the Username field.
	 *
	 * @return void
	 */
	public static function field_username(): void {
		$settings    = self::get_settings();
		$has_api_key = ! empty( $settings['api_key'] );
		?>
		<input
			type="text"
			id="bookit_username"
			name="bookit_settings[username]"
			value="<?php echo esc_attr( $settings['username'] ); ?>"
			class="regular-text"
			<?php if ( $has_api_key ) : ?>
				readonly
				style="background:#f0f0f1;color:#666;cursor:default;"
			<?php endif; ?>
		/>
		<p class="description" id="bookit-username-desc">
			<?php if ( $has_api_key ) : ?>
				<?php esc_html_e( 'Auto-detected from your API key. Click "Refresh event types" to update.', 'bookit-for-calcom' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'Your Cal.com username (used as URL prefix). Required when no API key is set.', 'bookit-for-calcom' ); ?>
			<?php endif; ?>
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
	 * Render the settings page HTML — dispatches to the active tab.
	 *
	 * @return void
	 */
	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab routing, no data mutation.
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
		$page        = 'bookit-for-calcom';
		?>
		<div class="wrap bookit-admin-wrap">
			<h1><?php esc_html_e( 'BookIt for Cal.com', 'bookit-for-calcom' ); ?></h1>

			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'BookIt settings tabs', 'bookit-for-calcom' ); ?>">
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . $page . '&tab=settings' ) ); ?>"
				   class="nav-tab<?php echo 'settings' === $current_tab ? ' nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Settings', 'bookit-for-calcom' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . $page . '&tab=shortcode' ) ); ?>"
				   class="nav-tab<?php echo 'shortcode' === $current_tab ? ' nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Shortcode Helper', 'bookit-for-calcom' ); ?>
				</a>
			</nav>

			<?php
			if ( 'shortcode' === $current_tab ) {
				self::render_tab_shortcode();
			} else {
				self::render_tab_settings();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render the Settings tab (existing settings form).
	 *
	 * @return void
	 */
	private static function render_tab_settings(): void {
		?>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'bookit_settings_group' );
			do_settings_sections( 'bookit-for-calcom' );
			submit_button( esc_html__( 'Save Settings', 'bookit-for-calcom' ) );
			?>
		</form>
		<?php
	}

	/**
	 * Render the Shortcode Helper tab.
	 *
	 * @return void
	 */
	private static function render_tab_shortcode(): void {
		?>
		<div class="bookit-shortcode-helper">

			<p class="description">
				<?php esc_html_e( 'Configure your shortcode options below. The shortcode updates in real time — only non-default values are included to keep it clean.', 'bookit-for-calcom' ); ?>
			</p>

			<!-- Output bar -->
			<div class="bookit-sh-output">
				<code id="bookit-sh-result" class="bookit-sh-code">[bookit event=""]</code>
				<button type="button" id="bookit-sh-copy" class="button button-secondary">
					<?php esc_html_e( 'Copy', 'bookit-for-calcom' ); ?>
				</button>
				<span id="bookit-sh-copy-status" aria-live="polite"></span>
			</div>

			<!-- Configuration form -->
			<div id="bookit-sh-form">
				<table class="form-table" role="presentation">
					<tbody>

						<!-- Event -->
						<tr>
							<th scope="row">
								<label for="bookit-sh-event"><?php esc_html_e( 'Event', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="text" id="bookit-sh-event" data-bookit-attr="event"
									class="regular-text" placeholder="username/slug" />
								<p class="description">
									<?php esc_html_e( 'Your Cal.com event type. Format: username/slug (e.g. jane/consultation-30min).', 'bookit-for-calcom' ); ?>
								</p>
							</td>
						</tr>

						<!-- Display type -->
						<tr>
							<th scope="row">
								<label for="bookit-sh-type"><?php esc_html_e( 'Display Type', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<select id="bookit-sh-type" data-bookit-attr="type">
									<option value="popup-button"><?php esc_html_e( 'Popup Button', 'bookit-for-calcom' ); ?></option>
									<option value="popup-text"><?php esc_html_e( 'Popup Text Link', 'bookit-for-calcom' ); ?></option>
									<option value="inline"><?php esc_html_e( 'Inline Embed', 'bookit-for-calcom' ); ?></option>
								</select>
							</td>
						</tr>

						<!-- Label (popup types only) -->
						<tr data-bookit-show="popup">
							<th scope="row">
								<label for="bookit-sh-label"><?php esc_html_e( 'Button / Link Label', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="text" id="bookit-sh-label" data-bookit-attr="label"
									class="regular-text" value="Book a meeting" />
							</td>
						</tr>

						<!-- Height (inline only) -->
						<tr data-bookit-show="inline" class="bookit-sh-hidden">
							<th scope="row">
								<label for="bookit-sh-height"><?php esc_html_e( 'Height (px)', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-height" data-bookit-attr="height"
									class="small-text" value="600" min="100" max="2000" />
							</td>
						</tr>

						<!-- Theme -->
						<tr>
							<th scope="row">
								<label for="bookit-sh-theme"><?php esc_html_e( 'Theme', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<select id="bookit-sh-theme" data-bookit-attr="theme">
									<option value="global"><?php esc_html_e( 'Global (use plugin setting)', 'bookit-for-calcom' ); ?></option>
									<option value="auto"><?php esc_html_e( 'Auto (browser preference)', 'bookit-for-calcom' ); ?></option>
									<option value="light"><?php esc_html_e( 'Light', 'bookit-for-calcom' ); ?></option>
									<option value="dark"><?php esc_html_e( 'Dark', 'bookit-for-calcom' ); ?></option>
								</select>
							</td>
						</tr>

						<!-- Accent color -->
						<tr>
							<th scope="row">
								<label for="bookit-sh-accent-picker"><?php esc_html_e( 'Accent Color', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-accent" data-bookit-attr="accent" value="" />
								<input type="color" id="bookit-sh-accent-picker" value="#000000" />
								<button type="button" id="bookit-sh-accent-clear" class="button button-small">
									<?php esc_html_e( 'Clear', 'bookit-for-calcom' ); ?>
								</button>
								<p class="description">
									<?php esc_html_e( 'Leave unset to use the global accent color from Settings.', 'bookit-for-calcom' ); ?>
								</p>
							</td>
						</tr>

						<!-- Hide details -->
						<tr>
							<th scope="row"><?php esc_html_e( 'Hide Details', 'bookit-for-calcom' ); ?></th>
							<td>
								<label>
									<input type="checkbox" id="bookit-sh-hide-details" data-bookit-attr="hide_details" />
									<?php esc_html_e( 'Hide the event details panel in the booking modal', 'bookit-for-calcom' ); ?>
								</label>
							</td>
						</tr>

						<!-- Prefill -->
						<tr>
							<th scope="row"><?php esc_html_e( 'Prefill User Data', 'bookit-for-calcom' ); ?></th>
							<td>
								<label>
									<input type="checkbox" id="bookit-sh-prefill" data-bookit-attr="prefill" />
									<?php esc_html_e( 'Pre-fill the logged-in user\'s name and email', 'bookit-for-calcom' ); ?>
								</label>
							</td>
						</tr>

						<!-- ── Button Styles (popup-button only) ───────────────── -->

						<tr data-bookit-show="popup-button">
							<td colspan="2" class="bookit-sh-section-heading">
								<h3><?php esc_html_e( 'Button Styles', 'bookit-for-calcom' ); ?></h3>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-bg-picker"><?php esc_html_e( 'Background Color', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-bg" data-bookit-attr="btn_bg" value="" />
								<input type="color" id="bookit-sh-btn-bg-picker" value="#000000" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-bg">
									<?php esc_html_e( 'Clear', 'bookit-for-calcom' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-text-picker"><?php esc_html_e( 'Text Color', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-text" data-bookit-attr="btn_text" value="" />
								<input type="color" id="bookit-sh-btn-text-picker" value="#ffffff" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-text">
									<?php esc_html_e( 'Clear', 'bookit-for-calcom' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-radius"><?php esc_html_e( 'Border Radius (px)', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-btn-radius" data-bookit-attr="btn_radius"
									class="small-text" value="4" min="0" max="50" />
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-border-width"><?php esc_html_e( 'Border Width (px)', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-btn-border-width" data-bookit-attr="btn_border_width"
									class="small-text" value="0" min="0" max="20" />
							</td>
						</tr>

						<tr data-bookit-show="popup-button" data-bookit-border-conditional class="bookit-sh-hidden">
							<th scope="row">
								<label for="bookit-sh-btn-border-style"><?php esc_html_e( 'Border Style', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<select id="bookit-sh-btn-border-style" data-bookit-attr="btn_border_style">
									<option value="solid"><?php esc_html_e( 'Solid', 'bookit-for-calcom' ); ?></option>
									<option value="dashed"><?php esc_html_e( 'Dashed', 'bookit-for-calcom' ); ?></option>
									<option value="dotted"><?php esc_html_e( 'Dotted', 'bookit-for-calcom' ); ?></option>
								</select>
							</td>
						</tr>

						<tr data-bookit-show="popup-button" data-bookit-border-conditional class="bookit-sh-hidden">
							<th scope="row">
								<label for="bookit-sh-btn-border-color-picker"><?php esc_html_e( 'Border Color', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-border-color" data-bookit-attr="btn_border_color" value="" />
								<input type="color" id="bookit-sh-btn-border-color-picker" value="#000000" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-border-color">
									<?php esc_html_e( 'Clear', 'bookit-for-calcom' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row"><?php esc_html_e( 'Padding (px)', 'bookit-for-calcom' ); ?></th>
							<td class="bookit-sh-padding-row">
								<label><?php esc_html_e( 'Top', 'bookit-for-calcom' ); ?>
									<input type="number" data-bookit-attr="btn_padding_top" class="small-text" value="10" min="0" />
								</label>
								<label><?php esc_html_e( 'Right', 'bookit-for-calcom' ); ?>
									<input type="number" data-bookit-attr="btn_padding_right" class="small-text" value="20" min="0" />
								</label>
								<label><?php esc_html_e( 'Bottom', 'bookit-for-calcom' ); ?>
									<input type="number" data-bookit-attr="btn_padding_bottom" class="small-text" value="10" min="0" />
								</label>
								<label><?php esc_html_e( 'Left', 'bookit-for-calcom' ); ?>
									<input type="number" data-bookit-attr="btn_padding_left" class="small-text" value="20" min="0" />
								</label>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-full-width"><?php esc_html_e( 'Full Width', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<label>
									<input type="checkbox" id="bookit-sh-btn-full-width" data-bookit-attr="btn_full_width" />
									<?php esc_html_e( 'Stretch button to full container width', 'bookit-for-calcom' ); ?>
								</label>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-font-size"><?php esc_html_e( 'Font Size (px)', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-btn-font-size" data-bookit-attr="btn_font_size"
									class="small-text" value="14" min="10" max="36" />
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-font-weight"><?php esc_html_e( 'Font Weight', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<select id="bookit-sh-btn-font-weight" data-bookit-attr="btn_font_weight">
									<option value=""><?php esc_html_e( 'Default', 'bookit-for-calcom' ); ?></option>
									<option value="300"><?php esc_html_e( '300 — Light', 'bookit-for-calcom' ); ?></option>
									<option value="400"><?php esc_html_e( '400 — Normal', 'bookit-for-calcom' ); ?></option>
									<option value="500"><?php esc_html_e( '500 — Medium', 'bookit-for-calcom' ); ?></option>
									<option value="600"><?php esc_html_e( '600 — Semi Bold', 'bookit-for-calcom' ); ?></option>
									<option value="700"><?php esc_html_e( '700 — Bold', 'bookit-for-calcom' ); ?></option>
									<option value="800"><?php esc_html_e( '800 — Extra Bold', 'bookit-for-calcom' ); ?></option>
								</select>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-text-transform"><?php esc_html_e( 'Text Transform', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<select id="bookit-sh-btn-text-transform" data-bookit-attr="btn_text_transform">
									<option value=""><?php esc_html_e( 'None', 'bookit-for-calcom' ); ?></option>
									<option value="uppercase"><?php esc_html_e( 'Uppercase', 'bookit-for-calcom' ); ?></option>
									<option value="lowercase"><?php esc_html_e( 'Lowercase', 'bookit-for-calcom' ); ?></option>
									<option value="capitalize"><?php esc_html_e( 'Capitalize', 'bookit-for-calcom' ); ?></option>
								</select>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-letter-spacing"><?php esc_html_e( 'Letter Spacing (px)', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-btn-letter-spacing" data-bookit-attr="btn_letter_spacing"
									class="small-text" value="0" min="0" max="10" step="0.5" />
							</td>
						</tr>

						<!-- Hover effects -->
						<tr data-bookit-show="popup-button">
							<td colspan="2" class="bookit-sh-section-heading">
								<h3><?php esc_html_e( 'Hover Effects', 'bookit-for-calcom' ); ?></h3>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-hover-bg-picker"><?php esc_html_e( 'Hover Background', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-hover-bg" data-bookit-attr="btn_hover_bg" value="" />
								<input type="color" id="bookit-sh-btn-hover-bg-picker" value="#000000" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-hover-bg">
									<?php esc_html_e( 'Clear', 'bookit-for-calcom' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-hover-text-picker"><?php esc_html_e( 'Hover Text Color', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-hover-text" data-bookit-attr="btn_hover_text" value="" />
								<input type="color" id="bookit-sh-btn-hover-text-picker" value="#ffffff" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-hover-text">
									<?php esc_html_e( 'Clear', 'bookit-for-calcom' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button" data-bookit-border-conditional class="bookit-sh-hidden">
							<th scope="row">
								<label for="bookit-sh-btn-hover-border-picker"><?php esc_html_e( 'Hover Border Color', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-hover-border-color" data-bookit-attr="btn_hover_border_color" value="" />
								<input type="color" id="bookit-sh-btn-hover-border-picker" value="#000000" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-hover-border-color">
									<?php esc_html_e( 'Clear', 'bookit-for-calcom' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-transition"><?php esc_html_e( 'Transition Duration (ms)', 'bookit-for-calcom' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-btn-transition" data-bookit-attr="btn_transition_duration"
									class="small-text" value="200" min="0" max="500" step="50" />
							</td>
						</tr>

					</tbody>
				</table>
			</div><!-- #bookit-sh-form -->

			<!-- Reference table -->
			<div class="bookit-sh-reference">
				<details>
					<summary><?php esc_html_e( 'Shortcode attribute reference', 'bookit-for-calcom' ); ?></summary>
					<table>
						<thead>
							<tr>
								<th><?php esc_html_e( 'Attribute', 'bookit-for-calcom' ); ?></th>
								<th><?php esc_html_e( 'Accepted values', 'bookit-for-calcom' ); ?></th>
								<th><?php esc_html_e( 'Default', 'bookit-for-calcom' ); ?></th>
								<th><?php esc_html_e( 'Description', 'bookit-for-calcom' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr><td><code>event</code></td><td><?php esc_html_e( 'username/slug', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( '(required)', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( 'Cal.com event type identifier.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>type</code></td><td><code>popup-button</code> | <code>popup-text</code> | <code>inline</code></td><td><code>popup-button</code></td><td><?php esc_html_e( 'Widget display mode.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>label</code></td><td><?php esc_html_e( 'text', 'bookit-for-calcom' ); ?></td><td><code>Book a meeting</code></td><td><?php esc_html_e( 'Button or link text (popup types only).', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>height</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-calcom' ); ?></td><td><code>600</code></td><td><?php esc_html_e( 'Iframe height in pixels (inline type only).', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>theme</code></td><td><code>global</code> | <code>auto</code> | <code>light</code> | <code>dark</code></td><td><code>global</code></td><td><?php esc_html_e( 'Cal.com UI theme.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>accent</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( '(global setting)', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( 'Accent color override.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>hide_details</code></td><td><code>0</code> | <code>1</code></td><td><code>0</code></td><td><?php esc_html_e( 'Hide the booking details panel.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>prefill</code></td><td><code>0</code> | <code>1</code></td><td><code>0</code></td><td><?php esc_html_e( 'Pre-fill logged-in user name and email.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_bg</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( 'Button background color.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_text</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( 'Button text color.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_radius</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-calcom' ); ?></td><td><code>4</code></td><td><?php esc_html_e( 'Button border radius.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_border_width</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-calcom' ); ?></td><td><code>0</code></td><td><?php esc_html_e( 'Button border width.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_border_style</code></td><td><code>solid</code> | <code>dashed</code> | <code>dotted</code></td><td><code>solid</code></td><td><?php esc_html_e( 'Button border style (requires border width &gt; 0).', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_border_color</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( 'Button border color (requires border width &gt; 0).', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_padding_top</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-calcom' ); ?></td><td><code>10</code></td><td><?php esc_html_e( 'Button top padding.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_padding_right</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-calcom' ); ?></td><td><code>20</code></td><td><?php esc_html_e( 'Button right padding.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_padding_bottom</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-calcom' ); ?></td><td><code>10</code></td><td><?php esc_html_e( 'Button bottom padding.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_padding_left</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-calcom' ); ?></td><td><code>20</code></td><td><?php esc_html_e( 'Button left padding.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_font_size</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-calcom' ); ?></td><td><code>14</code></td><td><?php esc_html_e( 'Button font size.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_font_weight</code></td><td><code>300</code> | <code>400</code> | <code>500</code> | <code>600</code> | <code>700</code> | <code>800</code></td><td><?php esc_html_e( '(inherit)', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( 'Button font weight.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_text_transform</code></td><td><code>uppercase</code> | <code>lowercase</code> | <code>capitalize</code></td><td><?php esc_html_e( '(none)', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( 'Button text transform.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_letter_spacing</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-calcom' ); ?></td><td><code>0</code></td><td><?php esc_html_e( 'Button letter spacing.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_full_width</code></td><td><code>0</code> | <code>1</code></td><td><code>0</code></td><td><?php esc_html_e( 'Stretch button to full width.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_hover_bg</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( 'Button hover background color.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_hover_text</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( 'Button hover text color.', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_hover_border_color</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-calcom' ); ?></td><td><?php esc_html_e( 'Button hover border color (requires border width &gt; 0).', 'bookit-for-calcom' ); ?></td></tr>
							<tr><td><code>btn_transition_duration</code></td><td><?php esc_html_e( 'number (ms)', 'bookit-for-calcom' ); ?></td><td><code>200</code></td><td><?php esc_html_e( 'Hover transition duration in milliseconds.', 'bookit-for-calcom' ); ?></td></tr>
						</tbody>
					</table>
				</details>
			</div>

		</div><!-- .bookit-shortcode-helper -->
		<?php
	}

	/**
	 * Return event types formatted for JavaScript consumption.
	 *
	 * Reads the cached transient and returns an array of slug/label pairs.
	 * Returns an empty array when no API key is configured or the cache is cold.
	 *
	 * @return array<int, array{slug: string, label: string}>
	 */
	private static function get_localized_event_types(): array {
		$settings    = self::get_settings();
		$event_types = get_transient( BookIt_API::TRANSIENT_KEY );
		$username    = ! empty( $settings['username'] )
			? $settings['username']
			: (string) get_transient( BookIt_API::TRANSIENT_USERNAME );

		if ( empty( $event_types ) || ! is_array( $event_types ) ) {
			return array();
		}

		$out = array();
		foreach ( $event_types as $et ) {
			$slug  = isset( $et['slug'] )  ? sanitize_text_field( $et['slug'] )  : '';
			$title = isset( $et['title'] ) ? sanitize_text_field( $et['title'] ) : $slug;
			if ( empty( $slug ) ) {
				continue;
			}
			$qualified = ! empty( $username ) ? $username . '/' . $slug : $slug;
			$out[]     = array(
				'slug'  => $qualified,
				'label' => $title . ' \u2014 ' . $slug,
			);
		}
		return $out;
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
			array(),
			BOOKIT_VERSION,
			true
		);

		$settings = self::get_settings();

		wp_localize_script(
			'bookit-admin',
			'bookitAdminData',
			array(
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'refreshNonce'     => wp_create_nonce( 'bookit_refresh_event_types' ),
				'hasApiKey'        => ! empty( $settings['api_key'] ) ? '1' : '0',
				// Return cached username only — no HTTP call on page load.
				'autoUsername'     => (string) ( get_transient( BookIt_API::TRANSIENT_USERNAME ) ?: '' ),
				'msgSuccess'       => __( 'Event types refreshed successfully.', 'bookit-for-calcom' ),
				'msgError'         => __( 'Could not refresh event types.', 'bookit-for-calcom' ),
				'msgUsernameAuto'   => __( 'Auto-detected from your API key. Click "Refresh event types" to update.', 'bookit-for-calcom' ),
				'msgUsernameManual' => __( 'Your Cal.com username (used as URL prefix). Required when no API key is set.', 'bookit-for-calcom' ),
				'eventTypes'        => self::get_localized_event_types(),
				'msgCopied'         => __( 'Copied!', 'bookit-for-calcom' ),
				'msgCopyFailed'     => __( 'Copy failed.', 'bookit-for-calcom' ),
			)
		);

		wp_enqueue_script(
			'bookit-shortcode-helper',
			BOOKIT_PLUGIN_URL . 'assets/js/bookit-shortcode-helper.js',
			array( 'bookit-admin' ),
			BOOKIT_VERSION,
			true
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

		// Use form field values (not yet saved) if provided,
		// otherwise fall back to stored settings.
		$settings = self::get_settings();

		$api_key  = sanitize_text_field( wp_unslash( $_POST['api_key'] ?? '' ) );
		if ( empty( $api_key ) ) {
			$api_key = $settings['api_key'];
		}

		$api_base = esc_url_raw( rtrim( wp_unslash( $_POST['api_base'] ?? '' ), '/' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- esc_url_raw() is the correct sanitizer for URL inputs.
		if ( empty( $api_base ) ) {
			$api_base = $settings['api_base'];
		}

		$event_types = BookIt_API::get_event_types( $api_key, $api_base );

		if ( is_wp_error( $event_types ) ) {
			wp_send_json_error( $event_types->get_error_message() );
		}

		$username = BookIt_API::get_username( $api_key, $api_base );

		// Persist the detected username immediately so render.php can use it
		// even if the user hasn't clicked "Save Settings" yet.
		if ( ! empty( $username ) ) {
			$stored = self::get_settings();
			if ( $stored['username'] !== $username ) {
				$stored['username'] = $username;
				update_option( self::OPTION_KEY, $stored );
			}
		}

		wp_send_json_success(
			array(
				'count'    => count( $event_types ),
				'events'   => $event_types,
				'username' => $username,
			)
		);
	}
}
