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
 * Handles communication with the Cal.com v1 REST API and caches results
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
	const API_BASE = 'https://api.cal.com/v1';

	/**
	 * Transient key for cached event types.
	 *
	 * @var string
	 */
	const TRANSIENT_KEY = 'bookit_event_types';

	/**
	 * Cache duration in seconds (1 hour).
	 *
	 * @var int
	 */
	const CACHE_TTL = HOUR_IN_SECONDS;

	/**
	 * Retrieve event types from Cal.com API or transient cache.
	 *
	 * @param string $api_key Cal.com API key.
	 * @return array|\WP_Error Array of event type objects, or WP_Error on failure.
	 */
	public static function get_event_types( string $api_key ): array|\WP_Error {
		if ( empty( $api_key ) ) {
			return new \WP_Error(
				'bookit_no_api_key',
				__( 'No Cal.com API key configured.', 'bookit-for-calcom' )
			);
		}

		// Return cached data if available.
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( false !== $cached ) {
			return $cached;
		}

		$url = add_query_arg(
			'apiKey',
			rawurlencode( $api_key ),
			self::API_BASE . '/event-types'
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'user-agent' => 'BookIt-for-CalCom/' . BOOKIT_VERSION . '; ' . get_bloginfo( 'url' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $status ) {
			return new \WP_Error(
				'bookit_api_error',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Cal.com API returned HTTP %d.', 'bookit-for-calcom' ),
					$status
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || ! isset( $data['event_types'] ) ) {
			return new \WP_Error(
				'bookit_api_parse_error',
				__( 'Could not parse Cal.com API response.', 'bookit-for-calcom' )
			);
		}

		$event_types = $data['event_types'];

		set_transient( self::TRANSIENT_KEY, $event_types, self::CACHE_TTL );

		return $event_types;
	}

	/**
	 * Force-clear the cached event types transient.
	 *
	 * @return void
	 */
	public static function flush_cache(): void {
		delete_transient( self::TRANSIENT_KEY );
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
