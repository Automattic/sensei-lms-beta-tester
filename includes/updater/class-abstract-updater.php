<?php
/**
 * File containing the class \Sensei_LMS_Beta\Updater\Updater_Base.
 *
 * @package sensei-lms-beta
 * @since   1.0.0
 */

namespace Sensei_LMS_Beta\Updater;

use BjornJohansen\WPPreCommitHook\Plugin;
use Sensei_LMS_Beta\Admin\Plugin_Package;
use Sensei_LMS_Beta\Updater\Sources\Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing the shared update logic.
 *
 * @class \Sensei_LMS_Beta\Updater\Updater_Base
 */
abstract class Abstract_Updater {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Initialize the singleton instance.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->include_dependencies();
	}

	/**
	 * Adds all filters and actions.
	 *
	 * @since 1.0.0
	 */
	public function init() {
	}

	/**
	 * Get all version plugin packages.
	 *
	 * @param callable $filter_callback Callback to filter the versions returned.
	 * @return \Sensei_LMS_Beta\Admin\Plugin_Package[]
	 */
	public function get_versions( $filter_callback = null ) {
		$source_versions = $this->get_plugin_package_source()->get_versions();

		if ( $filter_callback ) {
			$source_versions = array_filter( $source_versions, $filter_callback );
		}

		uasort(
			$source_versions,
			function( $release_a, $release_b ) {
				return version_compare( $release_a->get_version(), $release_b->get_version(), '<' );
			}
		);

		return $source_versions;
	}

	/**
	 * Gets the latest beta channel release plugin package.
	 *
	 * @return bool|Plugin_Package
	 */
	public function get_latest_beta_channel_release() {
		$releases = $this->get_beta_channel();

		if ( empty( $releases ) ) {
			return false;
		}

		return array_shift( $releases );
	}

	/**
	 * Gets the latest RC channel release plugin package.
	 *
	 * @return bool|Plugin_Package
	 */
	public function get_latest_rc_channel_release() {
		$releases = $this->get_rc_channel();

		if ( empty( $releases ) ) {
			return false;
		}

		return array_shift( $releases );
	}

	/**
	 * Gets the latest stable channel release plugin package.
	 *
	 * @return bool|Plugin_Package
	 */
	public function get_latest_stable_channel_release() {
		$releases = $this->get_stable_channel();

		if ( empty( $releases ) ) {
			return false;
		}

		return array_shift( $releases );
	}

	/**
	 * Get version plugin packages for betas, RCs, and stable.
	 *
	 * @return \Sensei_LMS_Beta\Admin\Plugin_Package[]
	 */
	private function get_beta_channel() {
		return $this->get_versions();
	}

	/**
	 * Get version plugin packages for RCs and stable.
	 *
	 * @return \Sensei_LMS_Beta\Admin\Plugin_Package[]
	 */
	private function get_rc_channel() {
		return $this->get_versions(
			function( $package ) {
					/**
					 * Package variable.
					 *
					 * @var Plugin_Package $package
					 */
					return $package->is_stable() || $package->is_rc();
			}
		);
	}

	/**
	 * Get version plugin packages for stable only.
	 *
	 * @return \Sensei_LMS_Beta\Admin\Plugin_Package[]
	 */
	private function get_stable_channel() {
		return $this->get_versions(
			function( $package ) {
					/**
					 * Package variable.
					 *
					 * @var Plugin_Package $package
					 */
					return $package->is_stable();
			}
		);
	}

	/**
	 * Include required files.
	 */
	private function include_dependencies() {
		include_once __DIR__ . '/class-plugin-package.php';
		include_once __DIR__ . '/sources/interface-source.php';
		include_once __DIR__ . '/sources/class-abstract-source.php';
		include_once __DIR__ . '/sources/class-github.php';
	}

	/**
	 * Gets the source object to fetch the version plugin packages.
	 *
	 * @return Source
	 */
	abstract public function get_plugin_package_source();

	/**
	 * Gets the plugin slug.
	 *
	 * @return string
	 */
	abstract public function get_plugin_slug();

	/**
	 * Gets the current plugin version.
	 *
	 * @return string
	 */
	abstract public function get_current_version();

	/**
	 * Gets the plugin package for the current version.
	 *
	 * @return bool|Plugin_Package
	 */
	public function current_version_package() {
		$current_version = $this->get_current_version();
		if ( ! $current_version ) {
			return false;
		}

		foreach ( $this->get_versions() as $plugin_package ) {
			if ( $current_version === $plugin_package->get_version() ) {
				return $plugin_package;
			}
		}

		return false;
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new static();
		}
		return self::$instance;
	}

}
