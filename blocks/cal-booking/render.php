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
$btn_bg        = sanitize_hex_color( $attributes['btnBgColor']      ?? '' ) ?? '';
$btn_text      = sanitize_hex_color( $attributes['btnTextColor']    ?? '' ) ?? '';
$btn_radius    = absint( $attributes['btnBorderRadius'] ?? 4 );
$ns            = sanitize_key( $settings['namespace'] ?: 'cal' );

// Nothing to render without an event.
if ( empty( $event_type ) ) {
	return;
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
	'btn_bg'       => $btn_bg,
	'btn_text'     => $btn_text,
	'btn_radius'   => $btn_radius,
	'ns'           => $ns,
) );
