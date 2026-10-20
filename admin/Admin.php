<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Admin_Admin {

	const MENU_SLUG = 'request-coupon-system';

	private static $instance = null;

	private $pages = array();

	private function __construct() {
		$this->pages = array(
			'forms'    => RCS_Admin_Pages_FormsPage::instance(),
			'requests' => RCS_Admin_Pages_RequestsPage::instance(),
			'coupons'  => RCS_Admin_Pages_CouponsPage::instance(),
			'sms'      => RCS_Admin_Pages_SmsPage::instance(),
			'reports'  => RCS_Admin_Pages_ReportsPage::instance(),
			'settings' => RCS_Admin_Pages_SettingsPage::instance(),
		);

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_notices', array( $this, 'render_requirement_notices' ) );
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register_menu() {
		add_menu_page(
			__( 'Request & Coupon System', 'request-coupon-system' ),
			__( 'Request & Coupon', 'request-coupon-system' ),
			RCS_Core_Plugin::CAP_MANAGE_FORMS,
			self::MENU_SLUG,
			array( $this->page( 'forms' ), 'render' ),
			'dashicons-feedback',
			58
		);

		$submenus = array(
			array(
				'forms',
				__( 'Forms', 'request-coupon-system' ),
				self::MENU_SLUG,
				RCS_Core_Plugin::CAP_MANAGE_FORMS,
			),
			array(
				'requests',
				__( 'Requests', 'request-coupon-system' ),
				'request-coupon-system-requests',
				RCS_Core_Plugin::CAP_MANAGE_REQUESTS,
			),
			array(
				'coupons',
				__( 'Coupons', 'request-coupon-system' ),
				'request-coupon-system-coupons',
				RCS_Core_Plugin::CAP_MANAGE_COUPONS,
			),
			array(
				'sms',
				__( 'SMS', 'request-coupon-system' ),
				'request-coupon-system-sms',
				RCS_Core_Plugin::CAP_MANAGE_SMS,
			),
			array(
				'reports',
				__( 'Reports', 'request-coupon-system' ),
				'request-coupon-system-reports',
				RCS_Core_Plugin::CAP_VIEW_REPORTS,
			),
			array(
				'settings',
				__( 'Settings', 'request-coupon-system' ),
				'request-coupon-system-settings',
				RCS_Core_Plugin::CAP_MANAGE_SETTINGS,
			),
		);

		foreach ( $submenus as $submenu ) {
			add_submenu_page(
				self::MENU_SLUG,
				$submenu[1],
				$submenu[1],
				$submenu[3],
				$submenu[2],
				array( $this->page( $submenu[0] ), 'render' )
			);
		}
	}

	public function render_requirement_notices() {
		$errors = RCS_Core_Requirements::stored_errors();

		if ( empty( $errors ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>' . esc_html__( 'Request & Coupon System did not pass the environment checks:', 'request-coupon-system' ) . '</p><ul style="list-style:disc;padding-left:20px;">';

		foreach ( $errors as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';
		}

		echo '</ul></div>';
	}

	private function page( $key ) {
		return $this->pages[ $key ];
	}
}
