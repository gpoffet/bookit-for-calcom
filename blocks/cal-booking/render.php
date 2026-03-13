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

$settings = BookIt_Admin::get_settings();

// --- Resolve attributes with fallbacks to global settings -------------------

$event_type    = sanitize_text_field( $attributes['eventType']      ?? '' );
$display_type  = sanitize_text_field( $attributes['displayType']    ?? 'popup-button' );
$label         = sanitize_text_field( $attributes['label']          ?? __( 'Book a meeting', 'bookit-for-calcom' ) );
$inline_height = absint( $attributes['inlineHeight'] ?? 600 ) ?: 600;
$theme         = sanitize_text_field( $attributes['theme']          ?? 'global' );
$accent_color  = sanitize_hex_color( $attributes['accentColor']     ?? '' ) ?? '';
$hide_details  = ! empty( $attributes['hideDetails'] ) ? '1' : '0';
$prefill_user  = ! empty( $attributes['prefillUser'] )  ? '1' : '0';
$btn_bg             = sanitize_hex_color( $attributes['btnBgColor']        ?? '' ) ?? '';
$btn_text           = sanitize_hex_color( $attributes['btnTextColor']      ?? '' ) ?? '';
$btn_radius         = absint( $attributes['btnBorderRadius']  ?? 4 );
$btn_border_width   = absint( $attributes['btnBorderWidth']   ?? 0 );
$btn_border_style   = sanitize_text_field( $attributes['btnBorderStyle']   ?? 'solid' );
$btn_border_color   = sanitize_hex_color( $attributes['btnBorderColor']    ?? '' ) ?? '';
$btn_padding_top    = absint( $attributes['btnPaddingTop']    ?? 10 );
$btn_padding_right  = absint( $attributes['btnPaddingRight']  ?? 20 );
$btn_padding_bottom = absint( $attributes['btnPaddingBottom'] ?? 10 );
$btn_padding_left   = absint( $attributes['btnPaddingLeft']   ?? 20 );
$btn_font_size      = absint( $attributes['btnFontSize']      ?? 14 ) ?: 14;
$btn_font_weight    = sanitize_text_field( $attributes['btnFontWeight']    ?? '' );
$btn_text_transform = sanitize_text_field( $attributes['btnTextTransform'] ?? '' );
$btn_letter_spacing = (float) ( $attributes['btnLetterSpacing'] ?? 0 );
$btn_full_width          = ! empty( $attributes['btnFullWidth'] );
$btn_hover_bg            = sanitize_hex_color( $attributes['btnHoverBgColor']       ?? '' ) ?? '';
$btn_hover_text          = sanitize_hex_color( $attributes['btnHoverTextColor']      ?? '' ) ?? '';
$btn_hover_border_color  = sanitize_hex_color( $attributes['btnHoverBorderColor']    ?? '' ) ?? '';
$btn_transition_duration = absint( $attributes['btnTransitionDuration'] ?? 200 );
$ns                      = sanitize_key( $settings['namespace'] ?: 'cal' );

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

// Fall back to global accent color.
if ( empty( $accent_color ) && ! empty( $settings['accent_color'] ) ) {
	$accent_color = $settings['accent_color'];
}

// Delegate to the shared HTML builder in BookIt_Shortcode.
echo BookIt_Shortcode::build_html( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
