<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rcs_settings     = isset( $settings ) ? $settings : array();
$rcs_types        = isset( $types ) ? $types : array();
$rcs_pattern_ids  = isset( $pattern_ids ) ? $pattern_ids : array();
$rcs_parameter_map = isset( $parameter_map ) ? $parameter_map : array();
$rcs_records      = isset( $records ) ? $records : array();
$rcs_counts       = isset( $counts ) ? $counts : array();
$rcs_saved        = isset( $saved ) ? (int) $saved : 0;
$rcs_retried      = isset( $retried ) ? (int) $retried : 0;
$rcs_api_key      = isset( $rcs_settings['api_key'] ) ? $rcs_settings['api_key'] : '';
$rcs_sender_line  = isset( $rcs_settings['sender_line'] ) ? $rcs_settings['sender_line'] : '';
$rcs_admin_numbers = isset( $rcs_settings['admin_numbers'] ) && is_array( $rcs_settings['admin_numbers'] ) ? implode( "\n", $rcs_settings['admin_numbers'] ) : '';
?>
<div class="wrap rcs-sms">
	<h1><?php esc_html_e( 'SMS', 'request-coupon-system' ); ?></h1>

	<?php if ( $rcs_saved ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'The SMS settings were saved.', 'request-coupon-system' ); ?></p></div>
	<?php endif; ?>

	<?php if ( $rcs_retried ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'The message was queued for a new attempt.', 'request-coupon-system' ); ?></p></div>
	<?php endif; ?>

	<h2><?php esc_html_e( 'SMS.ir connection', 'request-coupon-system' ); ?></h2>

	<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => RCS_Admin_Admin::MENU_SLUG ), admin_url( 'admin.php' ) ) ); ?>">
		<?php wp_nonce_field( RCS_Admin_Pages_SmsPage::NONCE_SAVE ); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="rcs-api-key"><?php esc_html_e( 'API key', 'request-coupon-system' ); ?></label></th>
					<td>
						<input type="text" id="rcs-api-key" name="rcs_api_key" class="regular-text" value="<?php echo esc_attr( $rcs_api_key ); ?>" autocomplete="off" />
						<p class="description"><?php esc_html_e( 'The API key of the SMS.ir developer panel. A Sandbox key can be used for testing.', 'request-coupon-system' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rcs-sender-line"><?php esc_html_e( 'Sender line', 'request-coupon-system' ); ?></label></th>
					<td>
						<input type="text" id="rcs-sender-line" name="rcs_sender_line" class="regular-text" value="<?php echo esc_attr( $rcs_sender_line ); ?>" autocomplete="off" />
						<p class="description"><?php esc_html_e( 'Optional. The pattern messages are sent from the service line registered in the SMS.ir panel, so this field can stay empty.', 'request-coupon-system' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rcs-admin-numbers"><?php esc_html_e( 'Administrator numbers', 'request-coupon-system' ); ?></label></th>
					<td>
						<textarea id="rcs-admin-numbers" name="rcs_admin_numbers" rows="4" class="regular-text"><?php echo esc_textarea( $rcs_admin_numbers ); ?></textarea>
						<p class="description"><?php esc_html_e( 'One number per line. Every number receives its own message record.', 'request-coupon-system' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Message patterns', 'request-coupon-system' ); ?></h2>
		<p class="description"><?php esc_html_e( 'The patterns are defined in the SMS.ir panel. The message text is never stored in the plugin, only the pattern identifier and the parameter names.', 'request-coupon-system' ); ?></p>

		<table class="widefat striped rcs-sms-patterns">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Message type', 'request-coupon-system' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Pattern ID', 'request-coupon-system' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Parameter names', 'request-coupon-system' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rcs_types as $rcs_type => $rcs_type_label ) : ?>
					<?php
					$rcs_pattern_id = isset( $rcs_pattern_ids[ $rcs_type ] ) ? (int) $rcs_pattern_ids[ $rcs_type ] : 0;
					$rcs_names      = isset( $rcs_parameter_map[ $rcs_type ] ) ? array_keys( $rcs_parameter_map[ $rcs_type ] ) : array();
					?>
					<tr>
						<td><?php echo esc_html( $rcs_type_label ); ?><br /><code><?php echo esc_html( $rcs_type ); ?></code></td>
						<td><input type="number" min="0" name="rcs_pattern_<?php echo esc_attr( $rcs_type ); ?>" value="<?php echo esc_attr( (string) $rcs_pattern_id ); ?>" /></td>
						<td>
							<input type="text" name="rcs_parameters_<?php echo esc_attr( $rcs_type ); ?>" class="regular-text" value="<?php echo esc_attr( implode( ',', $rcs_names ) ); ?>" />
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save SMS settings', 'request-coupon-system' ); ?></button></p>
	</form>

	<h2><?php esc_html_e( 'Message history', 'request-coupon-system' ); ?></h2>

	<p>
		<?php
		printf(
			/* translators: 1: sent count, 2: failed count, 3: pending count. */
			esc_html__( 'Sent: %1$s — Failed: %2$s — Pending: %3$s', 'request-coupon-system' ),
			esc_html( (string) $rcs_counts['sent'] ),
			esc_html( (string) $rcs_counts['failed'] ),
			esc_html( (string) $rcs_counts['pending'] )
		);
		?>
	</p>

	<table class="wp-list-table widefat fixed striped rcs-sms-history">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'ID', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Type', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Recipient', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Retries', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Created', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Actions', 'request-coupon-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rcs_records ) ) : ?>
				<tr>
					<td colspan="7"><?php esc_html_e( 'No message has been sent yet.', 'request-coupon-system' ); ?></td>
				</tr>
			<?php endif; ?>

			<?php foreach ( $rcs_records as $rcs_record ) : ?>
				<?php
				$rcs_retry_url = add_query_arg(
					array(
						'page'           => RCS_Admin_Admin::MENU_SLUG,
						'rcs_sms_action' => 'retry',
						'rcs_sms_id'     => (int) $rcs_record['id'],
						'_wpnonce'       => wp_create_nonce( RCS_Admin_Pages_SmsPage::NONCE_RETRY . '_' . (int) $rcs_record['id'] ),
					),
					admin_url( 'admin.php' )
				);
				?>
				<tr>
					<td><?php echo esc_html( (string) $rcs_record['id'] ); ?></td>
					<td><?php echo esc_html( $rcs_record['sms_type'] ); ?></td>
					<td><?php echo esc_html( $rcs_record['recipient'] ); ?></td>
					<td><?php echo esc_html( $rcs_record['status'] ); ?></td>
					<td><?php echo esc_html( (string) $rcs_record['retry_count'] ); ?></td>
					<td><?php echo esc_html( $rcs_record['created_at'] ); ?></td>
					<td>
						<?php if ( RCS_SMS_SmsStatus::FAILED === $rcs_record['status'] ) : ?>
							<a href="<?php echo esc_url( $rcs_retry_url ); ?>"><?php esc_html_e( 'Retry', 'request-coupon-system' ); ?></a>
						<?php else : ?>
							<span aria-hidden="true">&mdash;</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
