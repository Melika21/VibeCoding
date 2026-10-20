<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Coupons_WooCommerceCoupon {

	const DISCOUNT_TYPE = 'percent';

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function is_available() {
		return class_exists( 'WC_Coupon' ) && function_exists( 'wc_get_coupon_id_by_code' );
	}

	public function create( array $args ) {
		if ( ! self::is_available() ) {
			return new WP_Error( 'rcs_woocommerce_missing', __( 'WooCommerce is not active.', 'request-coupon-system' ) );
		}

		$code = isset( $args['code'] ) ? trim( (string) $args['code'] ) : '';

		if ( '' === $code ) {
			$code = $this->generate_unique_code();

			if ( '' === $code ) {
				return new WP_Error( 'rcs_coupon_code_generation_failed', __( 'A unique coupon code could not be generated.', 'request-coupon-system' ) );
			}
		}

		try {
			$coupon = new WC_Coupon();

			$coupon->set_code( $code );
			$coupon->set_description( isset( $args['description'] ) ? (string) $args['description'] : '' );
			$coupon->set_discount_type( self::DISCOUNT_TYPE );
			$coupon->set_amount( isset( $args['amount'] ) ? (float) $args['amount'] : 0 );
			$coupon->set_maximum_amount( ! empty( $args['maximum_amount'] ) ? (float) $args['maximum_amount'] : '' );
			$coupon->set_individual_use( true );
			$coupon->set_usage_limit( 1 );
			$coupon->set_usage_limit_per_user( 1 );
			$coupon->set_product_ids( array() );
			$coupon->set_product_categories( array() );
			$coupon->set_excluded_product_ids( array() );
			$coupon->set_excluded_product_categories( array() );
			$coupon->set_exclude_sale_items( false );
			$coupon->set_minimum_amount( '' );
			$coupon->set_free_shipping( false );
			$coupon->set_email_restrictions( ! empty( $args['email'] ) ? array( $args['email'] ) : array() );
			$coupon->set_date_expires( isset( $args['date_expires'] ) ? (int) $args['date_expires'] : time() + ( 7 * 24 * 60 * 60 ) );
			$coupon->save();
		} catch ( \Throwable $error ) {
			return new WP_Error( 'rcs_coupon_create_failed', $error->getMessage() );
		}

		if ( ! $coupon->get_id() ) {
			return new WP_Error( 'rcs_coupon_create_failed', __( 'The coupon could not be stored.', 'request-coupon-system' ) );
		}

		return (int) $coupon->get_id();
	}

	public function get( $coupon_id ) {
		if ( ! self::is_available() ) {
			return null;
		}

		$coupon = new WC_Coupon( (int) $coupon_id );

		if ( ! $coupon->get_id() ) {
			return null;
		}

		return $coupon;
	}

	public function disable( $coupon_id ) {
		$coupon = $this->get( $coupon_id );

		if ( ! $coupon ) {
			return false;
		}

		if ( 'trash' === $coupon->get_status() ) {
			return false;
		}

		try {
			$coupon->set_status( 'trash' );
			$coupon->save();
		} catch ( \Throwable $error ) {
			return false;
		}

		return true;
	}

	public function update_amount( $coupon_id, $amount, $maximum_amount = null ) {
		$coupon = $this->get( $coupon_id );

		if ( ! $coupon ) {
			return false;
		}

		try {
			$coupon->set_amount( (float) $amount );

			if ( null !== $maximum_amount ) {
				$coupon->set_maximum_amount( '' !== $maximum_amount && null !== $maximum_amount ? (float) $maximum_amount : '' );
			}

			$coupon->save();
		} catch ( \Throwable $error ) {
			return false;
		}

		return true;
	}

	public function code_exists( $code ) {
		global $wpdb;

		$code = trim( (string) $code );

		if ( '' === $code ) {
			return false;
		}

		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE LOWER(post_title) = LOWER(%s) AND post_type = 'shop_coupon' LIMIT 1",
				$code
			)
		);

		return ! empty( $found );
	}

	public function generate_unique_code() {
		for ( $attempt = 0; $attempt < 25; $attempt++ ) {
			$code = strtoupper( wp_generate_password( 12, false, false ) );

			if ( ! $this->code_exists( $code ) ) {
				return $code;
			}
		}

		return '';
	}
}
