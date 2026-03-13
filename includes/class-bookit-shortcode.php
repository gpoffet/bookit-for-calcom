<?php
/**
 * [bookit] shortcode handler.
 *
 * @package BookIt_For_CalCom
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BookIt_Shortcode
 *
 * Registers and renders the [bookit] shortcode.
 *
 * Usage examples:
 *   [bookit event="username/slug" type="popup-button" label="Book now"]
 *   [bookit event="username/slug" type="inline" height="600"]
 *   [bookit event="username/slug" type="popup-text" label="Click here to book"]
 *
 * All attributes fall back to global plugin settings when omitted.
 *
 * @since 1.0.0
 */
class BookIt_Shortcode {

	/**
	 * Register the shortcode.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_shortcode( 'bookit', array( __CLASS__, 'render' ) );
	}

	/**
	 * Render the [bookit] shortcode.
	 *
	 * @param array<string, string>|string $atts    Shortcode attributes.
	 * @param string|null                  $content Enclosed content (unused).
	 * @return string HTML output.
	 */
	public static function render( $atts, ?string $content = null ): string {
		$settings = BookIt_Admin::get_settings();

		$atts = shortcode_atts(
			array(
				'event'             => '',
				'type'              => 'popup-button',
				'label'             => __( 'Book a meeting', 'bookit-for-calcom' ),
				'height'            => 600,
				'theme'             => 'global',
				'accent'            => '',
				'hide_details'      => '0',
				'prefill'           => '0',
				'btn_bg'            => '',
				'btn_text'          => '',
				'btn_radius'        => 4,
				'btn_border_width'  => 0,
				'btn_border_style'  => 'solid',
				'btn_border_color'  => '',
				'btn_padding_top'   => 10,
				'btn_padding_right' => 20,
				'btn_padding_bottom'=> 10,
				'btn_padding_left'  => 20,
				'btn_font_size'     => 14,
				'btn_font_weight'   => '',
				'btn_text_transform'=> '',
				'btn_letter_spacing'=> 0,
				'btn_full_width'         => '0',
				'btn_hover_bg'           => '',
				'btn_hover_text'         => '',
				'btn_hover_border_color' => '',
				'btn_transition_duration'=> 200,
			),
			$atts,
			'bookit'
		);

		// Sanitize all attributes.
		$event                   = sanitize_text_field( $atts['event'] );
		$type                    = self::sanitize_display_type( $atts['type'] );
		$label                   = sanitize_text_field( $atts['label'] );
		$height                  = absint( $atts['height'] ) ?: 600;
		$theme                   = self::sanitize_theme( $atts['theme'] );
		$accent                  = sanitize_hex_color( $atts['accent'] ) ?? '';
		$hide_details            = ! empty( $atts['hide_details'] ) && '0' !== $atts['hide_details'] ? '1' : '0';
		$prefill                 = ! empty( $atts['prefill'] ) && '0' !== $atts['prefill'] ? '1' : '0';
		$btn_bg                  = sanitize_hex_color( $atts['btn_bg'] ) ?? '';
		$btn_text                = sanitize_hex_color( $atts['btn_text'] ) ?? '';
		$btn_radius              = absint( $atts['btn_radius'] );
		$btn_border_width        = absint( $atts['btn_border_width'] );
		$btn_border_style        = sanitize_text_field( $atts['btn_border_style'] );
		$btn_border_color        = sanitize_hex_color( $atts['btn_border_color'] ) ?? '';
		$btn_padding_top         = absint( $atts['btn_padding_top'] );
		$btn_padding_right       = absint( $atts['btn_padding_right'] );
		$btn_padding_bottom      = absint( $atts['btn_padding_bottom'] );
		$btn_padding_left        = absint( $atts['btn_padding_left'] );
		$btn_font_size           = absint( $atts['btn_font_size'] ) ?: 14;
		$btn_font_weight         = sanitize_text_field( $atts['btn_font_weight'] );
		$btn_text_transform      = sanitize_text_field( $atts['btn_text_transform'] );
		$btn_letter_spacing      = (float) $atts['btn_letter_spacing'];
		$btn_full_width          = ! empty( $atts['btn_full_width'] ) && '0' !== $atts['btn_full_width'];
		$btn_hover_bg            = sanitize_hex_color( $atts['btn_hover_bg'] ) ?? '';
		$btn_hover_text          = sanitize_hex_color( $atts['btn_hover_text'] ) ?? '';
		$btn_hover_border_color  = sanitize_hex_color( $atts['btn_hover_border_color'] ) ?? '';
		$btn_transition_duration = absint( $atts['btn_transition_duration'] );
		$ns                      = sanitize_key( $settings['namespace'] ?: 'cal' );

		// If no event given, try to build it from global username.
		if ( empty( $event ) ) {
			return '<!-- BookIt: no event specified -->';
		}

		// If the event slug has no username prefix, resolve it from settings or the API.
		if ( false === strpos( $event, '/' ) ) {
			$username = ! empty( $settings['username'] )
				? $settings['username']
				: BookIt_API::get_username( $settings['api_key'], $settings['api_base'] );
			if ( ! empty( $username ) ) {
				$event = $username . '/' . $event;
			}
		}

		// Resolve "global" theme to the configured global theme.
		if ( 'global' === $theme ) {
			$theme = $settings['theme'] ?? 'auto';
		}

		// Fall back to global accent color.
		if ( empty( $accent ) && ! empty( $settings['accent_color'] ) ) {
			$accent = $settings['accent_color'];
		}

		return self::build_html( array(
			'event'             => $event,
			'type'              => $type,
			'label'             => $label,
			'height'            => $height,
			'theme'             => $theme,
			'accent'            => $accent,
			'hide_details'      => $hide_details,
			'prefill'           => $prefill,
			'btn_bg'            => $btn_bg,
			'btn_text'          => $btn_text,
			'btn_radius'        => $btn_radius,
			'btn_border_width'  => $btn_border_width,
			'btn_border_style'  => $btn_border_style,
			'btn_border_color'  => $btn_border_color,
			'btn_padding_top'   => $btn_padding_top,
			'btn_padding_right' => $btn_padding_right,
			'btn_padding_bottom'=> $btn_padding_bottom,
			'btn_padding_left'  => $btn_padding_left,
			'btn_font_size'     => $btn_font_size,
			'btn_font_weight'   => $btn_font_weight,
			'btn_text_transform'=> $btn_text_transform,
			'btn_letter_spacing'=> $btn_letter_spacing,
			'btn_full_width'          => $btn_full_width,
			'btn_hover_bg'            => $btn_hover_bg,
			'btn_hover_text'          => $btn_hover_text,
			'btn_hover_border_color'  => $btn_hover_border_color,
			'btn_transition_duration' => $btn_transition_duration,
			'ns'                      => $ns,
		) );
	}

