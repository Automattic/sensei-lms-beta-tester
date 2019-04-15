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
		if ( ! in_array( $channel, [ self::CHANNEL_STABLE, self::CHANNEL_BETA, self::CHANNEL_RC ], true ) ) {
			$channel = self::CHANNEL_STABLE;
		}
		$this->channel = $channel;

		// If a recognized copy of the plugin is not installed, we don't want to load our fancy overrides.
		if ( ! $this->get_current_version_package() ) {
			return;
		}

		if ( self::CHANNEL_STABLE !== $this->get_channel() ) {
			// If we aren't on the stable channel, override the update checks.
			add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'api_check' ] );
			add_filter( 'plugins_api', [ $this, 'plugins_api' ], 10, 3 );
		}
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
	 * Gets the plugin basename as installed.
	 *
	 * @return string
	 */
	abstract public function get_installed_basename();

	/**
	 * Gets the current plugin version.
	 *
	 * @return string
	 */
	abstract public function get_current_version();

	/**
	 * Get the basic configuration for the plugin.
	 *
	 * @return array
	 */
	abstract protected function get_plugin_base_config();

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
	 * Hook into the plugin update check and connect to WPorg.
	 *
	 * @since 1.0
	 * @param object $transient The plugin data transient.
	 * @return object $transient Updated plugin data transient.
	 */
	public function api_check( $transient ) {
		$new_version_package = $this->get_latest_channel_release();

		if ( ! $new_version_package ) {
			return $transient;
		}

		// check the version and decide if it's new.
		$update = version_compare( $new_version_package->get_version(), $this->get_current_version(), '>' );

		if ( ! $update ) {
			return $transient;
		}

		$plugin_basename = $this->get_installed_basename();

		// Populate response data.
		if ( ! isset( $transient->response[ $plugin_basename ] ) ) {
			$transient->response[ $plugin_basename ] = (object) $this->get_plugin_base_config();
		}

		$transient->response[ $plugin_basename ]->new_version = $new_version_package->get_version();
		$transient->response[ $plugin_basename ]->zip_url     = $new_version_package->get_download_package_url();
		$transient->response[ $plugin_basename ]->package     = $new_version_package->get_download_package_url();
		unset( $transient->no_update[ $plugin_basename ] );

		return $transient;
	}

	/**
	 * Filters the Plugin Installation API response results.
	 *
	 * @param false|object|array $response The result object or array. Default false.
	 * @param string             $action   The type of information being requested from the Plugin Installation API.
	 * @param object             $args     Plugin API arguments.
	 * @return object|bool
	 */
	public function plugins_api( $response, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $response;
		}

		// Check if this call API is for the right plugin.
		if ( ! isset( $args->slug ) || $args->slug !== $this->get_plugin_slug() ) {
			return $response;
		}

		$new_version_package = $this->get_latest_channel_release();

		if ( ! version_compare( $new_version_package->get_version(), $this->get_current_version(), '>' ) ) {
			return $response;
		}

		$response = (object) $this->get_plugin_base_config();
		$warning  = '';

		if ( $new_version_package->is_beta() ) {
			$warning = __( '<h1><span>&#9888;</span>This is a beta release<span>&#9888;</span></h1>', 'sensei-lms-beta' );
		}

		if ( ! $new_version_package->is_stable() ) {
			$warning = __( '<h1><span>&#9888;</span>This is a pre-release version<span>&#9888;</span></h1>', 'sensei-lms-beta' );
		}

		// If we are returning a different version than the stable tag on .org, manipulate the returned data.
		$response->version       = $new_version_package->get_version();
		$response->download_link = $new_version_package->get_download_package_url();

		if ( ! isset( $response->sections ) ) {
			$response->sections = [];
		}
		$response->sections['changelog'] = sprintf(
			'<p><a target="_blank" href="%s">' . esc_html__( 'Read the changelog and find out more about the release on GitHub.', 'sensei-lms-beta' ) . '</a></p>',
			$new_version_package->get_changelog_url()
		);

		foreach ( $response->sections as $key => $section ) {
			$response->sections[ $key ] = wp_kses_post( $warning . $section );
		}

		return $response;
	}

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
