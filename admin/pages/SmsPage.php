<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Admin_Pages_SmsPage {

	const SLUG       = 'request-coupon-system-sms';
	const NONCE_SAVE  = 'rcs_save_sms_settings';
	const NONCE_RETRY = 'rcs_retry_sms';

	private static $instance = null;

	private $capability = RCS_Core_Plugin::CAP_MANAGE_SMS;

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

		$action = isset( $_GET['rcs_sms_action'] ) ? sanitize_key( wp_unslash( $_GET['rcs_sms_action'] ) ) : '';

		if ( 'retry' === $action ) {
			$this->handle_retry();

			return;
		}

		$this->handle_save();

		$manager   = RCS_SMS_SmsManager::instance();
		$templates = RCS_SMS_SmsTemplateManager::instance();
		$client    = RCS_Integrations_SMSIr_SmsIrClient::instance();

		$settings      = $client->get_settings();
		$types         = $templates->get_types();
		$pattern_ids   = isset( $settings['pattern_ids'] ) && is_array( $settings['pattern_ids'] ) ? $settings['pattern_ids'] : array();
		$parameter_map = array();

		foreach ( array_keys( $types ) as $type ) {
			$parameter_map[ $type ] = $templates->get_parameter_map( $type );
		}

		$records = $manager->get_all( array( 'limit' => 50 ) );
		$counts  = array(
			'total'   => count( $manager->get_all( array( 'limit' => 0 ) ) ),
			'pending' => $manager->count_by_status( RCS_SMS_SmsStatus::PENDING ),
			'sent'    => $manager->count_by_status( RCS_SMS_SmsStatus::SENT ),
			'failed'  => $manager->count_by_status( RCS_SMS_SmsStatus::FAILED ),
		);

		$saved = isset( $_GET['rcs_saved'] ) ? (int) $_GET['rcs_saved'] : 0;
		$retried = isset( $_GET['rcs_retried'] ) ? (int) $_GET['rcs_retried'] : 0;

		include RCS_PATH . 'admin/views/sms/index.php';
	}

	private function handle_save() {
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '' ) ) {
			return;
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_SAVE ) ) {
			wp_die(
				esc_html__( 'The SMS request could not be verified.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		$templates = RCS_SMS_SmsTemplateManager::instance();
		$types     = $templates->get_types();

		$api_key = isset( $_POST['rcs_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['rcs_api_key'] ) ) : '';
		$sender  = isset( $_POST['rcs_sender_line'] ) ? sanitize_text_field( wp_unslash( $_POST['rcs_sender_line'] ) ) : '';

		$admin_numbers = array();

		if ( isset( $_POST['rcs_admin_numbers'] ) ) {
			$raw_numbers = sanitize_textarea_field( wp_unslash( $_POST['rcs_admin_numbers'] ) );

			foreach ( preg_split( '/[\r\n,]+/', $raw_numbers ) as $number ) {
				$number = trim( $number );

				if ( '' !== $number ) {
					$admin_numbers[] = $number;
				}
			}
		}

		$pattern_ids = array();

		foreach ( array_keys( $types ) as $type ) {
			$key = 'rcs_pattern_' . $type;

			$pattern_ids[ $type ] = isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : 0;

			$map_key = 'rcs_parameters_' . $type;
			$map     = array();

			if ( isset( $_POST[ $map_key ] ) ) {
				$names = sanitize_text_field( wp_unslash( $_POST[ $map_key ] ) );

				foreach ( explode( ',', $names ) as $name ) {
					$name = sanitize_key( trim( $name ) );

					if ( '' !== $name ) {
						$map[ $name ] = $name;
					}
				}
			}

			$templates->save_parameter_map( $type, $map );
		}

		update_option(
			'rcs_sms_settings',
			array(
				'api_key'       => $api_key,
				'pattern_ids'   => $pattern_ids,
				'sender_line'   => $sender,
				'admin_numbers' => $admin_numbers,
			)
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'      => self::SLUG,
					'rcs_saved' => 1,
				),
				admin_url( 'admin.php' )
			)
		);

		exit;
	}

	private function handle_retry() {
		$sms_id = isset( $_GET['rcs_sms_id'] ) ? absint( $_GET['rcs_sms_id'] ) : 0;
		$nonce  = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! $sms_id || ! wp_verify_nonce( $nonce, self::NONCE_RETRY . '_' . $sms_id ) ) {
			wp_die(
				esc_html__( 'The SMS request could not be verified.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		RCS_SMS_SmsRetry::instance()->retry( $sms_id );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'         => self::SLUG,
					'rcs_retried'  => 1,
				),
				admin_url( 'admin.php' )
			)
		);

		exit;
	}
}
