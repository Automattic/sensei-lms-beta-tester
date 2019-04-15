<?php
/**
 * File containing the class \Sensei_LMS_Beta\Updater.
 *
 * @package sensei-lms-beta
 * @since   1.0.0
 */

namespace Sensei_LMS_Beta;

use Sensei_LMS_Beta\Updater\Abstract_Updater;
use Sensei_LMS_Beta\Updater\Sources\Github;
use Sensei_LMS_Beta\Updater\Sources\Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing the update logic specific to this plugin.
 *
 * @class \Sensei_LMS_Beta\Updater
 */
final class Updater extends Abstract_Updater {
	/**
	 * Gets the source object to fetch the version plugin packages.
	 *
	 * @return Source
	 */
	public function get_plugin_package_source() {
		return new Github( $this->get_plugin_slug(), 'Automattic/sensei' );
	}

	/**
	 * Gets the plugin slug.
	 *
	 * @return string
	 */
	public function get_plugin_slug() {
		return 'woothemes-sensei';
	}

	/**
	 * Gets the plugin basename as installed.
	 *
	 * @return string
	 */
	public function get_installed_basename() {
		if ( function_exists( 'Sensei' ) && ! empty( Sensei()->plugin_path ) ) {
			return plugin_basename( Sensei()->plugin_path . '/woothemes-sensei.php' );
		}

		return false;
	}

	/**
	 * Gets the current plugin version.
	 *
	 * @return string
	 */
	public function get_current_version() {
		if ( function_exists( 'Sensei' ) ) {
			return Sensei()->version;
		}

		return false;
	}

	/**
	 * Get the basic configuration for the plugin.
	 *
	 * @return array
	 */
	protected function get_plugin_base_config() {
		return [
			'name'        => 'Sensei LMS',
			'plugin_name' => 'Sensei LMS',
			'author'      => 'Automattic',
			'homepage'    => 'https://senseilms.com',
			'plugin_file' => $this->get_installed_basename(),
			'slug'        => $this->get_plugin_slug(),
			'sections'    => [
				'description' => esc_html__( 'Share your knowledge, grow your network, and strengthen your brand by launching an online course.', 'sensei-lms-beta' ),
			],
		];
	}
}
