<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Core_Plugin {

	const CAP_MANAGE_FORMS    = 'rcs_manage_forms';
	const CAP_MANAGE_REQUESTS = 'rcs_manage_requests';
	const CAP_MANAGE_COUPONS  = 'rcs_manage_coupons';
	const CAP_MANAGE_SMS      = 'rcs_manage_sms';
	const CAP_VIEW_REPORTS    = 'rcs_view_reports';
	const CAP_MANAGE_SETTINGS = 'rcs_manage_settings';
	const CAP_VIEW_LOGS       = 'rcs_view_logs';

	private static $instance = null;

	private $booted = false;

	private $modules = array();

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function admin_capabilities() {
		return array(
			self::CAP_MANAGE_FORMS,
			self::CAP_MANAGE_REQUESTS,
			self::CAP_MANAGE_COUPONS,
			self::CAP_MANAGE_SMS,
			self::CAP_VIEW_REPORTS,
			self::CAP_MANAGE_SETTINGS,
			self::CAP_VIEW_LOGS,
		);
	}

	public function boot() {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		add_action( 'plugins_loaded', array( $this, 'init_modules' ), 20 );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'request-coupon-system', false, dirname( plugin_basename( RCS_FILE ) ) . '/languages' );
	}

	public function init_modules() {
		RCS_Database_Installer::maybe_upgrade();

		$this->modules['logs']       = RCS_Logs_LogManager::instance();
		$this->modules['scheduler']  = RCS_Scheduler_Scheduler::instance();
		$this->modules['frontend']   = RCS_Frontend_Frontend::instance();
		$this->modules['elementor']  = RCS_Elementor_Elementor::instance();
		$this->modules['woocommerce'] = RCS_Integrations_WooCommerce_WooCommerceIntegration::instance();

		if ( is_admin() ) {
			$this->modules['admin'] = RCS_Admin_Admin::instance();
		}
	}

	public function module( $key ) {
		if ( ! isset( $this->modules[ $key ] ) ) {
			return null;
		}

		return $this->modules[ $key ];
	}
}
