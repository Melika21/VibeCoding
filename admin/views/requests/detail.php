<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rcs_request     = isset( $request ) ? $request : array();
$rcs_request_id  = isset( $rcs_request['request_id'] ) ? (int) $rcs_request['request_id'] : 0;
$rcs_form        = isset( $form ) ? $form : null;
$rcs_fields      = isset( $fields ) ? $fields : array();
$rcs_owner       = isset( $owner ) ? $owner : null;
$rcs_revision    = isset( $revision ) ? $revision : null;
$rcs_history     = isset( $history ) ? $history : array();
$rcs_coupon      = isset( $coupon ) ? $coupon : null;
$rcs_mobile      = isset( $mobile ) ? $mobile : '';
$rcs_statuses    = isset( $statuses ) ? $statuses : array();
$rcs_can_approve = isset( $can_approve ) ? (bool) $can_approve : false;
$rcs_can_reject  = isset( $can_reject ) ? (bool) $can_reject : false;
$rcs_notice      = isset( $notice ) ? $notice : null;
$rcs_approved    = isset( $_GET['rcs_approved'] ) ? (int) $_GET['rcs_approved'] : 0;
$rcs_rejected    = isset( $_GET['rcs_rejected'] ) ? (int) $_GET['rcs_rejected'] : 0;
$rcs_back_url    = add_query_arg( array( 'page' => RCS_Admin_Pages_RequestsPage::SLUG ), admin_url( 'admin.php' ) );
$rcs_data        = $rcs_revision && ! empty( $rcs_revision['data'] ) ? $rcs_revision['data'] : array();
$rcs_status_key  = isset( $rcs_request['status'] ) ? $rcs_request['status'] : '';
$rcs_status_label = isset( $rcs_statuses[ $rcs_status_key ] ) ? $rcs_statuses[ $rcs_status_key ] : $rcs_status_key;
$rcs_labels      = array();

