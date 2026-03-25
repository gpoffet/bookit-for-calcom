<?php
/**
 * Server-side render for the bookit/cal-booking block.
 *
 * Available variables (injected by WordPress block renderer):
 *   $attributes  array  Block attributes.
 *   $content     string Inner block content (empty — dynamic block).
 *   $block       WP_Block Block instance.
 *
 * @package BookIt_For_CalCom
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Variables in block render callbacks are file-scoped by WP design; they are not true globals and do not pollute the namespace.

$settings = BookIt_Admin::get_settings();

/**
 * Returns the setting value if non-empty, or null.
 * Used to distinguish "setting not configured" from "setting configured to empty".
 *
 * @param string $key Setting key.
 * @return string|null
 */
$s = static function ( string $key ) use ( $settings ) {
	return ( isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) ? $settings[ $key ] : null;
};

// --- Basic --------------------------------------------------------------------

$event_type   = sanitize_text_field( $attributes['eventType']   ?? '' );
$display_type = sanitize_text_field( $attributes['displayType'] ?? 'popup-button' );

// label: block attr (if non-empty) → global default_label → hardcoded.
$label_raw = $attributes['label'] ?? null;
if ( '' !== (string) $label_raw ) {
	$label = sanitize_text_field( (string) $label_raw );
} elseif ( null !== $s( 'default_label' ) ) {
	$label = sanitize_text_field( $settings['default_label'] );
} else {
	$label = __( 'Book a meeting', 'bookit-for-cal-com' );
}

// inlineHeight: block attr → global inline_height → 600.
$inline_height_raw = $attributes['inlineHeight'] ?? null;
if ( null !== $inline_height_raw ) {
	$inline_height = absint( $inline_height_raw ) ?: 600;
} elseif ( null !== $s( 'inline_height' ) ) {
	$inline_height = absint( $settings['inline_height'] ) ?: 600;
} else {
	$inline_height = 600;
}

// --- Cal.com options ----------------------------------------------------------

$theme = sanitize_text_field( $attributes['theme'] ?? 'global' );

$accent_color = sanitize_hex_color( $attributes['accentColor'] ?? '' ) ?? '';
if ( empty( $accent_color ) && null !== $s( 'accent_color' ) ) {
	$accent_color = $settings['accent_color'];
}

$hide_details = ! empty( $attributes['hideDetails'] ) ? '1' : '0';
$prefill_user = ! empty( $attributes['prefillUser'] )  ? '1' : '0';

// --- Button colors ------------------------------------------------------------

$btn_bg = sanitize_hex_color( $attributes['btnBgColor'] ?? '' ) ?? '';
if ( empty( $btn_bg ) && null !== $s( 'btn_bg' ) ) {
	$btn_bg = $settings['btn_bg'];
}

$btn_text = sanitize_hex_color( $attributes['btnTextColor'] ?? '' ) ?? '';
if ( empty( $btn_text ) && null !== $s( 'btn_text' ) ) {
	$btn_text = $settings['btn_text'];
}

// --- Border -------------------------------------------------------------------

// btnBorderRadius: block attr → global → 4.
$btn_radius_raw = $attributes['btnBorderRadius'] ?? null;
if ( null !== $btn_radius_raw ) {
	$btn_radius = absint( $btn_radius_raw );
} elseif ( null !== $s( 'btn_radius' ) ) {
	$btn_radius = absint( $settings['btn_radius'] );
} else {
	$btn_radius = 4;
}

// btnBorderWidth: block attr → global → 0.
$btn_border_width_raw = $attributes['btnBorderWidth'] ?? null;
if ( null !== $btn_border_width_raw ) {
	$btn_border_width = absint( $btn_border_width_raw );
} elseif ( null !== $s( 'btn_border_width' ) ) {
	$btn_border_width = absint( $settings['btn_border_width'] );
} else {
	$btn_border_width = 0;
}

