<?php
/**
 * File containing the class \Sensei_LMS_Beta\Admin.
 *
 * @package sensei-lms-beta
 * @since   1.0.0
 */

namespace Sensei_LMS_Beta;

use Sensei_LMS_Beta\Updater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing settings for the plugin.
 *
 * @class \Sensei_LMS_Beta\Admin
 */
final class Admin {
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
	}

	/**
	 * Adds all filters and actions.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$current_version = \Sensei_LMS_Beta\Updater::instance()->get_current_version_package();

		if ( false === $current_version ) {
			add_action(
				'plugin_row_meta',
				function( $plugin_meta, $plugin_file ) {
					if ( SENSEI_LMS_BETA_PLUGIN_BASENAME !== $plugin_file ) {
						return $plugin_meta;
					}

					$message       = '<span style="color: red; font-weight: bold;">';
					$message      .= esc_html__( 'Requires Sensei LMS to be installed and activated.', 'sensei-lms-beta' );
					$message      .= '</span>';
					$plugin_meta[] = $message;

					return $plugin_meta;
				},
				10,
				2
			);

			return;
		}

		add_action( 'admin_init', [ $this, 'init_settings' ] );
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'plugin_action_links_' . SENSEI_LMS_BETA_PLUGIN_BASENAME, [ $this, 'add_settings_link' ], 10, 2 );
		add_action( 'network_admin_plugin_action_links_' . SENSEI_LMS_BETA_PLUGIN_BASENAME, [ $this, 'add_settings_link' ], 10, 2 );
	}

	/**
	 * Get plugin settings.
	 *
	 * @return object
	 */
	public static function get_settings() {
		$settings              = (object) wp_parse_args(
			get_option( 'sensei_lms_beta_options', [] ),
			[
				'channel'     => 'beta',
				'auto_update' => false,
			]
		);
		$settings->auto_update = (bool) $settings->auto_update;

		return $settings;
	}

	/**
	 * Initialise settings
	 */
	public function init_settings() {
		register_setting( 'sensei-lms-beta', 'sensei_lms_beta_options' );

		add_settings_section(
			'sensei-lms-beta-update',
			__( 'Settings', 'sensei-lms-beta' ),
			[ $this, 'update_section_html' ],
			'sensei-lms-beta'
		);

		add_settings_field(
			'sensei-lms-beta-channel',
			__( 'Release Channel', 'sensei-lms-beta' ),
			[ $this, 'version_select_html' ],
			'sensei-lms-beta',
			'sensei-lms-beta-update',
			[
				'label_for' => 'channel',
			]
		);

		add_settings_field(
			'sensei-lms-beta-auto-update',
			__( 'Automatic Updates', 'sensei-lms-beta' ),
			[ $this, 'automatic_update_checkbox_html' ],
			'sensei-lms-beta',
			'sensei-lms-beta-update',
			[
				'label_for' => 'auto_update',
			]
		);
	}

	/**
	 * Update section HTML output.
	 *
	 * @param array $args Arguments.
	 */
	public function update_section_html( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'The following settings allow you to choose which Sensei updates to receive on this site, including beta and RC versions not quite ready for production deployment.', 'sensei-lms-beta' ); ?></p>
		<?php
	}

	/**
	 * Version select markup output.
	 *
	 * @param array $args Arguments.
	 */
	public function version_select_html( $args ) {
		$settings = self::get_settings();
		$channels = [
			Updater::CHANNEL_BETA   => [
				'name'        => __( 'Beta Releases', 'sensei-lms-beta' ),
				'description' => __( 'Beta releases contain experimental functionality for testing purposes only. This channel will also include RC and stable releases if more current.', 'sensei-lms-beta' ),
				'latest'      => Updater::instance()->get_latest_channel_release( Updater::CHANNEL_BETA ),
			],
			Updater::CHANNEL_RC     => [
				'name'        => __( 'Release Candidates', 'sensei-lms-beta' ),
				'description' => __( 'Release candidates are released to ensure any critical problems have not gone undetected. This channel will also include stable releases if more current.', 'sensei-lms-beta' ),
				'latest'      => Updater::instance()->get_latest_channel_release( Updater::CHANNEL_RC ),
			],
			Updater::CHANNEL_STABLE => [
				'name'        => __( 'Stable Releases', 'sensei-lms-beta' ),
				'description' => __( 'This is the default behavior in WordPress.', 'sensei-lms-beta' ),
				'latest'      => Updater::instance()->get_latest_channel_release( Updater::CHANNEL_STABLE ),
			],
		];
		echo '<fieldset><legend class="screen-reader-text"><span>' . esc_html__( 'Update Channel', 'sensei-lms-beta' ) . '</span></legend>';
		foreach ( $channels as $channel_id => $channel ) {
			?>
			<label>
				<input type="radio" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="sensei_lms_beta_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $channel_id ); ?>" <?php checked( $settings->{ $args['label_for'] }, $channel_id ); ?> />
				<?php
				$update_time = ( $channel['latest'] && $channel['latest']->get_release_date() )
					// translators: %s placeholder is relative time since last update.
					? sprintf( __( 'Last updated %s ago', 'sensei-lms-beta' ), human_time_diff( strtotime( $channel['latest']->get_release_date() ) ) )
					: false;
				?>
				<?php echo esc_html( $channel['name'] ); ?>
				<?php
				if ( $update_time ) {
					echo '<small>(' . esc_html( $update_time ) . ')</small>';
				}
				?>
				<p class="description">
					<?php echo esc_html( $channel['description'] ); ?>
				</p>
			</label>
			<br>
			<?php
		}
		echo '</fieldset>';
	}

	/**
	 * Auto updates checkbox markup output.
	 *
	 * @param array $args Arguments.
	 */
	public function automatic_update_checkbox_html( $args ) {
		$settings = self::get_settings();
		?>
		<label for="<?php echo esc_attr( $args['label_for'] ); ?>">
			<input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="sensei_lms_beta_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1" <?php checked( $settings->{ $args['label_for'] }, true ); ?> />
			<?php echo esc_html__( 'If enabled, Sensei will update to the latest release in the background. Use with caution; we do not recommend using this on production sites!', 'sensei-lms-beta' ); ?>
		</label>
		<?php
	}

	/**
	 * Adds the admin menu item under Plugins.
	 */
	public function add_admin_menu() {
		add_plugins_page( esc_html__( 'Sensei Beta Tester', 'sensei-lms-beta' ), esc_html__( 'Sensei Beta Tester', 'sensei-lms-beta' ), 'install_plugins', 'sensei-lms-beta-tester', [ $this, 'output_settings_page' ] );
	}

	/**
	 * Add link to settings page for this plugin.
	 *
	 * @param array  $actions     Actions to show in plugin list.
	 * @param string $plugin_file Plugin file currently being listed.
	 *
	 * @return mixed
	 */
	public function add_settings_link( $actions, $plugin_file ) {
		if ( SENSEI_LMS_BETA_PLUGIN_BASENAME !== $plugin_file ) {
			return $actions;
		}

		$new_actions             = [];
		$new_actions['settings'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'plugins.php?page=sensei-lms-beta-tester' ) ),
			esc_html__( 'Settings', 'sensei-lms-beta' )
		);

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Output the settings page content.
	 */
	public function output_settings_page() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		include __DIR__ . '/views/html-beta-settings.php';
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

}
