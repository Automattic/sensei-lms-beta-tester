<?php
/**
 * File containing the interface \Sensei_LMS_Beta\Updater\Sources\Source.
 *
 * @package sensei-lms-beta
 * @since   1.0.0
 */

namespace Sensei_LMS_Beta\Updater\Sources;

use Sensei_LMS_Beta\Updater\Plugin_Package;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface for a version package source.
 *
 * @interface \Sensei_LMS_Beta\Updater\Sources\Source
 */
interface Source {
	/**
	 * Returns an array of plugin packages.
	 *
	 * @return bool|Plugin_Package[]
	 */
	public function get_versions();
}
