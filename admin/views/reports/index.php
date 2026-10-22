<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$rcs_requests_table = RCS_Database_Schema::requests();
$rcs_coupons_table  = RCS_Database_Schema::coupons();
$rcs_sms_table      = RCS_Database_Schema::sms();
$rcs_logs_table     = RCS_Database_Schema::logs();

// Request counts by status.
$rcs_request_counts = array();
foreach ( RCS_Requests_RequestStatus::all() as $rcs_status ) {
	$rcs_request_counts[ $rcs_status ] = (int) $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(*) FROM {$rcs_requests_table} WHERE status = %s", $rcs_status )
	);
}
$rcs_total_requests = array_sum( $rcs_request_counts );

// Coupon counts.
$rcs_total_coupons   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$rcs_coupons_table}" );
$rcs_active_coupons  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$rcs_coupons_table} WHERE status = 'active'" );
$rcs_disabled_coupons = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$rcs_coupons_table} WHERE status = 'disabled'" );

// SMS counts by status.
$rcs_sms_sent     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$rcs_sms_table} WHERE status = %s", RCS_SMS_SmsStatus::SENT ) );
$rcs_sms_failed   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$rcs_sms_table} WHERE status = %s", RCS_SMS_SmsStatus::FAILED ) );
$rcs_sms_pending  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$rcs_sms_table} WHERE status = %s", RCS_SMS_SmsStatus::PENDING ) );
$rcs_total_sms    = $rcs_sms_sent + $rcs_sms_failed + $rcs_sms_pending;

// Recent activity logs.
$rcs_recent_logs = $wpdb->get_results(
	"SELECT * FROM {$rcs_logs_table} ORDER BY created_at DESC LIMIT 10",
	ARRAY_A
);

?>
<div class="wrap rcs-reports">
	<h1><?php esc_html_e( 'Reports', 'request-coupon-system' ); ?></h1>

	<p class="description"><?php esc_html_e( 'Summary of requests, coupons, SMS messages and recent activity.', 'request-coupon-system' ); ?></p>

	<div class="rcs-report-cards">
		<div class="rcs-report-card">
			<h2><?php echo esc_html( (string) $rcs_total_requests ); ?></h2>
			<p><?php esc_html_e( 'Total Requests', 'request-coupon-system' ); ?></p>
		</div>
		<div class="rcs-report-card">
			<h2><?php echo esc_html( (string) $rcs_total_coupons ); ?></h2>
			<p><?php esc_html_e( 'Coupons Issued', 'request-coupon-system' ); ?></p>
		</div>
		<div class="rcs-report-card">
			<h2><?php echo esc_html( (string) $rcs_total_sms ); ?></h2>
			<p><?php esc_html_e( 'SMS Messages', 'request-coupon-system' ); ?></p>
		</div>
		<div class="rcs-report-card">
			<h2><?php echo esc_html( (string) count( $rcs_recent_logs ) ); ?></h2>
			<p><?php esc_html_e( 'Recent Log Entries', 'request-coupon-system' ); ?></p>
		</div>
	</div>

	<h2><?php esc_html_e( 'Requests by Status', 'request-coupon-system' ); ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Status', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Count', 'request-coupon-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( RCS_Requests_RequestStatus::all() as $rcs_status ) : ?>
				<?php
				$rcs_status_label = isset( $rcs_statuses[ $rcs_status ] ) ? $rcs_statuses[ $rcs_status ] : $rcs_status;
				?>
				<tr>
					<td><?php echo esc_html( $rcs_status_label ); ?></td>
					<td><?php echo esc_html( (string) $rcs_request_counts[ $rcs_status ] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'SMS by Status', 'request-coupon-system' ); ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Status', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Count', 'request-coupon-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Sent', 'request-coupon-system' ); ?></td>
				<td><?php echo esc_html( (string) $rcs_sms_sent ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Failed', 'request-coupon-system' ); ?></td>
				<td><?php echo esc_html( (string) $rcs_sms_failed ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Pending', 'request-coupon-system' ); ?></td>
				<td><?php echo esc_html( (string) $rcs_sms_pending ); ?></td>
			</tr>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'Recent Activity', 'request-coupon-system' ); ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Time', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Actor', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Role', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Action', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Result', 'request-coupon-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rcs_recent_logs ) ) : ?>
				<tr>
					<td colspan="5"><?php esc_html_e( 'No activity logged yet.', 'request-coupon-system' ); ?></td>
				</tr>
			<?php endif; ?>
			<?php foreach ( $rcs_recent_logs as $rcs_log ) : ?>
				<tr>
					<td><?php echo esc_html( $rcs_log['created_at'] ); ?></td>
					<td><?php echo esc_html( (int) $rcs_log['actor_user_id'] ); ?></td>
					<td><?php echo esc_html( $rcs_log['actor_role'] ); ?></td>
					<td><?php echo esc_html( $rcs_log['action'] ); ?></td>
					<td><?php echo esc_html( $rcs_log['result'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
