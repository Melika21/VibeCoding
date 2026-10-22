<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Admin_Pages_ReportsPage {

	const SLUG = 'request-coupon-system-reports';

	private static $instance = null;

	private $view = 'reports';

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function render() {
		if ( ! $this->can_view() ) {
			wp_die(
				esc_html__( 'You do not have permission to access this page.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		include RCS_PATH . 'admin/views/' . $this->view . '/index.php';
	}

	private function can_view() {
		return current_user_can( RCS_Core_Plugin::CAP_VIEW_REPORTS ) || current_user_can( RCS_Approval_ExpertRole::CAP_REVIEW );
	}
}
