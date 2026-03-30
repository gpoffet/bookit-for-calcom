<?php
/**
 * Plugin Name:       BookIt for Cal.com
 * Plugin URI:        https://github.com/gpoffet/bookit-for-calcom
 * Description:       Integrate Cal.com booking widgets into WordPress via a Gutenberg block, Elementor widget, and shortcode.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Gaël Poffet
 * Author URI:        https://poffet.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bookit-for-cal-com
 * Domain Path:       /languages
 *
 * @package BookIt_For_CalCom
 */

defined( 'ABSPATH' ) || exit;

// Constants.
define( 'BOOKIT_VERSION', '1.0.1' );
define( 'BOOKIT_PLUGIN_FILE', __FILE__ );
define( 'BOOKIT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BOOKIT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BOOKIT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class — singleton bootstrap.
 *
 * @since 1.0.0
 */
final class BookIt_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var BookIt_Plugin|null
	 */
	private static ?BookIt_Plugin $instance = null;

	/**
	 * Return or create the singleton instance.
	 *
	 * @return BookIt_Plugin
	 */
	public static function instance(): BookIt_Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — use instance().
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Require all class files.
	 *
	 * @return void
	 */
	private function load_dependencies(): void {
		require_once BOOKIT_PLUGIN_DIR . 'includes/class-bookit-api.php';
		require_once BOOKIT_PLUGIN_DIR . 'includes/class-bookit-admin.php'; // Always needed: get_settings() used in render.php (REST context).
		require_once BOOKIT_PLUGIN_DIR . 'includes/class-bookit-assets.php';
		require_once BOOKIT_PLUGIN_DIR . 'includes/class-bookit-shortcode.php';
		require_once BOOKIT_PLUGIN_DIR . 'includes/class-bookit-block.php';
	}

	/**
	 * Register hooks for all sub-components.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		BookIt_Assets::init();
		BookIt_Shortcode::init();
		BookIt_Block::init();

		if ( is_admin() ) {
			BookIt_Admin::init();
		}

		// Load Elementor widget after Elementor is ready.
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_elementor_category' ) );
	}

	/**
	 * Register the custom "BookIt" Elementor category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 * @return void
	 */
	public function register_elementor_category( \Elementor\Elements_Manager $elements_manager ): void {
		$elements_manager->add_category(
			'bookit',
			array(
				'title' => esc_html__( 'BookIt', 'bookit-for-cal-com' ),
				'icon'  => 'eicon-calendar',
			)
		);
	}

	/**
	 * Register the Elementor widget — only if Elementor is active.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_elementor_widget( \Elementor\Widgets_Manager $widgets_manager ): void {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}
		require_once BOOKIT_PLUGIN_DIR . 'elementor/class-bookit-elementor-widget.php';
		$widgets_manager->register( new BookIt_Elementor_Widget() );
	}
}

/**
 * Return the main plugin instance.
 *
 * @return BookIt_Plugin
 */
function bookit(): BookIt_Plugin {
	return BookIt_Plugin::instance();
}

bookit();
