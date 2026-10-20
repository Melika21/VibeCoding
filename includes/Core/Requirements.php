<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Core_Requirements {

	const TRANSIENT_ERRORS = 'rcs_requirements_errors';

	public static function php_version() {
		return PHP_VERSION;
	}

	public static function wp_version() {
		return (string) get_bloginfo( 'version' );
	}

	public static function wc_version() {
		if ( ! defined( 'WC_VERSION' ) ) {
			return '';
		}

		return (string) WC_VERSION;
	}

	public static function has_woocommerce() {
		return class_exists( 'WooCommerce' );
	}

	public static function has_elementor() {
		return did_action( 'elementor/loaded' ) > 0;
	}

	public static function errors() {
		$errors = array();

		if ( version_compare( self::php_version(), RCS_REQUIRES_PHP, '<' ) ) {
			$errors['php'] = sprintf(
				/* translators: 1: required PHP version, 2: current PHP version. */
				__( 'Request & Coupon System requires PHP %1$s or newer. This server runs PHP %2$s.', 'request-coupon-system' ),
				RCS_REQUIRES_PHP,
				self::php_version()
			);
		}

		if ( version_compare( self::wp_version(), RCS_REQUIRES_WP, '<' ) ) {
			$errors['wp'] = sprintf(
				/* translators: 1: required WordPress version, 2: current WordPress version. */
				__( 'Request & Coupon System requires WordPress %1$s or newer. This site runs WordPress %2$s.', 'request-coupon-system' ),
				RCS_REQUIRES_WP,
				self::wp_version()
			);
		}

		return $errors;
	}

	public static function warnings() {
		$warnings = array();

		if ( ! self::has_woocommerce() ) {
			$warnings['woocommerce'] = __( 'WooCommerce is not active. Coupon issuing and coupon management stay unavailable until it is activated.', 'request-coupon-system' );
		} elseif ( '' !== self::wc_version() && version_compare( self::wc_version(), RCS_REQUIRES_WC, '<' ) ) {
			$warnings['woocommerce'] = sprintf(
				/* translators: 1: required WooCommerce version, 2: current WooCommerce version. */
				__( 'Request & Coupon System requires WooCommerce %1$s or newer. This site runs WooCommerce %2$s.', 'request-coupon-system' ),
				RCS_REQUIRES_WC,
				self::wc_version()
			);
		}

		if ( ! self::has_elementor() ) {
			$warnings['elementor'] = __( 'Elementor is not active. The form widget is registered only while Elementor is running.', 'request-coupon-system' );
		}

		return $warnings;
	}

	public static function check() {
		return array(
			'errors'   => self::errors(),
			'warnings' => self::warnings(),
		);
	}

	public static function store() {
		$errors = self::errors();

		if ( empty( $errors ) ) {
			delete_transient( self::TRANSIENT_ERRORS );

			return;
		}

		set_transient( self::TRANSIENT_ERRORS, $errors, HOUR_IN_SECONDS );
	}

	public static function stored_errors() {
		$errors = get_transient( self::TRANSIENT_ERRORS );

		if ( ! is_array( $errors ) || empty( $errors ) ) {
			return array();
		}

		return $errors;
	}
}
