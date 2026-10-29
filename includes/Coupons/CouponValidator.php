<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Coupons_CouponValidator {

	const MIN_PERCENTAGE = 1;
	const MAX_PERCENTAGE = 100;

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function validate_issue( array $args ) {
		$request_id = isset( $args['request_id'] ) ? (int) $args['request_id'] : 0;

		if ( $request_id <= 0 ) {
			return new WP_Error( 'rcs_coupon_request_required', __( 'A coupon must belong to a request.', 'request-coupon-system' ) );
		}

		$user_id = isset( $args['user_id'] ) ? (int) $args['user_id'] : 0;

		if ( $user_id <= 0 ) {
			return new WP_Error( 'rcs_coupon_user_required', __( 'A coupon must belong to a valid user.', 'request-coupon-system' ) );
		}

		if ( ! isset( $args['amount'] ) ) {
			return new WP_Error( 'rcs_coupon_amount_required', __( 'The discount percentage is required.', 'request-coupon-system' ) );
		}

		$amount = (float) $args['amount'];

		if ( $amount < self::MIN_PERCENTAGE || $amount > self::MAX_PERCENTAGE ) {
			return new WP_Error(
				'rcs_coupon_amount_invalid',
				sprintf(
					/* translators: 1: minimum percentage, 2: maximum percentage. */
					__( 'The discount percentage must be between %1$s and %2$s.', 'request-coupon-system' ),
					self::MIN_PERCENTAGE,
					self::MAX_PERCENTAGE
				)
			);
		}

		if ( isset( $args['maximum_amount'] ) && '' !== $args['maximum_amount'] && null !== $args['maximum_amount'] ) {
			$maximum = (float) $args['maximum_amount'];

			if ( $maximum <= 0 ) {
				return new WP_Error( 'rcs_coupon_maximum_invalid', __( 'The maximum discount amount must be greater than zero.', 'request-coupon-system' ) );
			}
		}

		if ( isset( $args['code'] ) && '' !== trim( (string) $args['code'] ) ) {
			$code_check = $this->validate_code( $args['code'] );

			if ( is_wp_error( $code_check ) ) {
				return $code_check;
			}
		}

		return true;
	}

	public function validate_code( $code ) {
		$code = trim( (string) $code );

		if ( '' === $code ) {
			return new WP_Error( 'rcs_coupon_code_required', __( 'The coupon code is required.', 'request-coupon-system' ) );
		}

		if ( strlen( $code ) > 100 ) {
			return new WP_Error( 'rcs_coupon_code_invalid', __( 'The coupon code is too long.', 'request-coupon-system' ) );
		}

		if ( RCS_Coupons_WooCommerceCoupon::instance()->code_exists( $code ) ) {
			return new WP_Error( 'rcs_coupon_code_exists', __( 'This coupon code already exists.', 'request-coupon-system' ) );
		}

		return true;
	}
}
