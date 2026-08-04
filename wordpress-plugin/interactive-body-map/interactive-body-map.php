<?php
/**
 * Plugin Name:       Interactive Body Map
 * Plugin URI:        https://github.com/shahroozj/interactive-body-map
 * Description:       A clickable human body diagram. Link the head, arms, legs, chest, abdomen and more to any page on your site. Scalable vector artwork on a transparent background, responsive on every device.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Author:            Shahrooz Jafari
 * Text Domain:       interactive-body-map
 * Domain Path:       /languages
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 *
 * @package Interactive_Body_Map
 */

defined( 'ABSPATH' ) || exit;

define( 'BODYMAP_VERSION', '1.0.0' );
define( 'BODYMAP_FILE', __FILE__ );
define( 'BODYMAP_DIR', plugin_dir_path( __FILE__ ) );
define( 'BODYMAP_URL', plugin_dir_url( __FILE__ ) );

require_once BODYMAP_DIR . 'includes/class-bodymap-geometry.php';
require_once BODYMAP_DIR . 'includes/class-bodymap-model.php';
require_once BODYMAP_DIR . 'includes/class-bodymap-settings.php';
require_once BODYMAP_DIR . 'includes/class-bodymap-render.php';
require_once BODYMAP_DIR . 'includes/class-bodymap-shortcode.php';
require_once BODYMAP_DIR . 'includes/class-bodymap-block.php';

/**
 * Plugin bootstrap.
 */
final class Interactive_Body_Map {

	/**
	 * Singleton instance.
	 *
	 * @var Interactive_Body_Map|null
	 */
	private static $instance = null;

	/**
	 * Returns the one instance, creating it on first call.
	 *
	 * @return Interactive_Body_Map
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hooks everything up.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( 'BodyMap_Shortcode', 'register' ) );
		add_action( 'init', array( 'BodyMap_Block', 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		// Also registered in admin so the settings screen can show a live preview.
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );

		if ( is_admin() ) {
			BodyMap_Settings::init();
		}

		add_filter( 'plugin_action_links_' . plugin_basename( BODYMAP_FILE ), array( $this, 'action_links' ) );
	}

	/**
	 * Loads translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'interactive-body-map',
			false,
			dirname( plugin_basename( BODYMAP_FILE ) ) . '/languages'
		);
	}

	/**
	 * Registers the front-end assets.
	 *
	 * They are only enqueued when a diagram is actually on the page, which the
	 * shortcode and the block both take care of.
	 *
	 * @return void
	 */
	public function register_assets() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_style(
			'interactive-body-map',
			BODYMAP_URL . 'assets/css/body-map' . $suffix . '.css',
			array(),
			BODYMAP_VERSION
		);

		wp_register_script(
			'interactive-body-map',
			BODYMAP_URL . 'assets/js/body-map' . $suffix . '.js',
			array(),
			BODYMAP_VERSION,
			true
		);
	}

	/**
	 * Adds a Settings link on the plugins screen.
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	public function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=' . BodyMap_Settings::PAGE );

		array_unshift(
			$links,
			sprintf(
				'<a href="%s">%s</a>',
				esc_url( $url ),
				esc_html__( 'Settings', 'interactive-body-map' )
			)
		);

		return $links;
	}

	/**
	 * Seeds the default options on activation.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( false === get_option( BodyMap_Settings::OPTION, false ) ) {
			add_option( BodyMap_Settings::OPTION, BodyMap_Settings::defaults() );
		}
	}
}

register_activation_hook( __FILE__, array( 'Interactive_Body_Map', 'activate' ) );

Interactive_Body_Map::instance();