foreach ( $rcs_fields as $rcs_field ) {
	if ( isset( $rcs_field['field_key'] ) && '' !== $rcs_field['field_key'] ) {
		$rcs_labels[ $rcs_field['field_key'] ] = isset( $rcs_field['label'] ) ? $rcs_field['label'] : $rcs_field['field_key'];
	}
}
?>
<div class="wrap rcs-request-detail">
	<h1>
		<?php
		printf(
			/* translators: %s: request id. */
			esc_html__( 'Request #%s', 'request-coupon-system' ),
			esc_html( (string) $rcs_request_id )
		);
		?>
	</h1>

	<p>
		<a href="<?php echo esc_url( $rcs_back_url ); ?>">&larr; <?php esc_html_e( 'Back to requests', 'request-coupon-system' ); ?></a>
	</p>

	<?php if ( $rcs_notice && 'error' === $rcs_notice['type'] ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $rcs_notice['message'] ); ?></p></div>
	<?php endif; ?>

	<?php if ( $rcs_approved ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'The request was approved and the coupon was issued.', 'request-coupon-system' ); ?></p></div>
	<?php endif; ?>

	<?php if ( $rcs_rejected ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'The request was rejected.', 'request-coupon-system' ); ?></p></div>
	<?php endif; ?>

	<div class="rcs-request-columns">
		<div class="rcs-request-main">
			<h2><?php esc_html_e( 'Submitted data', 'request-coupon-system' ); ?></h2>

			<?php if ( empty( $rcs_data ) ) : ?>
				<p class="description"><?php esc_html_e( 'No data was submitted with this request.', 'request-coupon-system' ); ?></p>
			<?php else : ?>
				<table class="widefat striped rcs-request-data">
					<tbody>
						<?php foreach ( $rcs_data as $rcs_key => $rcs_value ) : ?>
							<?php
							$rcs_label = isset( $rcs_labels[ $rcs_key ] ) ? $rcs_labels[ $rcs_key ] : $rcs_key;

							if ( is_array( $rcs_value ) ) {
								$rcs_value = implode( ', ', array_map( 'strval', $rcs_value ) );
							}
							?>
							<tr>
								<th scope="row"><?php echo esc_html( $rcs_label ); ?></th>
								<td><?php echo esc_html( (string) $rcs_value ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Revision history', 'request-coupon-system' ); ?></h2>

			<?php if ( empty( $rcs_history ) ) : ?>
				<p class="description"><?php esc_html_e( 'No data was submitted with this request.', 'request-coupon-system' ); ?></p>
			<?php else : ?>
				<table class="widefat striped rcs-request-revisions">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Revision', 'request-coupon-system' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'request-coupon-system' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Submitted by', 'request-coupon-system' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Submitted at', 'request-coupon-system' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Reason', 'request-coupon-system' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rcs_history as $rcs_item ) : ?>
							<?php
							$rcs_item_status = isset( $rcs_item['status'] ) ? $rcs_item['status'] : '';
							$rcs_item_label  = isset( $rcs_statuses[ $rcs_item_status ] ) ? $rcs_statuses[ $rcs_item_status ] : $rcs_item_status;
							$rcs_submitter   = get_userdata( (int) $rcs_item['submitted_by'] );
							?>
							<tr>
								<td><?php echo esc_html( (string) $rcs_item['revision_number'] ); ?></td>
								<td><?php echo esc_html( $rcs_item_label ); ?></td>
								<td><?php echo esc_html( $rcs_submitter ? $rcs_submitter->display_name : '' ); ?></td>
								<td><?php echo esc_html( isset( $rcs_item['submitted_at'] ) ? $rcs_item['submitted_at'] : '' ); ?></td>
								<td><?php echo esc_html( isset( $rcs_item['rejection_reason'] ) ? $rcs_item['rejection_reason'] : '' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="rcs-request-side">
			<h2><?php esc_html_e( 'Request details', 'request-coupon-system' ); ?></h2>

			<table class="widefat striped rcs-request-meta">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'ID', 'request-coupon-system' ); ?></th>
						<td><?php echo esc_html( (string) $rcs_request_id ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Form', 'request-coupon-system' ); ?></th>
						<td><?php echo esc_html( $rcs_form ? $rcs_form['name'] : '' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'User', 'request-coupon-system' ); ?></th>
						<td>
							<?php echo esc_html( $rcs_owner ? $rcs_owner->display_name : '' ); ?>
							<?php if ( $rcs_owner ) : ?>
								<br /><small><?php echo esc_html( $rcs_owner->user_email ); ?></small>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Status', 'request-coupon-system' ); ?></th>
						<td><span class="rcs-status rcs-status-<?php echo esc_attr( $rcs_status_key ); ?>"><?php echo esc_html( $rcs_status_label ); ?></span></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Created', 'request-coupon-system' ); ?></th>
						<td><?php echo esc_html( isset( $rcs_request['created_at'] ) ? $rcs_request['created_at'] : '' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Updated', 'request-coupon-system' ); ?></th>
						<td><?php echo esc_html( isset( $rcs_request['updated_at'] ) ? $rcs_request['updated_at'] : '' ); ?></td>
					</tr>
					<?php if ( ! empty( $rcs_request['approved_at'] ) ) : ?>
						<tr>
							<th scope="row"><?php esc_html_e( 'Approved', 'request-coupon-system' ); ?></th>
							<td><?php echo esc_html( $rcs_request['approved_at'] ); ?></td>
						</tr>
					<?php endif; ?>
					<?php if ( ! empty( $rcs_request['rejected_at'] ) ) : ?>
						<tr>
							<th scope="row"><?php esc_html_e( 'Rejected', 'request-coupon-system' ); ?></th>
							<td><?php echo esc_html( $rcs_request['rejected_at'] ); ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Coupon code', 'request-coupon-system' ); ?></th>
						<td><?php echo esc_html( $rcs_coupon ? $rcs_coupon['coupon_code'] : '' ); ?></td>
					</tr>
				</tbody>
			</table>

			<?php if ( $rcs_can_approve ) : ?>
				<h2><?php esc_html_e( 'Approve request', 'request-coupon-system' ); ?></h2>

				<form method="post" action="<?php echo esc_url( $rcs_back_url ); ?>" class="rcs-request-approve">
					<input type="hidden" name="rcs_request_action" value="approve" />
					<input type="hidden" name="rcs_request_id" value="<?php echo esc_attr( (string) $rcs_request_id ); ?>" />
					<?php wp_nonce_field( RCS_Admin_Pages_RequestsPage::NONCE_APPROVE . '_' . $rcs_request_id ); ?>

					<p>
						<label for="rcs-discount"><strong><?php esc_html_e( 'Discount percentage', 'request-coupon-system' ); ?></strong></label><br />
						<input type="number" id="rcs-discount" name="rcs_discount" min="0" max="100" step="0.01" value="<?php echo esc_attr( '10' ); ?>" class="small-text" />
					</p>

					<p>
						<label for="rcs-maximum-amount"><strong><?php esc_html_e( 'Maximum discount amount', 'request-coupon-system' ); ?></strong></label><br />
						<input type="text" id="rcs-maximum-amount" name="rcs_maximum_amount" value="" class="regular-text" />
					</p>

					<p>
						<label for="rcs-customer-mobile"><strong><?php esc_html_e( 'Customer mobile number', 'request-coupon-system' ); ?></strong></label><br />
						<input type="text" id="rcs-customer-mobile" name="rcs_customer_mobile" value="<?php echo esc_attr( $rcs_mobile ); ?>" class="regular-text" autocomplete="off" />
					</p>

					<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Approve and issue the coupon', 'request-coupon-system' ); ?></button></p>
				</form>
			<?php endif; ?>

			<?php if ( $rcs_can_reject ) : ?>
				<h2><?php esc_html_e( 'Reject request', 'request-coupon-system' ); ?></h2>

				<form method="post" action="<?php echo esc_url( $rcs_back_url ); ?>" class="rcs-request-reject">
					<input type="hidden" name="rcs_request_action" value="reject" />
					<input type="hidden" name="rcs_request_id" value="<?php echo esc_attr( (string) $rcs_request_id ); ?>" />
					<?php wp_nonce_field( RCS_Admin_Pages_RequestsPage::NONCE_REJECT . '_' . $rcs_request_id ); ?>

					<p>
						<label for="rcs-rejection-reason"><strong><?php esc_html_e( 'Reason for rejection', 'request-coupon-system' ); ?></strong></label><br />
						<textarea id="rcs-rejection-reason" name="rcs_rejection_reason" rows="4" class="large-text"></textarea>
					</p>

					<p><button type="submit" class="button"><?php esc_html_e( 'Reject the request', 'request-coupon-system' ); ?></button></p>
				</form>
			<?php endif; ?>
		</div>
	</div>
</div>
