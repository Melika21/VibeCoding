<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Integrations_SMSIr_SmsIrClient {

	const API_BASE       = 'https://api.sms.ir/v1';
	const ENDPOINT_PATTERN = '/send/verify';

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_settings() {
		$settings = get_option( 'rcs_sms_settings' );

		if ( ! is_array( $settings ) ) {
			return array();
		}

		return $settings;
	}

	public function get_api_key() {
		$settings = $this->get_settings();

		return isset( $settings['api_key'] ) ? trim( (string) $settings['api_key'] ) : '';
	}

	public function get_sender_line() {
		$settings = $this->get_settings();

		return isset( $settings['sender_line'] ) ? trim( (string) $settings['sender_line'] ) : '';
	}

	public function is_configured() {
		return '' !== $this->get_api_key();
	}

	public function send_pattern( $pattern_id, $mobile, array $parameters ) {
		$api_key = $this->get_api_key();

		if ( '' === $api_key ) {
			return $this->failure( __( 'The SMS.ir API key is not configured.', 'request-coupon-system' ), 10 );
		}

		if ( ! $pattern_id ) {
			return $this->failure( __( 'No SMS.ir pattern is configured for this message type.', 'request-coupon-system' ), 0 );
		}

		$payload = array(
			'mobile'     => (string) $mobile,
			'templateId' => (int) $pattern_id,
			'parameters' => $this->format_parameters( $parameters ),
		);

		$response = wp_remote_request(
			self::API_BASE . self::ENDPOINT_PATTERN,
			array(
				'method'  => 'POST',
				'timeout' => 15,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
					'X-API-KEY'    => $api_key,
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $this->failure( $response->get_error_message(), 0 );
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) ) {
			$body = array();
		}

		$provider_status = isset( $body['status'] ) ? (int) $body['status'] : 0;
		$message         = isset( $body['message'] ) ? (string) $body['message'] : '';
		$data            = isset( $body['data'] ) && is_array( $body['data'] ) ? $body['data'] : array();

		if ( 200 !== $status_code || 1 !== $provider_status ) {
			if ( '' === $message ) {
				$message = __( 'SMS.ir rejected the request.', 'request-coupon-system' );
			}

			return array(
				'success'    => false,
				'code'       => $provider_status ? $provider_status : $status_code,
				'message'    => $message,
				'message_id' => 0,
				'cost'       => 0,
				'raw'        => $body,
			);
		}

		return array(
			'success'    => true,
			'code'       => $provider_status,
			'message'    => $message,
			'message_id' => isset( $data['messageId'] ) ? (int) $data['messageId'] : 0,
			'cost'       => isset( $data['cost'] ) ? (float) $data['cost'] : 0,
			'raw'        => $body,
		);
	}

	public function get_credit() {
		$api_key = $this->get_api_key();

		if ( '' === $api_key ) {
			return $this->failure( __( 'The SMS.ir API key is not configured.', 'request-coupon-system' ), 10 );
		}

		$response = wp_remote_get(
			self::API_BASE . '/credit',
			array(
				'timeout' => 15,
				'headers' => array(
					'Accept'    => 'application/json',
					'X-API-KEY' => $api_key,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $this->failure( $response->get_error_message(), 0 );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) || 1 !== ( isset( $body['status'] ) ? (int) $body['status'] : 0 ) ) {
			return array(
				'success' => false,
				'code'    => isset( $body['status'] ) ? (int) $body['status'] : 0,
				'message' => isset( $body['message'] ) ? (string) $body['message'] : __( 'SMS.ir rejected the request.', 'request-coupon-system' ),
				'credit'  => 0,
				'raw'     => is_array( $body ) ? $body : array(),
			);
		}

		return array(
			'success' => true,
			'code'    => 1,
			'message' => isset( $body['message'] ) ? (string) $body['message'] : '',
			'credit'  => isset( $body['data'] ) ? (float) $body['data'] : 0,
			'raw'     => $body,
		);
	}

	private function format_parameters( array $parameters ) {
		$formatted = array();

		foreach ( $parameters as $name => $value ) {
			$formatted[] = array(
				'name'  => (string) $name,
				'value' => is_scalar( $value ) ? (string) $value : wp_json_encode( $value ),
			);
		}

		return $formatted;
	}

	private function failure( $message, $code ) {
		return array(
			'success'    => false,
			'code'       => (int) $code,
			'message'    => (string) $message,
			'message_id' => 0,
			'cost'       => 0,
			'raw'        => array(),
		);
	}
}
