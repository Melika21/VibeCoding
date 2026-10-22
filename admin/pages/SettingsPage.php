<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	final class RCS_Admin_Pages_SettingsPage {

	const SLUG          = 'request-coupon-system-settings';
	const NONCE_SAVE    = 'rcs_save_sms_settings';

	private static $instance = null;

	private $view = 'settings';

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function render() {
		if ( ! current_user_can( RCS_Core_Plugin::CAP_MANAGE_SETTINGS ) ) {
			wp_die(
				esc_html__( 'You do not have permission to access this page.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		if ( 'POST' === $this->request_method() ) {
			$this->handle_save();
		}

		include RCS_PATH . 'admin/views/' . $this->view . '/index.php';
	}

	private function handle_save() {
		if ( ! isset( $_POST['rcs_sms_settings_nonce'] ) || ! wp_verify_nonce( $_POST['rcs_sms_settings_nonce'], self::NONCE_SAVE ) ) {
			wp_die(
				esc_html__( 'The settings could not be verified.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		if ( ! current_user_can( RCS_Core_Plugin::CAP_MANAGE_SETTINGS ) ) {
			wp_die(
				esc_html__( 'You do not have permission to save settings.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		$api_key      = isset( $_POST['rcs_sms_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['rcs_sms_api_key'] ) ) : '';
		$sender_line  = isset( $_POST['rcs_sms_sender_line'] ) ? sanitize_text_field( wp_unslash( $_POST['rcs_sms_sender_line'] ) ) : '';
		$admin_numbers = isset( $_POST['rcs_sms_admin_numbers'] ) ? wp_unslash( $_POST['rcs_sms_admin_numbers'] ) : '';

		$numbers = array();

		foreach ( explode( "\n", $admin_numbers ) as $number ) {
			$number = trim( $number );

			if ( '' !== $number ) {
				$numbers[] = $number;
			}
		}

		update_option(
			'rcs_sms_settings',
			array(
				'api_key'       => $api_key,
				'sender_line'   => $sender_line,
				'admin_numbers' => $numbers,
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

	private function request_method() {
		return isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
	}
}
