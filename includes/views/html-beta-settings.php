<?php
/**
 * File containing the view for the beta testing settings.
 *
 * @package sensei-lms-beta-tester
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	add_settings_error( 'sensei-lms-beta-tester-messages', 'sensei-lms-beta-tester-message', __( 'Settings Saved', 'sensei-lms-beta-tester' ), 'updated' );
}

$switch_version_result = \Sensei_LMS_Beta_Tester\Admin::instance()->get_destroy_switch_version_result();
if ( $switch_version_result ) {
	if ( ! empty( $switch_version_result['result'] ) ) {
		echo '<div class="notice notice-success"><p>';
		// translators: placeholder is the version that was just switched to.
		$message = sprintf( __( '<strong>Sensei LMS</strong> was successfully switched to version <strong>%s</strong>.', 'sensei-lms-beta-tester' ), $switch_version_result['new_version'] );
		echo wp_kses( $message, [ 'strong' => [] ] );
		echo '</p></div>';
	} else {
		echo '<div class="error"><p>';
		// translators: %1$s is the version that was being switched to; %2$s is the error message that was passed back.
		$message = sprintf( __( 'An error occurred while switching <strong>Sensei LMS</strong> to version <strong>%1$s</strong>: %2$s', 'sensei-lms-beta-tester' ), $switch_version_result['new_version'], $switch_version_result['error_message'] );
		echo wp_kses( $message, [ 'strong' => [] ] );
		echo '</p></div>';
	}
}

// show error/update messages.
settings_errors( 'sensei-lms-beta-tester-messages' );

$current_version = \Sensei_LMS_Beta_Tester\Updater::instance()->get_current_version_package();
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="notice notice-info">
		<p>
			<?php
			echo wp_kses(
				// translators: placeholder is version of Sensei LMS currently installed.
				sprintf( __( 'You currently have <strong>Sensei LMS %s</strong> installed.', 'sensei-lms-beta-tester' ), $current_version->get_version() ),
				[ 'strong' => [] ]
			)
			?>
			<?php esc_html_e( 'Please report any issues you encounter in pre-release versions of Sensei LMS to the GitHub repository.', 'sensei-lms-beta-tester' ); ?>
		</p>
		<p>
			<?php
			if ( $current_version->get_release_info_url() ) {
				printf(
					'<a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> ',
					esc_url( $current_version->get_release_info_url() ),
					esc_html__( 'Release Information', 'sensei-lms-beta-tester' ),
					/* translators: accessibility text */
					esc_html__( '(opens in a new tab)', 'sensei-lms-beta-tester' )
				);
			}
			if ( $current_version->get_changelog_url() ) {
				printf(
					'<a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> ',
					esc_url( $current_version->get_changelog_url() ),
					esc_html__( 'View Changelog', 'sensei-lms-beta-tester' ),
					/* translators: accessibility text */
					esc_html__( '(opens in a new tab)', 'sensei-lms-beta-tester' )
				);
			}
			printf(
				'<a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> ',
				esc_url( \Sensei_LMS_Beta_Tester\Sensei_LMS_Beta_Tester::SENSEI_LMS_BETA_REPORT_ISSUE_URL ),
				esc_html__( 'Report Issue', 'sensei-lms-beta-tester' ),
				/* translators: accessibility text */
				esc_html__( '(opens in a new tab)', 'sensei-lms-beta-tester' )
			);
			?>

		</p>
	</div>

	<div class="postbox">
		<div class="inside" style="margin-bottom:0;">
			<form action="options.php" method="post">
				<?php

				settings_fields( 'sensei-lms-beta-tester' );
				do_settings_sections( 'sensei-lms-beta-tester' );
				submit_button();

				?>
			</form>
		</div>
	</div>

	<div class="postbox">
		<div class="inside" style="margin-bottom:0;">
			<h2><?php esc_html_e( 'Switch Version', 'sensei-lms-beta-tester' ); ?></h2>
			<?php
			$confirm_message = esc_html__( 'Are you sure you want to switch your Sensei LMS version? We do not recommend doing this on production sites. Back up first.', 'sensei-lms-beta-tester' );
			?>
			<form action="plugins.php?page=sensei-lms-beta-tester-tester" method="post" onsubmit="return confirm( '<?php echo esc_attr( $confirm_message ); ?>' );">
				<p><?php esc_html_e( 'Use this to manually switch to a particular version of Sensei LMS.', 'sensei-lms-beta-tester' ); ?></p>
				<label>
					<select id="sensei-lms-beta-tester-version-select" name="sensei_lms_beta_version_select">
						<?php
						foreach ( \Sensei_LMS_Beta_Tester\Updater::instance()->get_beta_channel() as $package ) {
							echo sprintf( '<option name="%1$s">%1$s</option>', esc_html( $package->get_version() ) );
						}
						?>
					</select>
				</label>
				<br>
				<?php
				wp_nonce_field( 'switch-sensei-lms-version' );
				submit_button( esc_html__( 'Switch Version', 'sensei-lms-beta-tester' ) );
				?>
			</form>
		</div>
	</div>
</div>
