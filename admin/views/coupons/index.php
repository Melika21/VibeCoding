<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rcs_records        = isset( $records ) ? $records : array();
$rcs_total          = isset( $total ) ? (int) $total : 0;
$rcs_disabled_count = isset( $disabled ) ? (int) $disabled : 0;
$rcs_woo_available  = isset( $woo_available ) ? (bool) $woo_available : false;
$rcs_disabled_now   = isset( $disabled_now ) ? (int) $disabled_now : 0;
?>
<div class="wrap rcs-coupons">
	<h1><?php esc_html_e( 'Coupons', 'request-coupon-system' ); ?></h1>

	<?php if ( $rcs_disabled_now ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'The coupon was disabled.', 'request-coupon-system' ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! $rcs_woo_available ) : ?>
		<div class="notice notice-error"><p><?php esc_html_e( 'WooCommerce is not active. Coupons are stored in WooCommerce and cannot be issued or disabled without it.', 'request-coupon-system' ); ?></p></div>
	<?php endif; ?>

	<p>
		<?php
		printf(
			/* translators: 1: total coupon count, 2: disabled coupon count. */
			esc_html__( 'Issued coupons: %1$s — Disabled: %2$s', 'request-coupon-system' ),
			esc_html( (string) $rcs_total ),
			esc_html( (string) $rcs_disabled_count )
		);
		?>
	</p>

	<table class="wp-list-table widefat fixed striped rcs-coupons-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Code', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Request', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'User', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Discount', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Expires', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Actions', 'request-coupon-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rcs_records ) ) : ?>
				<tr>
					<td colspan="7"><?php esc_html_e( 'No coupon has been issued yet.', 'request-coupon-system' ); ?></td>
				</tr>
			<?php endif; ?>

			<?php foreach ( $rcs_records as $rcs_record ) : ?>
				<?php
				$rcs_coupon   = RCS_Coupons_WooCommerceCoupon::instance()->get( (int) $rcs_record['wc_coupon_id'] );
				$rcs_amount   = $rcs_coupon ? (float) $rcs_coupon->get_amount() : 0;
				$rcs_maximum  = $rcs_coupon ? (float) $rcs_coupon->get_maximum_amount() : 0;
				$rcs_expires  = $rcs_coupon && $rcs_coupon->get_date_expires() ? $rcs_coupon->get_date_expires()->date( 'Y-m-d H:i' ) : '';
				$rcs_is_disabled = ! empty( $rcs_record['disabled_at'] );
				$rcs_disable_url = add_query_arg(
					array(
						'page'             => RCS_Admin_Pages_CouponsPage::SLUG,
						'rcs_coupon_action' => 'disable',
						'rcs_coupon_id'     => (int) $rcs_record['id'],
						'_wpnonce'          => wp_create_nonce( RCS_Admin_Pages_CouponsPage::NONCE_DISABLE . '_' . (int) $rcs_record['id'] ),
					),
					admin_url( 'admin.php' )
				);
				?>
				<tr>
					<td><strong><code><?php echo esc_html( $rcs_record['coupon_code'] ); ?></code></strong></td>
					<td><?php echo esc_html( (string) $rcs_record['request_id'] ); ?></td>
					<td><?php echo esc_html( (string) $rcs_record['user_id'] ); ?></td>
					<td>
						<?php echo esc_html( $rcs_amount . '%' ); ?>
						<?php if ( $rcs_maximum > 0 ) : ?>
								<br /><small><?php echo esc_html( sprintf( __( 'max %s', 'request-coupon-system' ), $rcs_maximum ) ); ?></small>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $rcs_expires ); ?></td>
					<td><?php echo esc_html( $rcs_is_disabled ? 'disabled' : 'active' ); ?></td>
					<td>
						<?php if ( ! $rcs_is_disabled ) : ?>
							<a href="<?php echo esc_url( $rcs_disable_url ); ?>" class="submitdelete" onclick="return confirm( '<?php echo esc_js( __( 'Disable this coupon?', 'request-coupon-system' ) ); ?>' );">
								<?php esc_html_e( 'Disable', 'request-coupon-system' ); ?>
							</a>
						<?php else : ?>
							<span aria-hidden="true">&mdash;</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
