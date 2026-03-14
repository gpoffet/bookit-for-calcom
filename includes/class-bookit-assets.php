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
	 * Enqueue assets on demand (called from shortcode/block render when smart
	 * detection missed the widget — e.g. inside Formidable Forms HTML fields).
	 *
	 * Safe to call multiple times: wp_enqueue_script() is idempotent.
	 *
	 * @return void
	 */
	public static function enqueue_now(): void {
		self::enqueue( BookIt_Admin::get_settings() );
	}

	/**
	 * Enqueue Cal.com embed script + BookIt loader + localised data.
	 *
	 * @param array<string, mixed> $settings Plugin settings.
	 * @return void
	 */
	private static function enqueue( array $settings ): void {
		$cal_origin = self::get_cal_origin( $settings['api_base'] ?? '' );
		$embed_url  = $cal_origin . '/embed/embed.js';

		// Cal.com's embed cannot be loaded directly via <script src="embed.js">.
		// It must be bootstrapped by an inline stub that creates window.Cal first,
		// then dynamically loads embed.js. Registering a handle with src=false
		// lets us attach the stub as an inline script in the footer.
		if ( ! wp_script_is( 'calcom-embed', 'registered' ) ) {
			wp_register_script( 'calcom-embed', false, array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
			wp_add_inline_script(
				'calcom-embed',
				sprintf(
					// phpcs:ignore WordPress.WP.EnqueuedResourceParameters
					'(function(C,A,L){let p=function(a,ar){a.q.push(ar);};let d=C.document;C.Cal=C.Cal||function(){let cal=C.Cal;let ar=arguments;if(!cal.loaded){cal.ns={};cal.q=cal.q||[];d.head.appendChild(d.createElement("script")).src=A;cal.loaded=true;}if(ar[0]===L){const api=function(){p(api,arguments);};const namespace=ar[1];api.q=api.q||[];if(typeof namespace==="string"){cal.ns[namespace]=cal.ns[namespace]||api;p(cal.ns[namespace],ar);p(cal,[L,namespace,ar[2]]);}else{p(cal,ar);cal.ns["__call"]=cal.ns["__call"]||api;}return;}p(cal,ar);};})(window,%s,"init");',
					wp_json_encode( $embed_url )
				)
			);
		}
		wp_enqueue_script( 'calcom-embed' );

		// Plugin loader (depends on calcom-embed stub).
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
				'calOrigin'   => $cal_origin,
			)
		);
	}

	/**
	 * Derive the Cal.com app origin URL from the configured API base URL.
	 *
	 * Examples:
	 *   https://api.cal.com/v2      → https://app.cal.com
	 *   https://app.cal.eu/api/v2   → https://app.cal.eu
	 *
	 * @param string $api_base Stored api_base setting.
	 * @return string
	 */
	private static function get_cal_origin( string $api_base ): string {
		$parsed = wp_parse_url( $api_base );
		$scheme = $parsed['scheme'] ?? 'https';
		$host   = $parsed['host']   ?? 'api.cal.com';

		// When the API lives on an "api." subdomain, the embed app is on "app."
		// e.g. api.cal.com → app.cal.com, api.cal.eu → app.cal.eu
		if ( 0 === strpos( $host, 'api.' ) ) {
			return $scheme . '://app.' . substr( $host, 4 );
		}

		// For instances where the API and app share the same host
		// (e.g. app.cal.eu/api/v2), use the host directly.
		return $scheme . '://' . $host;
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