// btnBorderStyle: block attr → global → 'solid'.
$btn_border_style_raw = $attributes['btnBorderStyle'] ?? null;
if ( '' !== (string) $btn_border_style_raw ) {
	$btn_border_style = sanitize_text_field( (string) $btn_border_style_raw );
} elseif ( null !== $s( 'btn_border_style' ) ) {
	$btn_border_style = sanitize_text_field( $settings['btn_border_style'] );
} else {
	$btn_border_style = 'solid';
}

$btn_border_color = sanitize_hex_color( $attributes['btnBorderColor'] ?? '' ) ?? '';
if ( empty( $btn_border_color ) && null !== $s( 'btn_border_color' ) ) {
	$btn_border_color = $settings['btn_border_color'];
}

// --- Padding ------------------------------------------------------------------

$btn_padding_top_raw = $attributes['btnPaddingTop'] ?? null;
if ( null !== $btn_padding_top_raw ) {
	$btn_padding_top = absint( $btn_padding_top_raw );
} elseif ( null !== $s( 'btn_padding_top' ) ) {
	$btn_padding_top = absint( $settings['btn_padding_top'] );
} else {
	$btn_padding_top = 10;
}

$btn_padding_right_raw = $attributes['btnPaddingRight'] ?? null;
if ( null !== $btn_padding_right_raw ) {
	$btn_padding_right = absint( $btn_padding_right_raw );
} elseif ( null !== $s( 'btn_padding_right' ) ) {
	$btn_padding_right = absint( $settings['btn_padding_right'] );
} else {
	$btn_padding_right = 20;
}

$btn_padding_bottom_raw = $attributes['btnPaddingBottom'] ?? null;
if ( null !== $btn_padding_bottom_raw ) {
	$btn_padding_bottom = absint( $btn_padding_bottom_raw );
} elseif ( null !== $s( 'btn_padding_bottom' ) ) {
	$btn_padding_bottom = absint( $settings['btn_padding_bottom'] );
} else {
	$btn_padding_bottom = 10;
}

$btn_padding_left_raw = $attributes['btnPaddingLeft'] ?? null;
if ( null !== $btn_padding_left_raw ) {
	$btn_padding_left = absint( $btn_padding_left_raw );
} elseif ( null !== $s( 'btn_padding_left' ) ) {
	$btn_padding_left = absint( $settings['btn_padding_left'] );
} else {
	$btn_padding_left = 20;
}

// --- Typography ---------------------------------------------------------------

$btn_font_size_raw = $attributes['btnFontSize'] ?? null;
if ( null !== $btn_font_size_raw ) {
	$btn_font_size = absint( $btn_font_size_raw ) ?: 14;
} elseif ( null !== $s( 'btn_font_size' ) ) {
	$btn_font_size = absint( $settings['btn_font_size'] ) ?: 14;
} else {
	$btn_font_size = 14;
}

$btn_font_weight = sanitize_text_field( $attributes['btnFontWeight'] ?? '' );
if ( empty( $btn_font_weight ) && null !== $s( 'btn_font_weight' ) ) {
	$btn_font_weight = $settings['btn_font_weight'];
}

$btn_text_transform = sanitize_text_field( $attributes['btnTextTransform'] ?? '' );
if ( empty( $btn_text_transform ) && null !== $s( 'btn_text_transform' ) ) {
	$btn_text_transform = $settings['btn_text_transform'];
}

$btn_letter_spacing_raw = $attributes['btnLetterSpacing'] ?? null;
if ( null !== $btn_letter_spacing_raw ) {
	$btn_letter_spacing = (float) $btn_letter_spacing_raw;
} elseif ( null !== $s( 'btn_letter_spacing' ) ) {
	$btn_letter_spacing = (float) $settings['btn_letter_spacing'];
} else {
	$btn_letter_spacing = 0;
}

// --- Layout -------------------------------------------------------------------

