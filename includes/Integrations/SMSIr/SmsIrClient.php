<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Integrations_SMSIr_SmsIrClient {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function is_configured() {
		$settings = get_option( 'rcs_sms_settings' );

		if ( ! is_array( $settings ) || empty( $settings['api_key'] ) ) {
			return false;
		}

		return true;
	}
}
