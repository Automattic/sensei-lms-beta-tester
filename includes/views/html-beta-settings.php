<?php
/**
 * File containing the view for the beta testing settings.
 *
 * @package sensei-lms-beta
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	add_settings_error( 'sensei-lms-beta-messages', 'sensei-lms-beta-message', __( 'Settings Saved', 'sensei-lms-beta' ), 'updated' );
}

// show error/update messages.
settings_errors( 'sensei-lms-beta-messages' );

$current_version = \Sensei_LMS_Beta\Updater\Updater::instance()->current_version_package();
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="notice notice-info">
		<p>
			<?php
			echo wp_kses(
				// translators: placeholder is version of Sensei LMS currently installed.
				sprintf( __( 'You currently have <strong>Sensei LMS %s</strong> installed.', 'sensei-lms-beta' ), $current_version->get_version() ),
				[ 'strong' => [] ]
			)
			?>
			<?php esc_html_e( 'Please report any issues you encounter in pre-release versions of Sensei LMS to the GitHub repository.', 'sensei-lms-beta' ); ?>
		</p>
		<p>
			<?php
			if ( $current_version->get_release_info_url() ) {
				printf(
					'<a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> ',
					esc_url( $current_version->get_release_info_url() ),
					esc_html__( 'Release Information', 'sensei-lms-beta' ),
					/* translators: accessibility text */
					esc_html__( '(opens in a new tab)', 'sensei-lms-beta' )
				);
			}
			if ( $current_version->get_changelog_url() ) {
				printf(
					'<a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> ',
					esc_url( $current_version->get_changelog_url() ),
					esc_html__( 'View Changelog', 'sensei-lms-beta' ),
					/* translators: accessibility text */
					esc_html__( '(opens in a new tab)', 'sensei-lms-beta' )
				);
			}
			printf(
				'<a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> ',
				esc_url( \Sensei_LMS_Beta\Sensei_LMS_Beta::SENSEI_LMS_BETA_REPORT_ISSUE_URL ),
				esc_html__( 'Report Issue', 'sensei-lms-beta' ),
				/* translators: accessibility text */
				esc_html__( '(opens in a new tab)', 'sensei-lms-beta' )
			);
			?>

		</p>
	</div>

	<div class="postbox">
		<div class="inside" style="margin-bottom:0;">
			<form action="options.php" method="post">
				<?php

				settings_fields( 'sensei-lms-beta' );
				do_settings_sections( 'sensei-lms-beta' );
				submit_button();

				?>
			</form>
		</div>
	</div>

	<div class="postbox">
		<div class="inside" style="margin-bottom:0;">
			<h2><?php esc_html_e( 'Switch Sensei Version', 'sensei-lms-beta' ); ?></h2>
			<?php
			$confirm_message = esc_html__( 'Are you sure you want to switch your Sensei version? We do not recommend doing this on production sites. Back up first.', 'sensei-lms-beta' );
			?>
			<form action="plugins.php?page=sensei-lms-beta-tester" method="post" onsubmit="return confirm( '<?php echo esc_attr( $confirm_message ); ?>' );">
				<p><?php esc_html_e( 'Use this to manually switch to a particular version of Sensei LMS.', 'sensei-lms-beta' ); ?></p>
				<label>
					<select id="sensei-lms-beta-version-select" name="sensei_lms_beta_version_select">
						<?php
						foreach ( \Sensei_LMS_Beta\Updater\Updater::instance()->get_versions() as $package ) {
							echo sprintf( '<option name="%1$s">%1$s</option>', esc_html( $package->get_version() ) );
						}
						?>
					</select>
				</label>
				<br>
				<?php
				wp_nonce_field( 'switch-sensei-lms-version' );
				submit_button( esc_html__( 'Switch Version', 'sensei-lms-beta' ) );
				?>
			</form>
		</div>
	</div>
</div>
