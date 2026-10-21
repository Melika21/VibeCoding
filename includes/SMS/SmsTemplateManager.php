<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_SMS_SmsTemplateManager {

	const TYPE_APPROVAL_CUSTOMER        = 'approval_customer';
	const TYPE_APPROVAL_ADMIN           = 'approval_admin';
	const TYPE_REJECTION_CUSTOMER       = 'rejection_customer';
	const TYPE_EXPIRY_REMINDER_CUSTOMER = 'expiry_reminder_customer';

	const OPTION_PARAMETERS = 'rcs_sms_parameters';

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_types() {
		return array(
			self::TYPE_APPROVAL_CUSTOMER        => __( 'Approval to customer', 'request-coupon-system' ),
			self::TYPE_APPROVAL_ADMIN           => __( 'Approval to administrator', 'request-coupon-system' ),
			self::TYPE_REJECTION_CUSTOMER       => __( 'Rejection to customer', 'request-coupon-system' ),
			self::TYPE_EXPIRY_REMINDER_CUSTOMER => __( 'Expiry reminder to customer', 'request-coupon-system' ),
		);
	}

	public function is_valid_type( $type ) {
		$types = $this->get_types();

		return isset( $types[ $type ] );
	}

	public function get_default_parameters( $type ) {
		$defaults = array(
			self::TYPE_APPROVAL_CUSTOMER        => array( 'name', 'code', 'discount', 'expiry' ),
			self::TYPE_APPROVAL_ADMIN           => array( 'name', 'code', 'discount', 'request' ),
			self::TYPE_REJECTION_CUSTOMER       => array( 'name', 'reason' ),
			self::TYPE_EXPIRY_REMINDER_CUSTOMER => array( 'name', 'code', 'expiry' ),
		);

		if ( ! isset( $defaults[ $type ] ) ) {
			return array();
		}

		return $defaults[ $type ];
	}

	public function get_pattern_id( $type ) {
		$settings = get_option( 'rcs_sms_settings' );

		if ( ! is_array( $settings ) || empty( $settings['pattern_ids'][ $type ] ) ) {
			return 0;
		}

		return (int) $settings['pattern_ids'][ $type ];
	}

	public function get_parameter_map( $type ) {
		$stored = get_option( self::OPTION_PARAMETERS );

		if ( is_array( $stored ) && ! empty( $stored[ $type ] ) && is_array( $stored[ $type ] ) ) {
			return $stored[ $type ];
		}

		$map = array();

		foreach ( $this->get_default_parameters( $type ) as $logical ) {
			$map[ $logical ] = $logical;
		}

		return $map;
	}

	public function save_parameter_map( $type, array $map ) {
		$stored = get_option( self::OPTION_PARAMETERS );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$clean = array();

		foreach ( $map as $logical => $provider ) {
			$logical  = sanitize_key( $logical );
			$provider = sanitize_text_field( $provider );

			if ( '' === $logical || '' === $provider ) {
				continue;
			}

			$clean[ $logical ] = $provider;
		}

		$stored[ $type ] = $clean;

		update_option( self::OPTION_PARAMETERS, $stored );
	}

	public function build_parameters( $type, array $values ) {
		$map    = $this->get_parameter_map( $type );
		$params = array();

		foreach ( $map as $logical => $provider ) {
			if ( ! array_key_exists( $logical, $values ) ) {
				continue;
			}

			$value = $values[ $logical ];

			$params[ $provider ] = is_scalar( $value ) ? (string) $value : wp_json_encode( $value );
		}

		return $params;
	}
}