	/**
	 * Build the HTML for a booking widget.
	 *
	 * This function is shared with render.php (Gutenberg block) via a static
	 * method so data-attribute structure stays in sync.
	 *
	 * @param array<string, mixed> $args Widget arguments.
	 * @return string HTML string.
	 */
	public static function build_html( array $args ): string {
		$type               = $args['type'];
		$event              = esc_attr( $args['event'] );
		$theme              = esc_attr( $args['theme'] );
		$accent             = esc_attr( $args['accent'] );
		$hide_details       = esc_attr( $args['hide_details'] );
		$prefill            = esc_attr( $args['prefill'] );
		$ns                 = esc_attr( $args['ns'] );
		$height             = absint( $args['height'] );
		$label              = esc_html( $args['label'] );
		$btn_bg             = esc_attr( $args['btn_bg']             ?? '' );
		$btn_text           = esc_attr( $args['btn_text']           ?? '' );
		$btn_radius         = absint( $args['btn_radius']           ?? 4 );
		$btn_border_width   = absint( $args['btn_border_width']     ?? 0 );
		$btn_border_style   = esc_attr( $args['btn_border_style']   ?? 'solid' );
		$btn_border_color   = esc_attr( $args['btn_border_color']   ?? '' );
		$btn_padding_top    = absint( $args['btn_padding_top']      ?? 10 );
		$btn_padding_right  = absint( $args['btn_padding_right']    ?? 20 );
		$btn_padding_bottom = absint( $args['btn_padding_bottom']   ?? 10 );
		$btn_padding_left   = absint( $args['btn_padding_left']     ?? 20 );
		$btn_font_size      = absint( $args['btn_font_size']        ?? 14 ) ?: 14;
		$btn_font_weight    = esc_attr( $args['btn_font_weight']    ?? '' );
		$btn_text_transform = esc_attr( $args['btn_text_transform'] ?? '' );
		$btn_letter_spacing = (float) ( $args['btn_letter_spacing'] ?? 0 );
		$btn_full_width          = ! empty( $args['btn_full_width'] );
		$btn_hover_bg            = esc_attr( $args['btn_hover_bg']            ?? '' );
		$btn_hover_text          = esc_attr( $args['btn_hover_text']          ?? '' );
		$btn_hover_border_color  = esc_attr( $args['btn_hover_border_color']  ?? '' );
		$btn_transition_duration = absint( $args['btn_transition_duration']   ?? 200 );

		$wrapper_attrs = sprintf(
			'class="bookit-widget" data-event="%s" data-type="%s" data-theme="%s" data-accent="%s" data-hide-details="%s" data-prefill="%s" data-ns="%s" data-height="%d"',
			$event,
			esc_attr( $type ),
			$theme,
			$accent,
			$hide_details,
			$prefill,
			$ns,
			$height
		);

		$inner = '';

		switch ( $type ) {
			case 'popup-button':
				// Build a complete inline style so block defaults override theme styles.
				$btn_style  = 'padding:' . $btn_padding_top . 'px ' . $btn_padding_right . 'px ' . $btn_padding_bottom . 'px ' . $btn_padding_left . 'px;';
				$btn_style .= 'font-size:' . $btn_font_size . 'px;';
				if ( $btn_font_weight )    { $btn_style .= 'font-weight:' . $btn_font_weight . ';'; }
				if ( $btn_text_transform ) { $btn_style .= 'text-transform:' . $btn_text_transform . ';'; }
				if ( $btn_letter_spacing ) { $btn_style .= 'letter-spacing:' . $btn_letter_spacing . 'px;'; }
				if ( $btn_bg )             { $btn_style .= 'background-color:' . $btn_bg . ';'; }
				if ( $btn_text )           { $btn_style .= 'color:' . $btn_text . ';'; }
				if ( $btn_border_width > 0 ) {
					$allowed_border_styles = array( 'solid', 'dashed', 'dotted' );
					$safe_border_style     = in_array( $btn_border_style, $allowed_border_styles, true ) ? $btn_border_style : 'solid';
					$btn_style .= 'border:' . $btn_border_width . 'px ' . $safe_border_style;
					if ( $btn_border_color ) { $btn_style .= ' ' . $btn_border_color; }
					$btn_style .= ';';
				} else {
					$btn_style .= 'border:none;';
				}
				$btn_style .= 'border-radius:' . $btn_radius . 'px;';
				if ( $btn_full_width ) { $btn_style .= 'width:100%;display:block;'; }

				// Add CSS transition for smooth hover effect (only if duration > 0).
				if ( $btn_transition_duration > 0 ) {
					$btn_style .= 'transition:background-color ' . $btn_transition_duration . 'ms ease,'
						. 'color ' . $btn_transition_duration . 'ms ease,'
						. 'border-color ' . $btn_transition_duration . 'ms ease;';
				}

				// Build hover rule and inject a scoped <style> if any hover color is set.
				// All values are already sanitized hex colors — no XSS risk.
				$hover_css = '';
				if ( $btn_hover_bg )           { $hover_css .= 'background-color:' . $btn_hover_bg . ';'; }
				if ( $btn_hover_text )         { $hover_css .= 'color:' . $btn_hover_text . ';'; }
				if ( $btn_hover_border_color ) { $hover_css .= 'border-color:' . $btn_hover_border_color . ';'; }

				$style_block = '';
				$extra_class = '';
				if ( $hover_css ) {
					$unique_class = 'bookit-h' . substr( md5( $hover_css ), 0, 8 );
					$style_block  = '<style>.' . $unique_class . ':hover{' . $hover_css . '}</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$extra_class  = ' ' . $unique_class;
				}

				$inner = $style_block . sprintf(
					'<button class="bookit-btn%s" type="button" style="%s">%s</button>',
					$extra_class,
					esc_attr( $btn_style ),
					$label
				);
				break;

			case 'popup-text':
				$inner = sprintf(
					'<a href="#" class="bookit-link">%s</a>',
					$label
				);
				break;

			case 'inline':
				$inner = sprintf(
					'<div class="bookit-inline" style="min-height:%dpx;width:100%%;" aria-label="%s"></div>',
					$height,
					esc_attr__( 'Booking calendar', 'bookit-for-calcom' )
				);
				break;
		}

		return sprintf( '<div %s>%s</div>', $wrapper_attrs, $inner );
	}

	// -------------------------------------------------------------------------
	// Sanitize helpers.
	// -------------------------------------------------------------------------

	/**
	 * Sanitize a display type value.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private static function sanitize_display_type( string $value ): string {
		$allowed = array( 'popup-button', 'popup-text', 'inline' );
		return in_array( $value, $allowed, true ) ? $value : 'popup-button';
	}

	/**
	 * Sanitize a theme value.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private static function sanitize_theme( string $value ): string {
		$allowed = array( 'global', 'auto', 'light', 'dark' );
		return in_array( $value, $allowed, true ) ? $value : 'global';
	}
}
