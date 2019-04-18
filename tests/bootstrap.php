<?php
/**
 * Bootstrap file for unit tests.
 *
 * @package sensei-lms-beta/Tests
 * @since   1.0.0
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
require_once __DIR__ . '/framework/class-base-test.php';

WP_Mock::bootstrap();
require_once __DIR__ . '/framework/core-mocks.php';

$plugin_dir = dirname( __DIR__ );
$updater_dir = $plugin_dir . '/includes/updater';

require_once $updater_dir . '/class-abstract-updater.php';
require_once $updater_dir . '/class-plugin-package.php';
require_once $updater_dir . '/class-plugin-upgrader.php';
require_once $updater_dir . '/sources/interface-source.php';
require_once $updater_dir . '/sources/class-abstract-source.php';
require_once $updater_dir . '/sources/class-github.php';

require_once $plugin_dir . '/sensei-lms-beta.php';
require_once $plugin_dir . '/includes/class-admin.php';
require_once $plugin_dir . '/includes/class-updater.php';

require_once __DIR__ . '/framework/class-updater-shim.php';
require_once __DIR__ . '/framework/class-source-shim.php';

\Sensei_LMS_Beta\Sensei_LMS_Beta::instance();
