<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Admin_Pages_CouponsPage {

	const SLUG          = 'request-coupon-system-coupons';
	const NONCE_DISABLE = 'rcs_disable_coupon';

	private static $instance = null;

	private $capability = RCS_Core_Plugin::CAP_MANAGE_COUPONS;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function render() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die(
				esc_html__( 'You do not have permission to access this page.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		$action = isset( $_GET['rcs_coupon_action'] ) ? sanitize_key( wp_unslash( $_GET['rcs_coupon_action'] ) ) : '';

		if ( 'disable' === $action ) {
			$this->handle_disable();

			return;
		}

		$manager = RCS_Coupons_CouponManager::instance();
		$records = $manager->get_all();
		$woo     = RCS_Coupons_WooCommerceCoupon::instance();

		$total    = $manager->count_all();
		$disabled = 0;

		foreach ( $records as $record ) {
			if ( ! empty( $record['disabled_at'] ) ) {
				$disabled++;
			}
		}

		$woo_available = RCS_Coupons_WooCommerceCoupon::is_available();
		$disabled_now  = isset( $_GET['rcs_disabled'] ) ? (int) $_GET['rcs_disabled'] : 0;

		include RCS_PATH . 'admin/views/coupons/index.php';
	}

	private function handle_disable() {
		$relation_id = isset( $_GET['rcs_coupon_id'] ) ? absint( $_GET['rcs_coupon_id'] ) : 0;
		$nonce       = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! $relation_id || ! wp_verify_nonce( $nonce, self::NONCE_DISABLE . '_' . $relation_id ) ) {
			wp_die(
				esc_html__( 'The coupon request could not be verified.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		RCS_Coupons_CouponManager::instance()->disable( $relation_id );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'         => self::SLUG,
					'rcs_disabled' => 1,
				),
				admin_url( 'admin.php' )
			)
		);

		exit;
	}
}
