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
