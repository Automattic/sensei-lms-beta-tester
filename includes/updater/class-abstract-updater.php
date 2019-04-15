<?php
/**
 * File containing the class \Sensei_LMS_Beta\Updater_Base.
 *
 * @package sensei-lms-beta
 * @since   1.0.0
 */

namespace Sensei_LMS_Beta\Updater;

use http\Exception;
use Sensei_LMS_Beta\Admin\Plugin_Package;
use Sensei_LMS_Beta\Updater\Sources\Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing the shared update logic.
 *
 * @class \Sensei_LMS_Beta\Updater_Base
 */
abstract class Abstract_Updater {
	const CHANNEL_BETA   = 'beta';
	const CHANNEL_RC     = 'rc';
	const CHANNEL_STABLE = 'stable';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Current channel that we're currently set to follow.
	 *
	 * @var string
	 */
	private $channel;

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
	 *
	 * @param string $channel Current channel (beta, rc, stable).
	 */
	public function init( $channel ) {
		$this->channel = $channel;

		// If a recognized copy of the plugin is not installed, we don't want to load our fancy overrides.
		if ( ! $this->get_current_version_package() ) {
			return;
		}
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
	 * Get the latest channel release for a particular channel.
	 *
	 * @param string|null $channel Channel to get latest release for. Null for the current channel.
	 * @return bool|mixed|Plugin_Package
	 */
	public function get_latest_channel_release( $channel = null ) {
		if ( null === $channel ) {
			$channel = $this->channel;
		}

		switch ( $channel ) {
			case 'beta':
				$releases = $this->get_beta_channel();
				break;
			case 'rc':
				$releases = $this->get_rc_channel();
				break;
			case 'stable':
				$releases = $this->get_stable_channel();
				break;
			default:
				return false;
		}

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
	public function get_current_version_package() {
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
	 * Get the current channel.
	 *
	 * @return string
	 */
	public function get_channel() {
		return $this->channel;
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
