<?php
/**
 * File containing the class \Sensei_LMS_Beta\Updater\Updater_Base.
 *
 * @package sensei-lms-beta
 * @since   1.0.0
 */

namespace Sensei_LMS_Beta\Updater;

use Sensei_LMS_Beta\Updater\Sources\Github;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing the update logic specific to this plugin.
 *
 * @class \Sensei_LMS_Beta\Updater\Updater
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
		return 'sensei-lms';
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
}
