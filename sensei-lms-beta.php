<?php
/**
 * Plugin Name: Sensei LMS Beta Tester
 * Plugin URI: https://senseilms.com/
 * Description: Help us test upcoming versions of Sensei LMS. Warning: Do not use on production sites!
 * Version: 1.0.0-dev
 * Tested up to: 5.0
 * Requires PHP: 5.6
 * Author: Automattic
 * Author URI: https://senseilms.com/
 * Text Domain: sensei-lms-beta
 * Domain Path: /languages/
 *
 * @package sensei-lms-beta
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SENSEI_LMS_BETA_VERSION', '1.0.0-dev' );
define( 'SENSEI_LMS_BETA_PLUGIN_FILE', __FILE__ );
define( 'SENSEI_LMS_BETA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( version_compare( phpversion(), '5.6.0', '<' ) ) {
	// translators: %1$s placeholder is minimum PHP version; %2$s is the version of PHP they have installed.
	die( esc_html( sprintf( __( 'Sensei LMS Beta Tester requires a minimum PHP version of %1$s, but you are running %2$s.', 'sensei-lms-beta' ), '5.6.0', phpversion() ) ) );
}

// Include deprecated functions.
require_once dirname( __FILE__ ) . '/includes/class-sensei-lms-beta.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', array( 'Sensei_LMS_Beta\Sensei_LMS_Beta', 'init' ), 5 );

Sensei_LMS_Beta\Sensei_LMS_Beta::instance();

