<?php
/**
 * File containing the class \Sensei_LMS_Beta\Tests\Framework\Base_Test.
 *
 * @package sensei-lms-beta/Tests
 * @since   1.0.0
 */

namespace Sensei_LMS_Beta\Tests\Framework;

use Sensei_LMS_Beta\Updater;

/**
 * Class containing base test.
 *
 * @class \Sensei_LMS_Beta\Tests\Framework\Base_Test
 */
class Base_Test extends \WP_Mock\Tools\TestCase {
	public function setUp() : void {
		\WP_Mock::setUp();
	}

	public function tearDown() : void {
		\WP_Mock::tearDown();
	}

}