// btnFullWidth: block attr (if explicitly set) → global → false.
$btn_full_width_raw = $attributes['btnFullWidth'] ?? null;
if ( null !== $btn_full_width_raw ) {
	$btn_full_width = (bool) $btn_full_width_raw;
} else {
	$btn_full_width = (bool) $settings['btn_full_width'];
}

// --- Hover effects ------------------------------------------------------------

$btn_hover_bg = sanitize_hex_color( $attributes['btnHoverBgColor'] ?? '' ) ?? '';
if ( empty( $btn_hover_bg ) && null !== $s( 'btn_hover_bg' ) ) {
	$btn_hover_bg = $settings['btn_hover_bg'];
}

$btn_hover_text = sanitize_hex_color( $attributes['btnHoverTextColor'] ?? '' ) ?? '';
if ( empty( $btn_hover_text ) && null !== $s( 'btn_hover_text' ) ) {
	$btn_hover_text = $settings['btn_hover_text'];
}

$btn_hover_border_color = sanitize_hex_color( $attributes['btnHoverBorderColor'] ?? '' ) ?? '';
if ( empty( $btn_hover_border_color ) && null !== $s( 'btn_hover_border_color' ) ) {
	$btn_hover_border_color = $settings['btn_hover_border_color'];
}

$btn_transition_duration_raw = $attributes['btnTransitionDuration'] ?? null;
if ( null !== $btn_transition_duration_raw ) {
	$btn_transition_duration = absint( $btn_transition_duration_raw );
} elseif ( null !== $s( 'btn_transition_duration' ) ) {
	$btn_transition_duration = absint( $settings['btn_transition_duration'] );
} else {
	$btn_transition_duration = 200;
}

$ns = sanitize_key( $settings['namespace'] ?: 'cal' );

// Nothing to render without an event.
if ( empty( $event_type ) ) {
	return;
}

// If the event slug has no username prefix, resolve it from settings or the API.
if ( false === strpos( $event_type, '/' ) ) {
	$username = ! empty( $settings['username'] )
		? $settings['username']
		: BookIt_API::get_username( $settings['api_key'], $settings['api_base'] );
	if ( ! empty( $username ) ) {
		$event_type = $username . '/' . $event_type;
	}
}

// Resolve "global" theme.
if ( 'global' === $theme ) {
	$theme = $settings['theme'] ?? 'auto';
}

// Delegate to the shared HTML builder in BookIt_Shortcode.
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- build_html() escapes all output internally; every value passed here is sanitized above (sanitize_text_field, sanitize_hex_color, absint, sanitize_key).
echo BookIt_Shortcode::build_html( array(
	'event'        => $event_type,
	'type'         => $display_type,
	'label'        => $label,
	'height'       => $inline_height,
	'theme'        => $theme,
	'accent'       => $accent_color,
	'hide_details' => $hide_details,
	'prefill'      => $prefill_user,
	'btn_bg'             => $btn_bg,
	'btn_text'           => $btn_text,
	'btn_radius'         => $btn_radius,
	'btn_border_width'   => $btn_border_width,
	'btn_border_style'   => $btn_border_style,
	'btn_border_color'   => $btn_border_color,
	'btn_padding_top'    => $btn_padding_top,
	'btn_padding_right'  => $btn_padding_right,
	'btn_padding_bottom' => $btn_padding_bottom,
	'btn_padding_left'   => $btn_padding_left,
	'btn_font_size'      => $btn_font_size,
	'btn_font_weight'    => $btn_font_weight,
	'btn_text_transform' => $btn_text_transform,
	'btn_letter_spacing' => $btn_letter_spacing,
	'btn_full_width'          => $btn_full_width,
	'btn_hover_bg'            => $btn_hover_bg,
	'btn_hover_text'          => $btn_hover_text,
	'btn_hover_border_color'  => $btn_hover_border_color,
	'btn_transition_duration' => $btn_transition_duration,
	'ns'                      => $ns,
) );
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
