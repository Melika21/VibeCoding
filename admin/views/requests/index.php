<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rcs_filters    = isset( $filters ) ? $filters : array();
$rcs_total      = isset( $total ) ? (int) $total : 0;
$rcs_records    = isset( $records ) ? $records : array();
$rcs_forms      = isset( $forms ) ? $forms : array();
$rcs_statuses   = isset( $statuses ) ? $statuses : array();
$rcs_counts     = isset( $counts ) ? $counts : array();
$rcs_pages      = isset( $pages ) ? max( 1, (int) $pages ) : 1;
$rcs_paged      = isset( $paged ) ? max( 1, (int) $paged ) : 1;
$rcs_notice     = isset( $notice ) ? $notice : null;
$rcs_status     = isset( $rcs_filters['status'] ) ? $rcs_filters['status'] : '';
$rcs_form_id    = isset( $rcs_filters['form_id'] ) ? (int) $rcs_filters['form_id'] : 0;
$rcs_search     = isset( $rcs_filters['search'] ) ? $rcs_filters['search'] : '';
$rcs_list_url   = add_query_arg( array( 'page' => RCS_Admin_Pages_RequestsPage::SLUG ), admin_url( 'admin.php' ) );
$rcs_coupons    = RCS_Coupons_CouponManager::instance();
?>
<div class="wrap rcs-requests">
	<h1><?php esc_html_e( 'Requests', 'request-coupon-system' ); ?></h1>

	<?php if ( $rcs_notice && 'error' === $rcs_notice['type'] ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $rcs_notice['message'] ); ?></p></div>
	<?php endif; ?>

	<p class="description"><?php esc_html_e( 'Review the submitted requests, then approve them to issue a coupon or reject them with a reason.', 'request-coupon-system' ); ?></p>

	<ul class="subsubsub">
		<?php
		$rcs_summary = array();

		foreach ( $rcs_statuses as $rcs_status_key => $rcs_status_label ) {
			$rcs_summary[] = sprintf(
				/* translators: 1: status label, 2: request count. */
				esc_html__( '%1$s: %2$s', 'request-coupon-system' ),
				esc_html( $rcs_status_label ),
				esc_html( (string) ( isset( $rcs_counts[ $rcs_status_key ] ) ? $rcs_counts[ $rcs_status_key ] : 0 ) )
			);
		}
		?>
		<li><?php echo esc_html( implode( ' — ', $rcs_summary ) ); ?></li>
	</ul>

	<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="rcs-requests-filters">
		<input type="hidden" name="page" value="<?php echo esc_attr( RCS_Admin_Pages_RequestsPage::SLUG ); ?>" />

		<label class="screen-reader-text" for="rcs-search"><?php esc_html_e( 'Search', 'request-coupon-system' ); ?></label>
		<input type="search" id="rcs-search" name="rcs_search" value="<?php echo esc_attr( $rcs_search ); ?>" placeholder="<?php esc_attr_e( 'Search', 'request-coupon-system' ); ?>" />

		<label class="screen-reader-text" for="rcs-status"><?php esc_html_e( 'Status', 'request-coupon-system' ); ?></label>
		<select id="rcs-status" name="rcs_status">
			<option value=""><?php esc_html_e( 'All statuses', 'request-coupon-system' ); ?></option>
			<?php foreach ( $rcs_statuses as $rcs_status_key => $rcs_status_label ) : ?>
				<option value="<?php echo esc_attr( $rcs_status_key ); ?>" <?php selected( $rcs_status, $rcs_status_key ); ?>><?php echo esc_html( $rcs_status_label ); ?></option>
			<?php endforeach; ?>
		</select>

		<label class="screen-reader-text" for="rcs-form"><?php esc_html_e( 'Form', 'request-coupon-system' ); ?></label>
		<select id="rcs-form" name="rcs_form_id">
			<option value="0"><?php esc_html_e( 'All forms', 'request-coupon-system' ); ?></option>
			<?php foreach ( $rcs_forms as $rcs_form ) : ?>
				<option value="<?php echo esc_attr( (string) $rcs_form['id'] ); ?>" <?php selected( $rcs_form_id, (int) $rcs_form['id'] ); ?>><?php echo esc_html( $rcs_form['name'] ); ?></option>
			<?php endforeach; ?>
		</select>

		<?php submit_button( __( 'Filter', 'request-coupon-system' ), 'secondary', 'rcs_filter', false ); ?>
	</form>

	<table class="wp-list-table widefat fixed striped rcs-requests-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'ID', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Form', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'User', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Coupon code', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Created', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Actions', 'request-coupon-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rcs_records ) ) : ?>
				<tr>
					<td colspan="7"><?php esc_html_e( 'No request has been submitted yet.', 'request-coupon-system' ); ?></td>
				</tr>
			<?php endif; ?>

			<?php foreach ( $rcs_records as $rcs_record ) : ?>
				<?php
				$rcs_request_id  = (int) $rcs_record['request_id'];
				$rcs_owner       = get_userdata( (int) $rcs_record['user_id'] );
				$rcs_form        = RCS_Forms_FormManager::instance()->get( (int) $rcs_record['form_id'] );
				$rcs_coupon      = $rcs_coupons->get_by_request( $rcs_request_id );
				$rcs_view_url    = add_query_arg(
					array(
						'page'              => RCS_Admin_Pages_RequestsPage::SLUG,
						'rcs_request_action' => 'view',
						'rcs_request_id'    => $rcs_request_id,
					),
					admin_url( 'admin.php' )
				);
				?>
				<tr>
					<td><?php echo esc_html( (string) $rcs_request_id ); ?></td>
					<td><?php echo esc_html( $rcs_form ? $rcs_form['name'] : '' ); ?></td>
					<td>
						<?php echo esc_html( $rcs_owner ? $rcs_owner->display_name : '' ); ?>
						<?php if ( $rcs_owner ) : ?>
							<br /><small><?php echo esc_html( $rcs_owner->user_email ); ?></small>
						<?php endif; ?>
					</td>
					<td>
						<?php
						$rcs_status_key = $rcs_record['status'];
						$rcs_status_label = isset( $rcs_statuses[ $rcs_status_key ] ) ? $rcs_statuses[ $rcs_status_key ] : $rcs_status_key;
						?>
						<span class="rcs-status rcs-status-<?php echo esc_attr( $rcs_status_key ); ?>"><?php echo esc_html( $rcs_status_label ); ?></span>
					</td>
					<td><?php echo esc_html( $rcs_coupon ? $rcs_coupon['coupon_code'] : '' ); ?></td>
					<td><?php echo esc_html( $rcs_record['created_at'] ); ?></td>
					<td>
						<a href="<?php echo esc_url( $rcs_view_url ); ?>"><?php esc_html_e( 'View', 'request-coupon-system' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $rcs_pages > 1 ) : ?>
		<div class="tablenav">
			<div class="tablenav-pages">
				<span class="displaying-num">
					<?php
					printf(
						/* translators: %s: number of requests. */
						esc_html__( '%s requests', 'request-coupon-system' ),
						esc_html( (string) $rcs_total )
					);
					?>
				</span>

				<?php
				$rcs_page_links = paginate_links(
					array(
						'base'      => add_query_arg( 'rcs_paged', '%#%' ),
						'format'    => '',
						'current'   => $rcs_paged,
						'total'     => $rcs_pages,
						'prev_text' => __( 'Previous', 'request-coupon-system' ),
						'next_text' => __( 'Next', 'request-coupon-system' ),
					)
				);

				if ( $rcs_page_links ) {
					echo wp_kses_post( $rcs_page_links );
				}
				?>
			</div>
		</div>
	<?php endif; ?>
</div>
