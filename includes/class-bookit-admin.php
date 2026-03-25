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
			esc_html__( 'BookIt for Cal.com', 'bookit-for-cal-com' ),
			esc_html__( 'BookIt', 'bookit-for-cal-com' ),
			'manage_options',
			'bookit-for-cal-com',
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
			esc_html__( 'Cal.com Account', 'bookit-for-cal-com' ),
			'__return_false',
			'bookit-for-cal-com'
		);

		add_settings_field(
			'bookit_api_key',
			esc_html__( 'API Key', 'bookit-for-cal-com' ),
			array( __CLASS__, 'field_api_key' ),
			'bookit-for-cal-com',
			'bookit_section_account'
		);

		add_settings_field(
			'bookit_api_base',
			esc_html__( 'API Base URL', 'bookit-for-cal-com' ),
			array( __CLASS__, 'field_api_base' ),
			'bookit-for-cal-com',
			'bookit_section_account'
		);

		add_settings_field(
			'bookit_username',
			esc_html__( 'Cal.com Username', 'bookit-for-cal-com' ),
			array( __CLASS__, 'field_username' ),
			'bookit-for-cal-com',
			'bookit_section_account'
		);

		// Section: Widget Defaults.
		add_settings_section(
			'bookit_section_widget',
			esc_html__( 'Widget Defaults', 'bookit-for-cal-com' ),
			'__return_false',
			'bookit-for-cal-com'
		);

		add_settings_field(
			'bookit_namespace',
			esc_html__( 'Cal.com JS Namespace', 'bookit-for-cal-com' ),
			array( __CLASS__, 'field_namespace' ),
			'bookit-for-cal-com',
			'bookit_section_widget'
		);

		add_settings_field(
			'bookit_theme',
			esc_html__( 'Theme', 'bookit-for-cal-com' ),
			array( __CLASS__, 'field_theme' ),
			'bookit-for-cal-com',
			'bookit_section_widget'
		);

		add_settings_field(
			'bookit_accent_color',
			esc_html__( 'Accent Color', 'bookit-for-cal-com' ),
			array( __CLASS__, 'field_accent_color' ),
			'bookit-for-cal-com',
			'bookit_section_widget'
		);

		add_settings_field(
			'bookit_hide_branding',
			esc_html__( 'Hide Cal.com Branding', 'bookit-for-cal-com' ),
			array( __CLASS__, 'field_hide_branding' ),
			'bookit-for-cal-com',
			'bookit_section_widget'
		);

		// Section: Performance.
		add_settings_section(
			'bookit_section_perf',
			esc_html__( 'Performance', 'bookit-for-cal-com' ),
			'__return_false',
			'bookit-for-cal-com'
		);

		add_settings_field(
			'bookit_load_strategy',
			esc_html__( 'Script Loading Strategy', 'bookit-for-cal-com' ),
			array( __CLASS__, 'field_load_strategy' ),
			'bookit-for-cal-com',
			'bookit_section_perf'
		);

		// ── Style tab sections (page: bookit-settings-style) ──────────────────────────

		// Section: Button — General.
		add_settings_section(
			'bookit_section_style_button',
			esc_html__( 'Button — General', 'bookit-for-cal-com' ),
			'__return_false',
			'bookit-settings-style'
		);

		add_settings_field( 'bookit_default_label',  esc_html__( 'Default Label', 'bookit-for-cal-com' ),       array( __CLASS__, 'field_default_label' ),  'bookit-settings-style', 'bookit_section_style_button' );
		add_settings_field( 'bookit_btn_bg',         esc_html__( 'Background Color', 'bookit-for-cal-com' ),    array( __CLASS__, 'field_btn_bg' ),         'bookit-settings-style', 'bookit_section_style_button' );
		add_settings_field( 'bookit_btn_text',       esc_html__( 'Text Color', 'bookit-for-cal-com' ),          array( __CLASS__, 'field_btn_text' ),       'bookit-settings-style', 'bookit_section_style_button' );
		add_settings_field( 'bookit_btn_full_width', esc_html__( 'Full Width', 'bookit-for-cal-com' ),          array( __CLASS__, 'field_btn_full_width' ), 'bookit-settings-style', 'bookit_section_style_button' );

		// Section: Button — Border.
		add_settings_section(
			'bookit_section_style_border',
			esc_html__( 'Button — Border', 'bookit-for-cal-com' ),
			'__return_false',
			'bookit-settings-style'
		);

		add_settings_field( 'bookit_btn_border_width', esc_html__( 'Border Width', 'bookit-for-cal-com' ),       array( __CLASS__, 'field_btn_border_width' ), 'bookit-settings-style', 'bookit_section_style_border' );
		add_settings_field( 'bookit_btn_border_style', esc_html__( 'Border Style', 'bookit-for-cal-com' ),       array( __CLASS__, 'field_btn_border_style' ), 'bookit-settings-style', 'bookit_section_style_border' );
		add_settings_field( 'bookit_btn_border_color', esc_html__( 'Border Color', 'bookit-for-cal-com' ),       array( __CLASS__, 'field_btn_border_color' ), 'bookit-settings-style', 'bookit_section_style_border' );
		add_settings_field( 'bookit_btn_radius',       esc_html__( 'Border Radius', 'bookit-for-cal-com' ),      array( __CLASS__, 'field_btn_radius' ),       'bookit-settings-style', 'bookit_section_style_border' );

		// Section: Button — Typography.
		add_settings_section(
			'bookit_section_style_typo',
			esc_html__( 'Button — Typography', 'bookit-for-cal-com' ),
			'__return_false',
			'bookit-settings-style'
		);

		add_settings_field( 'bookit_btn_font_size',      esc_html__( 'Font Size', 'bookit-for-cal-com' ),        array( __CLASS__, 'field_btn_font_size' ),      'bookit-settings-style', 'bookit_section_style_typo' );
		add_settings_field( 'bookit_btn_font_weight',    esc_html__( 'Font Weight', 'bookit-for-cal-com' ),      array( __CLASS__, 'field_btn_font_weight' ),    'bookit-settings-style', 'bookit_section_style_typo' );
		add_settings_field( 'bookit_btn_text_transform', esc_html__( 'Text Transform', 'bookit-for-cal-com' ),   array( __CLASS__, 'field_btn_text_transform' ), 'bookit-settings-style', 'bookit_section_style_typo' );
		add_settings_field( 'bookit_btn_letter_spacing', esc_html__( 'Letter Spacing', 'bookit-for-cal-com' ),   array( __CLASS__, 'field_btn_letter_spacing' ), 'bookit-settings-style', 'bookit_section_style_typo' );

		// Section: Button — Padding.
		add_settings_section(
			'bookit_section_style_padding',
			esc_html__( 'Button — Padding', 'bookit-for-cal-com' ),
			'__return_false',
			'bookit-settings-style'
		);

		add_settings_field( 'bookit_btn_padding', esc_html__( 'Padding', 'bookit-for-cal-com' ), array( __CLASS__, 'field_btn_padding' ), 'bookit-settings-style', 'bookit_section_style_padding' );

		// Section: Button — Hover Effects.
		add_settings_section(
			'bookit_section_style_hover',
			esc_html__( 'Button — Hover Effects', 'bookit-for-cal-com' ),
			'__return_false',
			'bookit-settings-style'
		);

		add_settings_field( 'bookit_btn_hover_bg',           esc_html__( 'Hover Background Color', 'bookit-for-cal-com' ), array( __CLASS__, 'field_btn_hover_bg' ),           'bookit-settings-style', 'bookit_section_style_hover' );
		add_settings_field( 'bookit_btn_hover_text',         esc_html__( 'Hover Text Color', 'bookit-for-cal-com' ),       array( __CLASS__, 'field_btn_hover_text' ),         'bookit-settings-style', 'bookit_section_style_hover' );
		add_settings_field( 'bookit_btn_hover_border_color', esc_html__( 'Hover Border Color', 'bookit-for-cal-com' ),     array( __CLASS__, 'field_btn_hover_border_color' ), 'bookit-settings-style', 'bookit_section_style_hover' );
		add_settings_field( 'bookit_btn_transition_duration', esc_html__( 'Transition Duration', 'bookit-for-cal-com' ),   array( __CLASS__, 'field_btn_transition_duration' ), 'bookit-settings-style', 'bookit_section_style_hover' );

		// Section: Inline Calendar.
		add_settings_section(
			'bookit_section_style_inline',
			esc_html__( 'Inline Calendar', 'bookit-for-cal-com' ),
			'__return_false',
			'bookit-settings-style'
		);

		add_settings_field( 'bookit_inline_height', esc_html__( 'Default Height', 'bookit-for-cal-com' ), array( __CLASS__, 'field_inline_height' ), 'bookit-settings-style', 'bookit_section_style_inline' );
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
			// Style defaults — '' means "fall back to hardcoded value in the render layer".
			'default_label'           => '',
			'btn_bg'                  => '',
			'btn_text'                => '',
			'btn_radius'              => '',
			'btn_border_width'        => '',
			'btn_border_style'        => 'solid',
			'btn_border_color'        => '',
			'btn_padding_top'         => '',
			'btn_padding_right'       => '',
			'btn_padding_bottom'      => '',
			'btn_padding_left'        => '',
			'btn_font_size'           => '',
			'btn_font_weight'         => '',
			'btn_text_transform'      => '',
			'btn_letter_spacing'      => '',
			'btn_full_width'          => false,
			'btn_hover_bg'            => '',
			'btn_hover_text'          => '',
			'btn_hover_border_color'  => '',
			'btn_transition_duration' => '',
			'inline_height'           => '',
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

		// Which form tab submitted this data? The hidden _tab field tells us so
		// that fields from the non-active tab are preserved, not wiped.
		$tab             = sanitize_key( $raw['_tab'] ?? 'settings' );
		$is_settings_tab = 'settings' === $tab;
		$is_style_tab    = 'style' === $tab;

		// Existing stored values — used when a field is absent from the active form.
		$existing = self::get_settings();

		$clean = array();

		// ── Settings tab fields ──────────────────────────────────────────────

		if ( $is_settings_tab ) {
			$clean['api_key'] = sanitize_text_field( $raw['api_key'] ?? '' );

			$preset_urls  = array(
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
		} else {
			// Preserve existing main settings when the Style tab is saved.
			foreach ( array( 'api_key', 'api_base', 'username', 'namespace', 'theme', 'accent_color', 'hide_branding', 'load_strategy' ) as $_k ) {
				$clean[ $_k ] = $existing[ $_k ];
			}
		}

		// ── Style tab fields ─────────────────────────────────────────────────

		if ( $is_style_tab ) {
			$clean['default_label'] = sanitize_text_field( $raw['default_label'] ?? '' );
			$clean['btn_bg']        = sanitize_hex_color( $raw['btn_bg'] ?? '' ) ?: '';
			$clean['btn_text']      = sanitize_hex_color( $raw['btn_text'] ?? '' ) ?: '';

			$raw_radius                = $raw['btn_radius'] ?? '';
			$clean['btn_radius']       = ( '' !== $raw_radius ) ? absint( $raw_radius ) : '';
			$raw_bw                    = $raw['btn_border_width'] ?? '';
			$clean['btn_border_width'] = ( '' !== $raw_bw ) ? absint( $raw_bw ) : '';
			$clean['btn_border_style'] = in_array( $raw['btn_border_style'] ?? '', array( 'solid', 'dashed', 'dotted' ), true )
				? $raw['btn_border_style']
				: 'solid';
			$clean['btn_border_color'] = sanitize_hex_color( $raw['btn_border_color'] ?? '' ) ?: '';

			foreach ( array( 'btn_padding_top', 'btn_padding_right', 'btn_padding_bottom', 'btn_padding_left' ) as $pad_key ) {
				$raw_val           = $raw[ $pad_key ] ?? '';
				$clean[ $pad_key ] = ( '' !== $raw_val ) ? absint( $raw_val ) : '';
			}

			$raw_fs                      = $raw['btn_font_size'] ?? '';
			$clean['btn_font_size']      = ( '' !== $raw_fs ) ? absint( $raw_fs ) : '';
			$clean['btn_font_weight']    = in_array( $raw['btn_font_weight'] ?? '', array( '', '300', '400', '500', '600', '700', '800' ), true )
				? $raw['btn_font_weight']
				: '';
			$clean['btn_text_transform'] = in_array( $raw['btn_text_transform'] ?? '', array( '', 'uppercase', 'lowercase', 'capitalize' ), true )
				? $raw['btn_text_transform']
				: '';
			$raw_ls                      = $raw['btn_letter_spacing'] ?? '';
			$clean['btn_letter_spacing'] = ( '' !== $raw_ls && is_numeric( $raw_ls ) ) ? (float) $raw_ls : '';

			$clean['btn_full_width']          = ! empty( $raw['btn_full_width'] );
			$clean['btn_hover_bg']            = sanitize_hex_color( $raw['btn_hover_bg'] ?? '' ) ?: '';
			$clean['btn_hover_text']          = sanitize_hex_color( $raw['btn_hover_text'] ?? '' ) ?: '';
			$clean['btn_hover_border_color']  = sanitize_hex_color( $raw['btn_hover_border_color'] ?? '' ) ?: '';

			$raw_td                           = $raw['btn_transition_duration'] ?? '';
			$clean['btn_transition_duration'] = ( '' !== $raw_td ) ? absint( $raw_td ) : '';
			$raw_ih                           = $raw['inline_height'] ?? '';
			$clean['inline_height']           = ( '' !== $raw_ih ) ? absint( $raw_ih ) : '';
		} else {
			// Preserve existing style settings when the Settings tab is saved.
			$style_keys = array(
				'default_label', 'btn_bg', 'btn_text', 'btn_radius', 'btn_border_width',
				'btn_border_style', 'btn_border_color', 'btn_padding_top', 'btn_padding_right',
				'btn_padding_bottom', 'btn_padding_left', 'btn_font_size', 'btn_font_weight',
				'btn_text_transform', 'btn_letter_spacing', 'btn_full_width', 'btn_hover_bg',
				'btn_hover_text', 'btn_hover_border_color', 'btn_transition_duration', 'inline_height',
			);
			foreach ( $style_keys as $_k ) {
				$clean[ $_k ] = $existing[ $_k ];
			}
		}

		// ── Side effects (only relevant when Settings tab credentials change) ─

		if ( $is_settings_tab ) {
			if ( $clean['api_key'] !== $existing['api_key'] || $clean['api_base'] !== $existing['api_base'] ) {
				BookIt_API::flush_cache();
			}

			// Auto-populate username when API key is set but username is empty.
			if ( ! empty( $clean['api_key'] ) && empty( $clean['username'] ) ) {
				BookIt_API::get_event_types( $clean['api_key'], $clean['api_base'] );
				$auto = BookIt_API::get_username( $clean['api_key'], $clean['api_base'] );
				if ( ! empty( $auto ) ) {
					$clean['username'] = $auto;
				}
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
			<?php esc_html_e( 'Your Cal.com API key. Found in Cal.com → Settings → Developer → API Keys.', 'bookit-for-cal-com' ); ?>
		</p>
		<button
			type="button"
			id="bookit-refresh-event-types"
			class="button"
			style="margin-top:8px;"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'bookit_refresh_event_types' ) ); ?>"
		>
			<?php esc_html_e( 'Refresh event types', 'bookit-for-cal-com' ); ?>
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
				<?php esc_html_e( 'Global — app.cal.com', 'bookit-for-cal-com' ); ?>
			</option>
			<option value="eu" <?php selected( $current_preset, 'eu' ); ?>>
				<?php esc_html_e( 'Europe — app.cal.eu', 'bookit-for-cal-com' ); ?>
			</option>
			<option value="custom" <?php selected( $current_preset, 'custom' ); ?>>
				<?php esc_html_e( 'Custom&hellip;', 'bookit-for-cal-com' ); ?>
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
			<?php esc_html_e( 'Select your Cal.com instance. To find out which one you use: log in to Cal.com — if your URL starts with app.cal.eu, choose "Europe", otherwise choose "Global".', 'bookit-for-cal-com' ); ?>
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
				<?php esc_html_e( 'Auto-detected from your API key. Click "Refresh event types" to update.', 'bookit-for-cal-com' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'Your Cal.com username (used as URL prefix). Required when no API key is set.', 'bookit-for-cal-com' ); ?>
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
			<?php esc_html_e( 'Cal.com JS namespace (default: cal). Change only if you have conflicts.', 'bookit-for-cal-com' ); ?>
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
			'auto'  => __( 'Auto (follow browser)', 'bookit-for-cal-com' ),
			'light' => __( 'Light', 'bookit-for-cal-com' ),
			'dark'  => __( 'Dark', 'bookit-for-cal-com' ),
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
			<?php esc_html_e( 'Hide "Powered by Cal.com" branding (requires Cal.com Pro).', 'bookit-for-cal-com' ); ?>
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
			'smart'  => __( 'Smart — only on pages with a booking widget', 'bookit-for-cal-com' ),
			'always' => __( 'Always — on every frontend page', 'bookit-for-cal-com' ),
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
			<?php esc_html_e( '"Smart" reduces page weight by loading the Cal.com script only where needed.', 'bookit-for-cal-com' ); ?>
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
		$page        = 'bookit-for-cal-com';
		?>
		<div class="wrap bookit-admin-wrap">
			<h1><?php esc_html_e( 'BookIt for Cal.com', 'bookit-for-cal-com' ); ?></h1>

			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'BookIt settings tabs', 'bookit-for-cal-com' ); ?>">
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . $page . '&tab=settings' ) ); ?>"
				   class="nav-tab<?php echo 'settings' === $current_tab ? ' nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Settings', 'bookit-for-cal-com' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . $page . '&tab=shortcode' ) ); ?>"
				   class="nav-tab<?php echo 'shortcode' === $current_tab ? ' nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Shortcode Helper', 'bookit-for-cal-com' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . $page . '&tab=style' ) ); ?>"
				   class="nav-tab<?php echo 'style' === $current_tab ? ' nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Style', 'bookit-for-cal-com' ); ?>
				</a>
			</nav>

			<?php
			if ( 'shortcode' === $current_tab ) {
				self::render_tab_shortcode();
			} elseif ( 'style' === $current_tab ) {
				self::render_tab_style();
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
			<?php settings_fields( 'bookit_settings_group' ); ?>
			<input type="hidden" name="bookit_settings[_tab]" value="settings" />
			<?php
			do_settings_sections( 'bookit-for-cal-com' );
			submit_button( esc_html__( 'Save Settings', 'bookit-for-cal-com' ) );
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
				<?php esc_html_e( 'Configure your shortcode options below. The shortcode updates in real time — only non-default values are included to keep it clean.', 'bookit-for-cal-com' ); ?>
			</p>

			<!-- Output bar -->
			<div class="bookit-sh-output">
				<code id="bookit-sh-result" class="bookit-sh-code">[bookit event=""]</code>
				<button type="button" id="bookit-sh-copy" class="button button-secondary">
					<?php esc_html_e( 'Copy', 'bookit-for-cal-com' ); ?>
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
								<label for="bookit-sh-event"><?php esc_html_e( 'Event', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="text" id="bookit-sh-event" data-bookit-attr="event"
									class="regular-text" placeholder="username/slug" />
								<p class="description">
									<?php esc_html_e( 'Your Cal.com event type. Format: username/slug (e.g. jane/consultation-30min).', 'bookit-for-cal-com' ); ?>
								</p>
							</td>
						</tr>

						<!-- Display type -->
						<tr>
							<th scope="row">
								<label for="bookit-sh-type"><?php esc_html_e( 'Display Type', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<select id="bookit-sh-type" data-bookit-attr="type">
									<option value="popup-button"><?php esc_html_e( 'Popup Button', 'bookit-for-cal-com' ); ?></option>
									<option value="popup-text"><?php esc_html_e( 'Popup Text Link', 'bookit-for-cal-com' ); ?></option>
									<option value="inline"><?php esc_html_e( 'Inline Embed', 'bookit-for-cal-com' ); ?></option>
								</select>
							</td>
						</tr>

						<!-- Label (popup types only) -->
						<tr data-bookit-show="popup">
							<th scope="row">
								<label for="bookit-sh-label"><?php esc_html_e( 'Button / Link Label', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="text" id="bookit-sh-label" data-bookit-attr="label"
									class="regular-text" value="Book a meeting" />
							</td>
						</tr>

						<!-- Height (inline only) -->
						<tr data-bookit-show="inline" class="bookit-sh-hidden">
							<th scope="row">
								<label for="bookit-sh-height"><?php esc_html_e( 'Height (px)', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-height" data-bookit-attr="height"
									class="small-text" value="600" min="100" max="2000" />
							</td>
						</tr>

						<!-- Theme -->
						<tr>
							<th scope="row">
								<label for="bookit-sh-theme"><?php esc_html_e( 'Theme', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<select id="bookit-sh-theme" data-bookit-attr="theme">
									<option value="global"><?php esc_html_e( 'Global (use plugin setting)', 'bookit-for-cal-com' ); ?></option>
									<option value="auto"><?php esc_html_e( 'Auto (browser preference)', 'bookit-for-cal-com' ); ?></option>
									<option value="light"><?php esc_html_e( 'Light', 'bookit-for-cal-com' ); ?></option>
									<option value="dark"><?php esc_html_e( 'Dark', 'bookit-for-cal-com' ); ?></option>
								</select>
							</td>
						</tr>

						<!-- Accent color -->
						<tr>
							<th scope="row">
								<label for="bookit-sh-accent-picker"><?php esc_html_e( 'Accent Color', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-accent" data-bookit-attr="accent" value="" />
								<input type="color" id="bookit-sh-accent-picker" value="#000000" />
								<button type="button" id="bookit-sh-accent-clear" class="button button-small">
									<?php esc_html_e( 'Clear', 'bookit-for-cal-com' ); ?>
								</button>
								<p class="description">
									<?php esc_html_e( 'Leave unset to use the global accent color from Settings.', 'bookit-for-cal-com' ); ?>
								</p>
							</td>
						</tr>

						<!-- Hide details -->
						<tr>
							<th scope="row"><?php esc_html_e( 'Hide Details', 'bookit-for-cal-com' ); ?></th>
							<td>
								<label>
									<input type="checkbox" id="bookit-sh-hide-details" data-bookit-attr="hide_details" />
									<?php esc_html_e( 'Hide the event details panel in the booking modal', 'bookit-for-cal-com' ); ?>
								</label>
							</td>
						</tr>

						<!-- Prefill -->
						<tr>
							<th scope="row"><?php esc_html_e( 'Prefill User Data', 'bookit-for-cal-com' ); ?></th>
							<td>
								<label>
									<input type="checkbox" id="bookit-sh-prefill" data-bookit-attr="prefill" />
									<?php esc_html_e( 'Pre-fill the logged-in user\'s name and email', 'bookit-for-cal-com' ); ?>
								</label>
							</td>
						</tr>

						<!-- ── Button Styles (popup-button only) ───────────────── -->

						<tr data-bookit-show="popup-button">
							<td colspan="2" class="bookit-sh-section-heading">
								<h3><?php esc_html_e( 'Button Styles', 'bookit-for-cal-com' ); ?></h3>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-bg-picker"><?php esc_html_e( 'Background Color', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-bg" data-bookit-attr="btn_bg" value="" />
								<input type="color" id="bookit-sh-btn-bg-picker" value="#000000" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-bg">
									<?php esc_html_e( 'Clear', 'bookit-for-cal-com' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-text-picker"><?php esc_html_e( 'Text Color', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-text" data-bookit-attr="btn_text" value="" />
								<input type="color" id="bookit-sh-btn-text-picker" value="#ffffff" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-text">
									<?php esc_html_e( 'Clear', 'bookit-for-cal-com' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-radius"><?php esc_html_e( 'Border Radius (px)', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-btn-radius" data-bookit-attr="btn_radius"
									class="small-text" value="4" min="0" max="50" />
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-border-width"><?php esc_html_e( 'Border Width (px)', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-btn-border-width" data-bookit-attr="btn_border_width"
									class="small-text" value="0" min="0" max="20" />
							</td>
						</tr>

						<tr data-bookit-show="popup-button" data-bookit-border-conditional class="bookit-sh-hidden">
							<th scope="row">
								<label for="bookit-sh-btn-border-style"><?php esc_html_e( 'Border Style', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<select id="bookit-sh-btn-border-style" data-bookit-attr="btn_border_style">
									<option value="solid"><?php esc_html_e( 'Solid', 'bookit-for-cal-com' ); ?></option>
									<option value="dashed"><?php esc_html_e( 'Dashed', 'bookit-for-cal-com' ); ?></option>
									<option value="dotted"><?php esc_html_e( 'Dotted', 'bookit-for-cal-com' ); ?></option>
								</select>
							</td>
						</tr>

						<tr data-bookit-show="popup-button" data-bookit-border-conditional class="bookit-sh-hidden">
							<th scope="row">
								<label for="bookit-sh-btn-border-color-picker"><?php esc_html_e( 'Border Color', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-border-color" data-bookit-attr="btn_border_color" value="" />
								<input type="color" id="bookit-sh-btn-border-color-picker" value="#000000" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-border-color">
									<?php esc_html_e( 'Clear', 'bookit-for-cal-com' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row"><?php esc_html_e( 'Padding (px)', 'bookit-for-cal-com' ); ?></th>
							<td class="bookit-sh-padding-row">
								<label><?php esc_html_e( 'Top', 'bookit-for-cal-com' ); ?>
									<input type="number" data-bookit-attr="btn_padding_top" class="small-text" value="10" min="0" />
								</label>
								<label><?php esc_html_e( 'Right', 'bookit-for-cal-com' ); ?>
									<input type="number" data-bookit-attr="btn_padding_right" class="small-text" value="20" min="0" />
								</label>
								<label><?php esc_html_e( 'Bottom', 'bookit-for-cal-com' ); ?>
									<input type="number" data-bookit-attr="btn_padding_bottom" class="small-text" value="10" min="0" />
								</label>
								<label><?php esc_html_e( 'Left', 'bookit-for-cal-com' ); ?>
									<input type="number" data-bookit-attr="btn_padding_left" class="small-text" value="20" min="0" />
								</label>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-full-width"><?php esc_html_e( 'Full Width', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<label>
									<input type="checkbox" id="bookit-sh-btn-full-width" data-bookit-attr="btn_full_width" />
									<?php esc_html_e( 'Stretch button to full container width', 'bookit-for-cal-com' ); ?>
								</label>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-font-size"><?php esc_html_e( 'Font Size (px)', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-btn-font-size" data-bookit-attr="btn_font_size"
									class="small-text" value="14" min="10" max="36" />
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-font-weight"><?php esc_html_e( 'Font Weight', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<select id="bookit-sh-btn-font-weight" data-bookit-attr="btn_font_weight">
									<option value=""><?php esc_html_e( 'Default', 'bookit-for-cal-com' ); ?></option>
									<option value="300"><?php esc_html_e( '300 — Light', 'bookit-for-cal-com' ); ?></option>
									<option value="400"><?php esc_html_e( '400 — Normal', 'bookit-for-cal-com' ); ?></option>
									<option value="500"><?php esc_html_e( '500 — Medium', 'bookit-for-cal-com' ); ?></option>
									<option value="600"><?php esc_html_e( '600 — Semi Bold', 'bookit-for-cal-com' ); ?></option>
									<option value="700"><?php esc_html_e( '700 — Bold', 'bookit-for-cal-com' ); ?></option>
									<option value="800"><?php esc_html_e( '800 — Extra Bold', 'bookit-for-cal-com' ); ?></option>
								</select>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-text-transform"><?php esc_html_e( 'Text Transform', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<select id="bookit-sh-btn-text-transform" data-bookit-attr="btn_text_transform">
									<option value=""><?php esc_html_e( 'None', 'bookit-for-cal-com' ); ?></option>
									<option value="uppercase"><?php esc_html_e( 'Uppercase', 'bookit-for-cal-com' ); ?></option>
									<option value="lowercase"><?php esc_html_e( 'Lowercase', 'bookit-for-cal-com' ); ?></option>
									<option value="capitalize"><?php esc_html_e( 'Capitalize', 'bookit-for-cal-com' ); ?></option>
								</select>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-letter-spacing"><?php esc_html_e( 'Letter Spacing (px)', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="number" id="bookit-sh-btn-letter-spacing" data-bookit-attr="btn_letter_spacing"
									class="small-text" value="0" min="0" max="10" step="0.5" />
							</td>
						</tr>

						<!-- Hover effects -->
						<tr data-bookit-show="popup-button">
							<td colspan="2" class="bookit-sh-section-heading">
								<h3><?php esc_html_e( 'Hover Effects', 'bookit-for-cal-com' ); ?></h3>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-hover-bg-picker"><?php esc_html_e( 'Hover Background', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-hover-bg" data-bookit-attr="btn_hover_bg" value="" />
								<input type="color" id="bookit-sh-btn-hover-bg-picker" value="#000000" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-hover-bg">
									<?php esc_html_e( 'Clear', 'bookit-for-cal-com' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-hover-text-picker"><?php esc_html_e( 'Hover Text Color', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-hover-text" data-bookit-attr="btn_hover_text" value="" />
								<input type="color" id="bookit-sh-btn-hover-text-picker" value="#ffffff" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-hover-text">
									<?php esc_html_e( 'Clear', 'bookit-for-cal-com' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button" data-bookit-border-conditional class="bookit-sh-hidden">
							<th scope="row">
								<label for="bookit-sh-btn-hover-border-picker"><?php esc_html_e( 'Hover Border Color', 'bookit-for-cal-com' ); ?></label>
							</th>
							<td>
								<input type="hidden" id="bookit-sh-btn-hover-border-color" data-bookit-attr="btn_hover_border_color" value="" />
								<input type="color" id="bookit-sh-btn-hover-border-picker" value="#000000" />
								<button type="button" class="button button-small bookit-sh-color-clear" data-target="bookit-sh-btn-hover-border-color">
									<?php esc_html_e( 'Clear', 'bookit-for-cal-com' ); ?>
								</button>
							</td>
						</tr>

						<tr data-bookit-show="popup-button">
							<th scope="row">
								<label for="bookit-sh-btn-transition"><?php esc_html_e( 'Transition Duration (ms)', 'bookit-for-cal-com' ); ?></label>
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
					<summary><?php esc_html_e( 'Shortcode attribute reference', 'bookit-for-cal-com' ); ?></summary>
					<table>
						<thead>
							<tr>
								<th><?php esc_html_e( 'Attribute', 'bookit-for-cal-com' ); ?></th>
								<th><?php esc_html_e( 'Accepted values', 'bookit-for-cal-com' ); ?></th>
								<th><?php esc_html_e( 'Default', 'bookit-for-cal-com' ); ?></th>
								<th><?php esc_html_e( 'Description', 'bookit-for-cal-com' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr><td><code>event</code></td><td><?php esc_html_e( 'username/slug', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( '(required)', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( 'Cal.com event type identifier.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>type</code></td><td><code>popup-button</code> | <code>popup-text</code> | <code>inline</code></td><td><code>popup-button</code></td><td><?php esc_html_e( 'Widget display mode.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>label</code></td><td><?php esc_html_e( 'text', 'bookit-for-cal-com' ); ?></td><td><code>Book a meeting</code></td><td><?php esc_html_e( 'Button or link text (popup types only).', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>height</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-cal-com' ); ?></td><td><code>600</code></td><td><?php esc_html_e( 'Iframe height in pixels (inline type only).', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>theme</code></td><td><code>global</code> | <code>auto</code> | <code>light</code> | <code>dark</code></td><td><code>global</code></td><td><?php esc_html_e( 'Cal.com UI theme.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>accent</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( '(global setting)', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( 'Accent color override.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>hide_details</code></td><td><code>0</code> | <code>1</code></td><td><code>0</code></td><td><?php esc_html_e( 'Hide the booking details panel.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>prefill</code></td><td><code>0</code> | <code>1</code></td><td><code>0</code></td><td><?php esc_html_e( 'Pre-fill logged-in user name and email.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_bg</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( 'Button background color.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_text</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( 'Button text color.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_radius</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-cal-com' ); ?></td><td><code>4</code></td><td><?php esc_html_e( 'Button border radius.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_border_width</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-cal-com' ); ?></td><td><code>0</code></td><td><?php esc_html_e( 'Button border width.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_border_style</code></td><td><code>solid</code> | <code>dashed</code> | <code>dotted</code></td><td><code>solid</code></td><td><?php esc_html_e( 'Button border style (requires border width &gt; 0).', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_border_color</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( 'Button border color (requires border width &gt; 0).', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_padding_top</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-cal-com' ); ?></td><td><code>10</code></td><td><?php esc_html_e( 'Button top padding.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_padding_right</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-cal-com' ); ?></td><td><code>20</code></td><td><?php esc_html_e( 'Button right padding.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_padding_bottom</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-cal-com' ); ?></td><td><code>10</code></td><td><?php esc_html_e( 'Button bottom padding.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_padding_left</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-cal-com' ); ?></td><td><code>20</code></td><td><?php esc_html_e( 'Button left padding.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_font_size</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-cal-com' ); ?></td><td><code>14</code></td><td><?php esc_html_e( 'Button font size.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_font_weight</code></td><td><code>300</code> | <code>400</code> | <code>500</code> | <code>600</code> | <code>700</code> | <code>800</code></td><td><?php esc_html_e( '(inherit)', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( 'Button font weight.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_text_transform</code></td><td><code>uppercase</code> | <code>lowercase</code> | <code>capitalize</code></td><td><?php esc_html_e( '(none)', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( 'Button text transform.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_letter_spacing</code></td><td><?php esc_html_e( 'number (px)', 'bookit-for-cal-com' ); ?></td><td><code>0</code></td><td><?php esc_html_e( 'Button letter spacing.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_full_width</code></td><td><code>0</code> | <code>1</code></td><td><code>0</code></td><td><?php esc_html_e( 'Stretch button to full width.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_hover_bg</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( 'Button hover background color.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_hover_text</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( 'Button hover text color.', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_hover_border_color</code></td><td><?php esc_html_e( 'hex color', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( '(none)', 'bookit-for-cal-com' ); ?></td><td><?php esc_html_e( 'Button hover border color (requires border width &gt; 0).', 'bookit-for-cal-com' ); ?></td></tr>
							<tr><td><code>btn_transition_duration</code></td><td><?php esc_html_e( 'number (ms)', 'bookit-for-cal-com' ); ?></td><td><code>200</code></td><td><?php esc_html_e( 'Hover transition duration in milliseconds.', 'bookit-for-cal-com' ); ?></td></tr>
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
				'msgSuccess'       => __( 'Event types refreshed successfully.', 'bookit-for-cal-com' ),
				'msgError'         => __( 'Could not refresh event types.', 'bookit-for-cal-com' ),
				'msgUsernameAuto'   => __( 'Auto-detected from your API key. Click "Refresh event types" to update.', 'bookit-for-cal-com' ),
				'msgUsernameManual' => __( 'Your Cal.com username (used as URL prefix). Required when no API key is set.', 'bookit-for-cal-com' ),
				'eventTypes'        => self::get_localized_event_types(),
				'msgCopied'         => __( 'Copied!', 'bookit-for-cal-com' ),
				'msgCopyFailed'     => __( 'Copy failed.', 'bookit-for-cal-com' ),
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
			wp_send_json_error( __( 'Insufficient permissions.', 'bookit-for-cal-com' ), 403 );
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

	// -------------------------------------------------------------------------
	// Style tab — render.
	// -------------------------------------------------------------------------

	/**
	 * Render the Style tab (button / widget global style defaults).
	 *
	 * @return void
	 */
	private static function render_tab_style(): void {
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'bookit_settings_group' ); ?>
			<input type="hidden" name="bookit_settings[_tab]" value="style" />
			<?php
			do_settings_sections( 'bookit-settings-style' );
			submit_button( esc_html__( 'Save Style Defaults', 'bookit-for-cal-com' ) );
			?>
		</form>
		<?php
	}

	// -------------------------------------------------------------------------
	// Style tab — field renderers.
	// -------------------------------------------------------------------------

	/**
	 * Helper: render a colour control (text input + native colour picker + clear button).
	 *
	 * @param string $id      Field id / name key inside bookit_settings[].
	 * @param string $value   Current stored value (hex or empty string).
	 * @return void
	 */
	private static function render_color_control( string $id, string $value ): void {
		$picker_value = $value ?: '#000000';
		?>
		<span class="bookit-color-control">
			<input
				type="text"
				id="<?php echo esc_attr( $id ); ?>"
				name="bookit_settings[<?php echo esc_attr( $id ); ?>]"
				value="<?php echo esc_attr( $value ); ?>"
				class="bookit-color-text small-text"
				placeholder="#rrggbb"
				maxlength="7"
			/>
			<input
				type="color"
				class="bookit-color-picker"
				value="<?php echo esc_attr( $picker_value ); ?>"
				aria-hidden="true"
				tabindex="-1"
			/>
			<button type="button" class="button button-small bookit-color-clear">
				<?php esc_html_e( 'Clear', 'bookit-for-cal-com' ); ?>
			</button>
		</span>
		<?php
	}

	/**
	 * Helper: render a number input with a unit label.
	 *
	 * @param string     $id          Field id / name key inside bookit_settings[].
	 * @param string|int $value       Current stored value (number or empty string).
	 * @param string     $unit        Unit label to display after the input (e.g. 'px').
	 * @param int        $min         Minimum value.
	 * @param int        $max         Maximum value.
	 * @param string     $placeholder Placeholder shown when value is empty.
	 * @param float      $step        Step increment (default 1).
	 * @return void
	 */
	private static function render_number_unit( string $id, $value, string $unit, int $min = 0, int $max = 9999, string $placeholder = '', float $step = 1 ): void {
		?>
		<span class="bookit-number-unit">
			<input
				type="number"
				id="<?php echo esc_attr( $id ); ?>"
				name="bookit_settings[<?php echo esc_attr( $id ); ?>]"
				value="<?php echo esc_attr( (string) $value ); ?>"
				class="small-text"
				min="<?php echo esc_attr( (string) $min ); ?>"
				max="<?php echo esc_attr( (string) $max ); ?>"
				step="<?php echo esc_attr( (string) $step ); ?>"
				<?php if ( '' !== $placeholder ) : ?>
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
				<?php endif; ?>
			/>
			<span class="bookit-unit"><?php echo esc_html( $unit ); ?></span>
		</span>
		<?php
	}

	/**
	 * Render the Default Label field.
	 *
	 * @return void
	 */
	public static function field_default_label(): void {
		$settings = self::get_settings();
		?>
		<input
			type="text"
			id="bookit_default_label"
			name="bookit_settings[default_label]"
			value="<?php echo esc_attr( $settings['default_label'] ); ?>"
			class="regular-text"
			placeholder="<?php esc_attr_e( 'Book a meeting', 'bookit-for-cal-com' ); ?>"
		/>
		<p class="description">
			<?php esc_html_e( 'Default button / link label. Leave empty to use "Book a meeting".', 'bookit-for-cal-com' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the Button Background Color field.
	 *
	 * @return void
	 */
	public static function field_btn_bg(): void {
		$settings = self::get_settings();
		self::render_color_control( 'btn_bg', $settings['btn_bg'] );
		echo '<p class="description">' . esc_html__( 'Leave empty to inherit theme default.', 'bookit-for-cal-com' ) . '</p>';
	}

	/**
	 * Render the Button Text Color field.
	 *
	 * @return void
	 */
	public static function field_btn_text(): void {
		$settings = self::get_settings();
		self::render_color_control( 'btn_text', $settings['btn_text'] );
		echo '<p class="description">' . esc_html__( 'Leave empty to inherit theme default.', 'bookit-for-cal-com' ) . '</p>';
	}

	/**
	 * Render the Button Full Width checkbox.
	 *
	 * @return void
	 */
	public static function field_btn_full_width(): void {
		$settings = self::get_settings();
		?>
		<label>
			<input
				type="checkbox"
				id="bookit_btn_full_width"
				name="bookit_settings[btn_full_width]"
				value="1"
				<?php checked( $settings['btn_full_width'] ); ?>
			/>
			<?php esc_html_e( 'Stretch button to full container width by default.', 'bookit-for-cal-com' ); ?>
		</label>
		<?php
	}

	/**
	 * Render the Button Border Width field.
	 *
	 * @return void
	 */
	public static function field_btn_border_width(): void {
		$settings = self::get_settings();
		self::render_number_unit( 'btn_border_width', $settings['btn_border_width'], 'px', 0, 20, '0' );
	}

	/**
	 * Render the Button Border Style select.
	 *
	 * @return void
	 */
	public static function field_btn_border_style(): void {
		$settings = self::get_settings();
		$options  = array(
			'solid'  => __( 'Solid', 'bookit-for-cal-com' ),
			'dashed' => __( 'Dashed', 'bookit-for-cal-com' ),
			'dotted' => __( 'Dotted', 'bookit-for-cal-com' ),
		);
		?>
		<select id="bookit_btn_border_style" name="bookit_settings[btn_border_style]">
			<?php foreach ( $options as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['btn_border_style'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render the Button Border Color field.
	 *
	 * @return void
	 */
	public static function field_btn_border_color(): void {
		$settings = self::get_settings();
		self::render_color_control( 'btn_border_color', $settings['btn_border_color'] );
	}

	/**
	 * Render the Button Border Radius field.
	 *
	 * @return void
	 */
	public static function field_btn_radius(): void {
		$settings = self::get_settings();
		self::render_number_unit( 'btn_radius', $settings['btn_radius'], 'px', 0, 100, '4' );
	}

	/**
	 * Render the Button Font Size field.
	 *
	 * @return void
	 */
	public static function field_btn_font_size(): void {
		$settings = self::get_settings();
		self::render_number_unit( 'btn_font_size', $settings['btn_font_size'], 'px', 10, 36, '14' );
	}

	/**
	 * Render the Button Font Weight select.
	 *
	 * @return void
	 */
	public static function field_btn_font_weight(): void {
		$settings = self::get_settings();
		$options  = array(
			''    => __( 'Default (inherit)', 'bookit-for-cal-com' ),
			'300' => __( '300 — Light', 'bookit-for-cal-com' ),
			'400' => __( '400 — Normal', 'bookit-for-cal-com' ),
			'500' => __( '500 — Medium', 'bookit-for-cal-com' ),
			'600' => __( '600 — Semi Bold', 'bookit-for-cal-com' ),
			'700' => __( '700 — Bold', 'bookit-for-cal-com' ),
			'800' => __( '800 — Extra Bold', 'bookit-for-cal-com' ),
		);
		?>
		<select id="bookit_btn_font_weight" name="bookit_settings[btn_font_weight]">
			<?php foreach ( $options as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['btn_font_weight'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render the Button Text Transform select.
	 *
	 * @return void
	 */
	public static function field_btn_text_transform(): void {
		$settings = self::get_settings();
		$options  = array(
			''           => __( 'None', 'bookit-for-cal-com' ),
			'uppercase'  => __( 'Uppercase', 'bookit-for-cal-com' ),
			'lowercase'  => __( 'Lowercase', 'bookit-for-cal-com' ),
			'capitalize' => __( 'Capitalize', 'bookit-for-cal-com' ),
		);
		?>
		<select id="bookit_btn_text_transform" name="bookit_settings[btn_text_transform]">
			<?php foreach ( $options as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['btn_text_transform'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render the Button Letter Spacing field.
	 *
	 * @return void
	 */
	public static function field_btn_letter_spacing(): void {
		$settings = self::get_settings();
		self::render_number_unit( 'btn_letter_spacing', $settings['btn_letter_spacing'], 'px', 0, 10, '0', 0.5 );
	}

	/**
	 * Render the Button Padding fields (all 4 sides in one row).
	 *
	 * @return void
	 */
	public static function field_btn_padding(): void {
		$settings = self::get_settings();
		$sides    = array(
			'btn_padding_top'    => __( 'Top', 'bookit-for-cal-com' ),
			'btn_padding_right'  => __( 'Right', 'bookit-for-cal-com' ),
			'btn_padding_bottom' => __( 'Bottom', 'bookit-for-cal-com' ),
			'btn_padding_left'   => __( 'Left', 'bookit-for-cal-com' ),
		);
		$placeholders = array( 'btn_padding_top' => '10', 'btn_padding_right' => '20', 'btn_padding_bottom' => '10', 'btn_padding_left' => '20' );
		?>
		<div class="bookit-padding-row">
			<?php foreach ( $sides as $key => $label ) : ?>
			<label for="bookit_<?php echo esc_attr( $key ); ?>">
				<?php echo esc_html( $label ); ?>
				<input
					type="number"
					id="bookit_<?php echo esc_attr( $key ); ?>"
					name="bookit_settings[<?php echo esc_attr( $key ); ?>]"
					value="<?php echo esc_attr( (string) $settings[ $key ] ); ?>"
					class="small-text"
					min="0"
					placeholder="<?php echo esc_attr( $placeholders[ $key ] ); ?>"
				/>
				<span class="bookit-unit">px</span>
			</label>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render the Button Hover Background Color field.
	 *
	 * @return void
	 */
	public static function field_btn_hover_bg(): void {
		$settings = self::get_settings();
		self::render_color_control( 'btn_hover_bg', $settings['btn_hover_bg'] );
		echo '<p class="description">' . esc_html__( 'Leave empty to disable hover background change.', 'bookit-for-cal-com' ) . '</p>';
	}

	/**
	 * Render the Button Hover Text Color field.
	 *
	 * @return void
	 */
	public static function field_btn_hover_text(): void {
		$settings = self::get_settings();
		self::render_color_control( 'btn_hover_text', $settings['btn_hover_text'] );
		echo '<p class="description">' . esc_html__( 'Leave empty to disable hover text colour change.', 'bookit-for-cal-com' ) . '</p>';
	}

	/**
	 * Render the Button Hover Border Color field.
	 *
	 * @return void
	 */
	public static function field_btn_hover_border_color(): void {
		$settings = self::get_settings();
		self::render_color_control( 'btn_hover_border_color', $settings['btn_hover_border_color'] );
		echo '<p class="description">' . esc_html__( 'Leave empty to disable hover border colour change.', 'bookit-for-cal-com' ) . '</p>';
	}

	/**
	 * Render the Button Transition Duration field.
	 *
	 * @return void
	 */
	public static function field_btn_transition_duration(): void {
		$settings = self::get_settings();
		self::render_number_unit( 'btn_transition_duration', $settings['btn_transition_duration'], 'ms', 0, 1000, '200', 50 );
		echo '<p class="description">' . esc_html__( 'Duration of the hover transition. Set 0 to disable.', 'bookit-for-cal-com' ) . '</p>';
	}

	/**
	 * Render the Inline Calendar Default Height field.
	 *
	 * @return void
	 */
	public static function field_inline_height(): void {
		$settings = self::get_settings();
		self::render_number_unit( 'inline_height', $settings['inline_height'], 'px', 100, 2000, '600' );
		echo '<p class="description">' . esc_html__( 'Default iframe height for inline embeds.', 'bookit-for-cal-com' ) . '</p>';
	}
}
