<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Coupons_CouponOwnership {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_owner_id( array $record ) {
		return isset( $record['user_id'] ) ? (int) $record['user_id'] : 0;
	}

	public function is_owner( array $record, $user_id = 0 ) {
		$owner = $this->get_owner_id( $record );

		if ( $owner <= 0 ) {
			return false;
		}

		$user_id = $user_id > 0 ? (int) $user_id : get_current_user_id();

		return $owner === (int) $user_id;
	}

	public function is_disabled( array $record ) {
		return ! empty( $record['disabled_at'] );
	}

	public function is_expired( array $record ) {
		if ( empty( $record['wc_coupon_id'] ) ) {
			return false;
		}

		$coupon = RCS_Coupons_WooCommerceCoupon::instance()->get( (int) $record['wc_coupon_id'] );

		if ( ! $coupon ) {
			return false;
		}

		$expires = $coupon->get_date_expires();

		if ( ! $expires ) {
			return false;
		}

		return (int) $expires->getTimestamp() < time();
	}

	public function can_use( array $record, $user_id = 0 ) {
		if ( $this->is_disabled( $record ) ) {
			return false;
		}

		if ( $this->is_expired( $record ) ) {
			return false;
		}

		return $this->is_owner( $record, $user_id );
	}
}
