<?php
/**
 * Conditional asset enqueue for BookIt for Cal.com.
 *
 * @package BookIt_For_CalCom
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BookIt_Assets
 *
 * Enqueues the Cal.com embed script and the plugin's loader script on the
 * frontend. Uses a "smart" strategy by default (only on pages that contain
 * a booking widget) or always, depending on settings.
 *
 * @since 1.0.0
 */
class BookIt_Assets {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ) );
	}

	/**
	 * Decide whether to enqueue and do so.
	 *
	 * @return void
	 */
	public static function maybe_enqueue(): void {
		$settings = BookIt_Admin::get_settings();

		if ( 'always' === $settings['load_strategy'] ) {
			self::enqueue( $settings );
			return;
		}

		// Smart strategy: enqueue only if page contains a booking widget.
		if ( self::page_has_widget() ) {
			self::enqueue( $settings );
		}
	}

	/**
	 * Enqueue Cal.com embed script + BookIt loader + localised data.
	 *
	 * @param array<string, mixed> $settings Plugin settings.
	 * @return void
	 */
	private static function enqueue( array $settings ): void {
		// Cal.com embed script (external, loaded in footer).
		wp_enqueue_script(
			'calcom-embed',
			'https://app.cal.com/embed/embed.js',
			array(),
			null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			true
		);

		// Plugin loader (depends on calcom-embed).
		wp_enqueue_script(
			'bookit-loader',
			BOOKIT_PLUGIN_URL . 'assets/js/bookit-loader.js',
			array( 'calcom-embed' ),
			BOOKIT_VERSION,
			true
		);

		// Pass data to JS.
		$current_user_data = self::get_current_user_data();

		wp_localize_script(
			'bookit-loader',
			'bookitCalcomData',
			array(
				'currentUser' => $current_user_data,
				'namespace'   => $settings['namespace'],
			)
		);
	}

	/**
	 * Detect whether the current page/post contains a BookIt widget.
	 *
	 * Checks for:
	 *  - The [bookit] shortcode in post content.
	 *  - The bookit/cal-booking Gutenberg block.
	 *  - A custom post meta flag set by the Elementor widget on save.
	 *
	 * @return bool
	 */
	private static function page_has_widget(): bool {
		if ( ! is_singular() ) {
			return false;
		}

		$post = get_queried_object();
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		if ( has_shortcode( $post->post_content, 'bookit' ) ) {
			return true;
		}

		if ( has_block( 'bookit/cal-booking', $post ) ) {
			return true;
		}

		// Elementor widget sets this meta key when saving a page that contains
		// the BookIt widget.
		if ( get_post_meta( $post->ID, '_bookit_elementor_widget', true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Build the current user data array for JS pre-fill.
	 *
	 * Returns null values when user is not logged in.
	 *
	 * @return array<string, string|null>
	 */
	private static function get_current_user_data(): array {
		if ( ! is_user_logged_in() ) {
			return array(
				'name'  => null,
				'email' => null,
			);
		}

		$user = wp_get_current_user();
		return array(
			'name'  => $user->display_name ?: null,
			'email' => $user->user_email   ?: null,
		);
	}
}
