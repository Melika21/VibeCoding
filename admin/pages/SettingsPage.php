<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Admin_Pages_SettingsPage {

	const SLUG = 'request-coupon-system-settings';

	private static $instance = null;

	private $capability = RCS_Core_Plugin::CAP_MANAGE_SETTINGS;

	private $view = 'settings';

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

		include RCS_PATH . 'admin/views/' . $this->view . '/index.php';
	}
}
