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
		return esc_html__( 'Cal.com Booking', 'bookit-for-calcom' );
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
				'label' => esc_html__( 'Event', 'bookit-for-calcom' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		if ( $has_api_key && ! empty( $event_types ) ) {
			$options = array( '' => esc_html__( '— Select an event —', 'bookit-for-calcom' ) );
			foreach ( $event_types as $et ) {
				$slug  = isset( $et['username'] ) ? $et['username'] . '/' . $et['slug'] : $et['slug'];
				$label = $et['title'] . ' — ' . $et['slug'];
				$options[ $slug ] = $label;
			}

			$this->add_control(
				'event_type',
				array(
					'label'   => esc_html__( 'Event type', 'bookit-for-calcom' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => $options,
					'default' => '',
				)
			);
		} else {
			$this->add_control(
				'event_type',
				array(
					'label'       => esc_html__( 'Event slug', 'bookit-for-calcom' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'placeholder' => 'username/event-slug',
					'description' => esc_html__( 'Configure an API key in BookIt settings to use a dropdown.', 'bookit-for-calcom' ),
					'default'     => '',
				)
			);
		}

		$this->add_control(
			'display_type',
			array(
				'label'   => esc_html__( 'Display type', 'bookit-for-calcom' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'popup-button' => esc_html__( 'Popup button', 'bookit-for-calcom' ),
					'popup-text'   => esc_html__( 'Popup text link', 'bookit-for-calcom' ),
					'inline'       => esc_html__( 'Inline calendar', 'bookit-for-calcom' ),
				),
				'default' => 'popup-button',
			)
		);

		$this->add_control(
			'label',
			array(
				'label'     => esc_html__( 'Button / link label', 'bookit-for-calcom' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Book a meeting', 'bookit-for-calcom' ),
				'condition' => array( 'display_type!' => 'inline' ),
			)
		);

		$this->add_control(
			'inline_height',
			array(
				'label'     => esc_html__( 'Inline height (px)', 'bookit-for-calcom' ),
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
				'label' => esc_html__( 'Cal.com options', 'bookit-for-calcom' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'theme',
			array(
				'label'   => esc_html__( 'Theme', 'bookit-for-calcom' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'global' => esc_html__( 'Use global setting', 'bookit-for-calcom' ),
					'auto'   => esc_html__( 'Auto (follow browser)', 'bookit-for-calcom' ),
					'light'  => esc_html__( 'Light', 'bookit-for-calcom' ),
					'dark'   => esc_html__( 'Dark', 'bookit-for-calcom' ),
				),
				'default' => 'global',
			)
		);

		$this->add_control(
			'accent_color',
			array(
				'label'   => esc_html__( 'Accent color', 'bookit-for-calcom' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'hide_details',
			array(
				'label'        => esc_html__( 'Hide booking details', 'bookit-for-calcom' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'bookit-for-calcom' ),
				'label_off'    => esc_html__( 'No', 'bookit-for-calcom' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'prefill_user',
			array(
				'label'        => esc_html__( 'Pre-fill logged-in user data', 'bookit-for-calcom' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'bookit-for-calcom' ),
				'label_off'    => esc_html__( 'No', 'bookit-for-calcom' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->end_controls_section();

		// ── Style Tab ─────────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_button_style',
			array(
				'label'     => esc_html__( 'Button style', 'bookit-for-calcom' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'display_type' => 'popup-button' ),
			)
		);

		$this->add_control(
			'btn_bg_color',
			array(
				'label'   => esc_html__( 'Background color', 'bookit-for-calcom' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'btn_text_color',
			array(
				'label'   => esc_html__( 'Text color', 'bookit-for-calcom' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'btn_border_radius',
			array(
				'label'   => esc_html__( 'Border radius (px)', 'bookit-for-calcom' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 50,
				'default' => 4,
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
			echo '<p>' . esc_html__( 'Please configure a Cal.com event in the widget settings.', 'bookit-for-calcom' ) . '</p>';
			return;
		}

		$display_type  = sanitize_text_field( $s['display_type']    ?? 'popup-button' );
		$label         = sanitize_text_field( $s['label']           ?? __( 'Book a meeting', 'bookit-for-calcom' ) );
		$inline_height = absint( $s['inline_height'] ?? 600 ) ?: 600;
		$theme         = sanitize_text_field( $s['theme']           ?? 'global' );
		$accent_color  = sanitize_hex_color( $s['accent_color']    ?? '' ) ?? '';
		$hide_details  = 'yes' === ( $s['hide_details'] ?? '' ) ? '1' : '0';
		$prefill_user  = 'yes' === ( $s['prefill_user']  ?? '' ) ? '1' : '0';
		$btn_bg        = sanitize_hex_color( $s['btn_bg_color']     ?? '' ) ?? '';
		$btn_text      = sanitize_hex_color( $s['btn_text_color']   ?? '' ) ?? '';
		$btn_radius    = absint( $s['btn_border_radius'] ?? 4 );
		$ns            = sanitize_key( $settings['namespace'] ?: 'cal' );

		if ( 'global' === $theme ) {
			$theme = $settings['theme'] ?? 'auto';
		}
		if ( empty( $accent_color ) && ! empty( $settings['accent_color'] ) ) {
			$accent_color = $settings['accent_color'];
		}

		echo BookIt_Shortcode::build_html( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'event'        => $event_type,
			'type'         => $display_type,
			'label'        => $label,
			'height'       => $inline_height,
			'theme'        => $theme,
			'accent'       => $accent_color,
			'hide_details' => $hide_details,
			'prefill'      => $prefill_user,
			'btn_bg'       => $btn_bg,
			'btn_text'     => $btn_text,
			'btn_radius'   => $btn_radius,
			'ns'           => $ns,
		) );

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
