<?php
/**
 * File containing the class \Sensei_LMS_Beta\Sensei_LMS_Beta.
 *
 * @package sensei-lms-beta
 * @since   1.0.0
 */

namespace Sensei_LMS_Beta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Sensei LMS Beta tester class.
 *
 * @class Sensei_LMS_Beta
 */
final class Sensei_LMS_Beta {
	const SENSEI_LMS_BETA_REPORT_ISSUE_URL = 'https://github.com/Automattic/sensei/issue/new';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Plugin directory.
	 *
	 * @var string
	 */
	public $plugin_dir;

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Initialize the singleton instance.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->plugin_dir = dirname( __DIR__ );
		$this->plugin_url = untrailingslashit( plugins_url( '', SENSEI_LMS_BETA_PLUGIN_BASENAME ) );
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		$instance = self::instance();

		$instance->include_dependencies();

		add_action( 'init', [ $instance, 'load_plugin_textdomain' ] );

		Admin\Settings::instance()->init();
	}

	/**
	 * Include required files.
	 */
	private function include_dependencies() {
		include_once $this->plugin_dir . '/includes/admin/class-settings.php';
		include_once $this->plugin_dir . '/includes/updater/class-abstract-updater.php';
		include_once $this->plugin_dir . '/includes/class-updater.php';
	}

	/**
	 * Loads textdomain for plugin.
	 */
	public function load_plugin_textdomain() {
		$domain = 'sensei-lms-beta';
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, $domain );

		unload_textdomain( $domain );
		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
