<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rcs_sms_settings = get_option( 'rcs_sms_settings', array() );
$rcs_api_key      = isset( $rcs_sms_settings['api_key'] ) ? $rcs_sms_settings['api_key'] : '';
$rcs_sender_line  = isset( $rcs_sms_settings['sender_line'] ) ? $rcs_sms_settings['sender_line'] : '';
$rcs_admin_numbers = isset( $rcs_sms_settings['admin_numbers'] ) ? $rcs_sms_settings['admin_numbers'] : array();
$rcs_admin_numbers_text = is_array( $rcs_admin_numbers ) ? implode( "\n", $rcs_admin_numbers ) : '';

$rcs_configured = RCS_Integrations_SMSIr_SmsIrClient::instance()->is_configured();
$rcs_saved      = isset( $_GET['rcs_saved'] ) ? (int) $_GET['rcs_saved'] : 0;

?>
<div class="wrap rcs-settings">
	<h1><?php esc_html_e( 'Settings', 'request-coupon-system' ); ?></h1>

	<?php if ( $rcs_saved ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'SMS settings were saved.', 'request-coupon-system' ); ?></p></div>
	<?php elseif ( $rcs_configured ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'SMS.ir is configured and ready to use.', 'request-coupon-system' ); ?></p></div>
	<?php else : ?>
		<div class="notice notice-warning is-dismissible"><p><?php esc_html_e( 'SMS.ir API key is not configured. SMS messages will not be sent until you enter a valid API key.', 'request-coupon-system' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . RCS_Admin_Pages_SettingsPage::SLUG ) ); ?>">
		<?php wp_nonce_field( 'rcs_save_sms_settings', 'rcs_sms_settings_nonce' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'API Key', 'request-coupon-system' ); ?></th>
				<td>
					<input type="text" name="rcs_sms_api_key" value="<?php echo esc_attr( $rcs_api_key ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Your SMS.ir developer panel API key. A Sandbox key can be used for testing.', 'request-coupon-system' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Sender Line', 'request-coupon-system' ); ?></th>
				<td>
					<input type="text" name="rcs_sms_sender_line" value="<?php echo esc_attr( $rcs_sender_line ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Optional. SMS messages are sent from the service line registered in your SMS.ir panel, so this field can stay empty.', 'request-coupon-system' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Administrator Numbers', 'request-coupon-system' ); ?></th>
				<td>
					<textarea name="rcs_sms_admin_numbers" rows="5" class="large-text"><?php echo esc_textarea( $rcs_admin_numbers_text ); ?></textarea>
					<p class="description"><?php esc_html_e( 'One number per line. Every number receives its own copy of admin notifications.', 'request-coupon-system' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Save SMS Settings', 'request-coupon-system' ) ); ?>
	</form>
</div>
