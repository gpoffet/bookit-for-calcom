<?php
/**
 * Elementor widget for BookIt for Cal.com.
 *
 * @package BookIt_For_CalCom
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BookIt_Elementor_Widget
 *
 * Integrates BookIt as an Elementor widget.
 * Compatible with Elementor Free and Pro — Pro-only features are
 * wrapped in capability checks and will never cause a fatal error
 * if only the Free version is installed.
 *
 * @since 1.0.0
 */
class BookIt_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Widget name (slug).
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'bookit_cal_booking';
	}

	/**
	 * Widget display title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Cal.com Booking', 'bookit-for-cal-com' );
	}

	/**
	 * Widget icon (Elementor icon class).
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-calendar';
	}

	/**
	 * Widget category — custom "bookit" category.
	 *
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return array( 'bookit' );
	}

	/**
	 * Widget keywords for search.
	 *
	 * @return array<int, string>
	 */
	public function get_keywords(): array {
		return array( 'cal.com', 'booking', 'calendar', 'appointment', 'bookit' );
	}

	/**
	 * Register widget controls (mirrors block attributes exactly).
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$settings    = BookIt_Admin::get_settings();
		$has_api_key = ! empty( $settings['api_key'] );
		$event_types = array();

		if ( $has_api_key ) {
			$result = BookIt_API::get_event_types( $settings['api_key'] );
			if ( ! is_wp_error( $result ) ) {
				$event_types = $result;
			}
		}

		// ── Content Tab ──────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_event',
			array(
				'label' => esc_html__( 'Event', 'bookit-for-cal-com' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		if ( $has_api_key && ! empty( $event_types ) ) {
			$options = array( '' => esc_html__( '— Select an event —', 'bookit-for-cal-com' ) );
			foreach ( $event_types as $et ) {
				// The Cal.com v2 API nests the username under owner/profile/user — not at root level.
				$username = $et['owner']['username'] ?? $et['profile']['username'] ?? $et['user']['username'] ?? '';
				$slug     = ! empty( $username ) ? $username . '/' . $et['slug'] : $et['slug'];
				$label    = $et['title'] . ' — ' . $et['slug'];
				$options[ $slug ] = $label;
			}

			$this->add_control(
				'event_type',
				array(
					'label'   => esc_html__( 'Event type', 'bookit-for-cal-com' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => $options,
					'default' => '',
				)
			);
		} else {
			$this->add_control(
				'event_type',
				array(
					'label'       => esc_html__( 'Event slug', 'bookit-for-cal-com' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'placeholder' => 'username/event-slug',
					'description' => esc_html__( 'Configure an API key in BookIt settings to use a dropdown.', 'bookit-for-cal-com' ),
					'default'     => '',
				)
			);
		}

		$this->add_control(
			'display_type',
			array(
				'label'   => esc_html__( 'Display type', 'bookit-for-cal-com' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'popup-button' => esc_html__( 'Popup button', 'bookit-for-cal-com' ),
					'popup-text'   => esc_html__( 'Popup text link', 'bookit-for-cal-com' ),
					'inline'       => esc_html__( 'Inline calendar', 'bookit-for-cal-com' ),
				),
				'default' => 'popup-button',
			)
		);

		$this->add_control(
			'label',
			array(
				'label'     => esc_html__( 'Button / link label', 'bookit-for-cal-com' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Book a meeting', 'bookit-for-cal-com' ),
				'condition' => array( 'display_type!' => 'inline' ),
			)
		);

		$this->add_control(
			'inline_height',
			array(
				'label'     => esc_html__( 'Inline height (px)', 'bookit-for-cal-com' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 300,
				'max'       => 1200,
				'step'      => 50,
				'default'   => 600,
				'condition' => array( 'display_type' => 'inline' ),
			)
		);

		$this->end_controls_section();

		// ── Cal.com Options ───────────────────────────────────────────────────

		$this->start_controls_section(
			'section_calcom',
			array(
				'label' => esc_html__( 'Cal.com options', 'bookit-for-cal-com' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'theme',
			array(
				'label'   => esc_html__( 'Theme', 'bookit-for-cal-com' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'global' => esc_html__( 'Use global setting', 'bookit-for-cal-com' ),
					'auto'   => esc_html__( 'Auto (follow browser)', 'bookit-for-cal-com' ),
					'light'  => esc_html__( 'Light', 'bookit-for-cal-com' ),
					'dark'   => esc_html__( 'Dark', 'bookit-for-cal-com' ),
				),
				'default' => 'global',
			)
		);

		$this->add_control(
			'accent_color',
			array(
				'label'   => esc_html__( 'Accent color', 'bookit-for-cal-com' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'hide_details',
			array(
				'label'        => esc_html__( 'Hide booking details', 'bookit-for-cal-com' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'bookit-for-cal-com' ),
				'label_off'    => esc_html__( 'No', 'bookit-for-cal-com' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'prefill_user',
			array(
				'label'        => esc_html__( 'Pre-fill logged-in user data', 'bookit-for-cal-com' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'bookit-for-cal-com' ),
				'label_off'    => esc_html__( 'No', 'bookit-for-cal-com' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->end_controls_section();

		// ── Style Tab ─────────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_button_style',
			array(
				'label'     => esc_html__( 'Button style', 'bookit-for-cal-com' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'display_type' => 'popup-button' ),
			)
		);

		$this->add_control(
			'btn_bg_color',
			array(
				'label'   => esc_html__( 'Background color', 'bookit-for-cal-com' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'btn_text_color',
			array(
				'label'   => esc_html__( 'Text color', 'bookit-for-cal-com' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'btn_border_radius',
			array(
				'label'   => esc_html__( 'Border radius (px)', 'bookit-for-cal-com' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 50,
				'default' => 4,
			)
		);

		$this->add_control(
			'btn_hover_heading',
			array(
				'label'     => esc_html__( 'Hover state', 'bookit-for-cal-com' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'btn_hover_bg_color',
			array(
				'label'   => esc_html__( 'Hover background color', 'bookit-for-cal-com' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'btn_hover_text_color',
			array(
				'label'   => esc_html__( 'Hover text color', 'bookit-for-cal-com' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'btn_transition_duration',
			array(
				'label'   => esc_html__( 'Hover transition (ms)', 'bookit-for-cal-com' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 1000,
				'step'    => 50,
				'default' => 200,
			)
		);

		// Typography (Elementor Pro only — safely wrapped).
		if ( class_exists( '\Elementor\Group_Control_Typography' ) ) {
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'btn_typography',
					'selector' => '{{WRAPPER}} .bookit-btn',
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Render the widget on the frontend.
	 *
	 * @return void
	 */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		$settings = BookIt_Admin::get_settings();

		$event_type = sanitize_text_field( $s['event_type'] ?? '' );
		if ( empty( $event_type ) ) {
			echo '<p>' . esc_html__( 'Please configure a Cal.com event in the widget settings.', 'bookit-for-cal-com' ) . '</p>';
			return;
		}

		// If the stored slug has no username prefix, resolve it from settings or the API.
		if ( false === strpos( $event_type, '/' ) ) {
			$username = ! empty( $settings['username'] )
				? $settings['username']
				: BookIt_API::get_username( $settings['api_key'], $settings['api_base'] ?? '' );
			if ( ! empty( $username ) ) {
				$event_type = $username . '/' . $event_type;
			}
		}

		$display_type  = sanitize_text_field( $s['display_type']    ?? 'popup-button' );
		$label         = sanitize_text_field( $s['label']           ?? __( 'Book a meeting', 'bookit-for-cal-com' ) );
		$inline_height = absint( $s['inline_height'] ?? 600 ) ?: 600;
		$theme         = sanitize_text_field( $s['theme']           ?? 'global' );
		$accent_color  = sanitize_hex_color( $s['accent_color']    ?? '' ) ?? '';
		$hide_details  = 'yes' === ( $s['hide_details'] ?? '' ) ? '1' : '0';
		$prefill_user  = 'yes' === ( $s['prefill_user']  ?? '' ) ? '1' : '0';
		$btn_bg              = sanitize_hex_color( $s['btn_bg_color']       ?? '' ) ?? '';
		$btn_text            = sanitize_hex_color( $s['btn_text_color']     ?? '' ) ?? '';
		$btn_radius          = absint( $s['btn_border_radius']              ?? 4 );
		$btn_hover_bg        = sanitize_hex_color( $s['btn_hover_bg_color']   ?? '' ) ?? '';
		$btn_hover_text      = sanitize_hex_color( $s['btn_hover_text_color'] ?? '' ) ?? '';
		$btn_transition      = absint( $s['btn_transition_duration']          ?? 200 );
		$ns                  = sanitize_key( $settings['namespace'] ?: 'cal' );

		if ( 'global' === $theme ) {
			$theme = $settings['theme'] ?? 'auto';
		}
		if ( empty( $accent_color ) && ! empty( $settings['accent_color'] ) ) {
			$accent_color = $settings['accent_color'];
		}

		// Fall back to global hover colors when not overridden in widget.
		if ( empty( $btn_hover_bg ) && ! empty( $settings['btn_hover_bg'] ) ) {
			$btn_hover_bg = $settings['btn_hover_bg'];
		}
		if ( empty( $btn_hover_text ) && ! empty( $settings['btn_hover_text'] ) ) {
			$btn_hover_text = $settings['btn_hover_text'];
		}

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- build_html() escapes all output internally; every value passed here is sanitized above (sanitize_text_field, sanitize_hex_color, absint, sanitize_key).
		echo BookIt_Shortcode::build_html( array(
			'event'                   => $event_type,
			'type'                    => $display_type,
			'label'                   => $label,
			'height'                  => $inline_height,
			'theme'                   => $theme,
			'accent'                  => $accent_color,
			'hide_details'            => $hide_details,
			'prefill'                 => $prefill_user,
			'btn_bg'                  => $btn_bg,
			'btn_text'                => $btn_text,
			'btn_radius'              => $btn_radius,
			'btn_hover_bg'            => $btn_hover_bg,
			'btn_hover_text'          => $btn_hover_text,
			'btn_transition_duration' => $btn_transition,
			'ns'                      => $ns,
		) );
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		// Mark this post so BookIt_Assets knows to enqueue scripts.
		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$post_id = get_the_ID();
			if ( $post_id ) {
				update_post_meta( $post_id, '_bookit_elementor_widget', '1' );
			}
		}
	}

	/**
	 * Render a placeholder in the editor.
	 *
	 * @return void
	 */
	public function render_plain_content(): void {
		// Dynamic content — no plain-text version.
	}
}
