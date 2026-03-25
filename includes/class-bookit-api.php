<?php
/**
 * Cal.com REST API wrapper with transient cache.
 *
 * @package BookIt_For_CalCom
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BookIt_API
 *
 * Handles communication with the Cal.com v2 REST API and caches results
 * in a WordPress transient for 1 hour.
 *
 * @since 1.0.0
 */
class BookIt_API {

	/**
	 * Cal.com API base URL.
	 *
	 * @var string
	 */
	const API_BASE = 'https://api.cal.com/v2';

	/**
	 * Transient key for cached event types.
	 *
	 * @var string
	 */
	const TRANSIENT_KEY = 'bookit_event_types';

	/**
	 * Transient key for cached Cal.com username (fetched from /me).
	 *
	 * @var string
	 */
	const TRANSIENT_USERNAME = 'bookit_cal_username';

	/**
	 * Cache duration in seconds (1 hour).
	 *
	 * @var int
	 */
	const CACHE_TTL = HOUR_IN_SECONDS;

	/**
	 * Retrieve event types from Cal.com API or transient cache.
	 *
	 * @param string $api_key  Cal.com API key.
	 * @param string $api_base Optional API base URL (defaults to self::API_BASE).
	 * @return array|\WP_Error Array of event type objects, or WP_Error on failure.
	 */
	public static function get_event_types( string $api_key, string $api_base = '' ): array|\WP_Error {
		if ( empty( $api_key ) ) {
			return new \WP_Error(
				'bookit_no_api_key',
				__( 'No Cal.com API key configured.', 'bookit-for-cal-com' )
			);
		}

		$api_base = empty( $api_base ) ? self::API_BASE : rtrim( $api_base, '/' );

		// Return cached data if available.
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( false !== $cached ) {
			return $cached;
		}

		$url = $api_base . '/event-types';

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'user-agent' => 'BookIt-for-CalCom/' . BOOKIT_VERSION . '; ' . get_bloginfo( 'url' ),
				'headers'    => array(
					'Authorization'    => 'Bearer ' . $api_key,
					'cal-api-version'  => '2024-06-14',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $status ) {
			$body    = wp_remote_retrieve_body( $response );
			$decoded = json_decode( $body, true );

			// Extract a readable message from various Cal.com error shapes.
			if ( isset( $decoded['message'] ) ) {
				$raw = $decoded['message'];
				$message = is_array( $raw ) ? implode( ' ', $raw ) : (string) $raw;
			} elseif ( isset( $decoded['error']['message'] ) ) {
				$message = (string) $decoded['error']['message'];
			} elseif ( isset( $decoded['error'] ) && is_string( $decoded['error'] ) ) {
				$message = $decoded['error'];
			} else {
				$message = $body;
			}

			return new \WP_Error(
				'bookit_api_error',
				sprintf(
					/* translators: 1: HTTP status code, 2: error message from API */
					__( 'Cal.com API returned HTTP %1$d: %2$s', 'bookit-for-cal-com' ),
					$status,
					$message
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return new \WP_Error(
				'bookit_api_parse_error',
				__( 'Could not parse Cal.com API response.', 'bookit-for-cal-com' )
			);
		}

		$event_types = $data['data'];

		set_transient( self::TRANSIENT_KEY, $event_types, self::CACHE_TTL );

		// Opportunistically cache the username from the owner of the first event type.
		// This avoids a separate /me call when the username isn't yet cached.
		if ( false === get_transient( self::TRANSIENT_USERNAME ) && ! empty( $event_types ) ) {
			foreach ( (array) $event_types as $et ) {
				$uname = $et['owner']['username'] ?? $et['profile']['username'] ?? $et['user']['username'] ?? '';
				if ( ! empty( $uname ) ) {
					set_transient( self::TRANSIENT_USERNAME, $uname, self::CACHE_TTL );
					break;
				}
			}
		}

		return $event_types;
	}

	/**
	 * Retrieve the Cal.com username for the given API key via the /me endpoint.
	 *
	 * Result is cached in a transient for 1 hour. Returns an empty string on
	 * any failure so callers can degrade gracefully.
	 *
	 * @param string $api_key  Cal.com API key.
	 * @param string $api_base Optional API base URL.
	 * @return string Username or empty string.
	 */
	public static function get_username( string $api_key, string $api_base = '' ): string {
		if ( empty( $api_key ) ) {
			return '';
		}

		$cached = get_transient( self::TRANSIENT_USERNAME );
		if ( false !== $cached ) {
			return (string) $cached;
		}

		$api_base = empty( $api_base ) ? self::API_BASE : rtrim( $api_base, '/' );

		$response = wp_remote_get(
			$api_base . '/me',
			array(
				'timeout'    => 10,
				'user-agent' => 'BookIt-for-CalCom/' . BOOKIT_VERSION . '; ' . get_bloginfo( 'url' ),
				'headers'    => array(
					'Authorization'   => 'Bearer ' . $api_key,
					'cal-api-version' => '2024-06-14',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$username = $data['data']['username'] ?? '';

		if ( ! empty( $username ) ) {
			set_transient( self::TRANSIENT_USERNAME, $username, self::CACHE_TTL );
		}

		return (string) $username;
	}

	/**
	 * Force-clear the cached event types transient.
	 *
	 * @return void
	 */
	public static function flush_cache(): void {
		delete_transient( self::TRANSIENT_KEY );
		delete_transient( self::TRANSIENT_USERNAME );
	}

	/**
	 * Build a human-readable label for an event type.
	 *
	 * Format: "Title — slug" (e.g. "Consultation — consultation-30min").
	 *
	 * @param array $event_type Single event-type object from the API.
	 * @return string
	 */
	public static function format_event_label( array $event_type ): string {
		$title = isset( $event_type['title'] ) ? $event_type['title'] : '';
		$slug  = isset( $event_type['slug'] ) ? $event_type['slug'] : '';
		return $title . ' \u2014 ' . $slug;
	}
}
