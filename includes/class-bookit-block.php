<?php
/**
 * Gutenberg block registration for BookIt for Cal.com.
 *
 * @package BookIt_For_CalCom
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BookIt_Block
 *
 * Registers the bookit/cal-booking dynamic block and passes editor data
 * (event types, API key presence) to the JS editor via wp_localize_script.
 *
 * @since 1.0.0
 */
class BookIt_Block {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_data' ) );
	}

	/**
	 * Register the block from block.json.
	 *
	 * @return void
	 */
	public static function register_block(): void {
		register_block_type( BOOKIT_PLUGIN_DIR . 'blocks/cal-booking' );
	}

	/**
	 * Pass event types and settings to the block editor.
	 *
	 * Called on enqueue_block_editor_assets so the data is available
	 * in edit.jsx via window.bookitEditorData.
	 *
	 * @return void
	 */
	public static function enqueue_editor_data(): void {
		$settings    = BookIt_Admin::get_settings();
		$has_api_key = ! empty( $settings['api_key'] );
		$event_types = array();

		if ( $has_api_key ) {
			$result = BookIt_API::get_event_types( $settings['api_key'], $settings['api_base'] );
			if ( ! is_wp_error( $result ) ) {
				$event_types = $result;
			}
		}

		// Resolve username: manual setting takes priority, then auto-fetch from /me.
		$username = ! empty( $settings['username'] )
			? $settings['username']
			: ( $has_api_key ? BookIt_API::get_username( $settings['api_key'], $settings['api_base'] ) : '' );

		wp_localize_script(
			'bookit-cal-booking-editor-script',
			'bookitEditorData',
			array(
				'hasApiKey'  => $has_api_key,
				'eventTypes' => $event_types,
				'username'   => $username,
				'namespace'  => $settings['namespace'],
			)
		);
	}
}
