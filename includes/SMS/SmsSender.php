<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_SMS_SmsSender {

	private static $instance = null;

	private $client;

	private $templates;

	private function __construct() {
		$this->client    = RCS_Integrations_SMSIr_SmsIrClient::instance();
		$this->templates = RCS_SMS_SmsTemplateManager::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function send( array $sms ) {
		$type = isset( $sms['sms_type'] ) ? $sms['sms_type'] : '';
		$id   = isset( $sms['id'] ) ? (int) $sms['id'] : 0;

		if ( ! $id || ! $this->templates->is_valid_type( $type ) ) {
			return array(
				'success' => false,
				'code'    => 0,
				'message' => __( 'The SMS record is not valid.', 'request-coupon-system' ),
			);
		}

		$pattern_id = (int) $sms['pattern_id'];

		if ( ! $pattern_id ) {
			$pattern_id = $this->templates->get_pattern_id( $type );
		}

		$values = $this->decode_params( isset( $sms['params'] ) ? $sms['params'] : '' );
		$params = $this->templates->build_parameters( $type, $values );

		$result = $this->client->send_pattern( $pattern_id, isset( $sms['recipient'] ) ? $sms['recipient'] : '', $params );

		return $result;
	}

	private function decode_params( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( ! is_string( $value ) || '' === $value ) {
			return array();
		}

		$decoded = json_decode( $value, true );

		return is_array( $decoded ) ? $decoded : array();
	}
}
