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

	private $catalogue = null;

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
			RCS_Approval_ExpertRole::CAP_REVIEW,
			RCS_Approval_ExpertRole::CAP_APPROVE,
			RCS_Approval_ExpertRole::CAP_REJECT,
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
		$domain = 'request-coupon-system';
		$locale = $this->locale();
		$file   = RCS_PATH . 'languages/' . $domain . '-' . $locale . '.mo';

		load_plugin_textdomain( $domain, false, dirname( plugin_basename( RCS_FILE ) ) . '/languages' );

		if ( ! file_exists( $file ) ) {
			return;
		}

		$mo = new MO();

		if ( ! $mo->import_from_file( $file ) ) {
			return;
		}

		$this->catalogue = $mo;

		add_filter( 'gettext_' . $domain, array( $this, 'filter_domain_translation' ), 10, 2 );
	}

	/**
	 * Serves the plugin catalogue for its own text domain.
	 *
	 * WordPress 6.7 loads text domains lazily against the site locale, so a
	 * plugin that ships its own locale cannot rely on the core loader. The
	 * catalogue is therefore applied to this domain only.
	 *
	 * @param string $translation Translation resolved by WordPress.
	 * @param string $text        Original text.
	 * @return string
	 */
	public function filter_domain_translation( $translation, $text ) {
		if ( ! $this->catalogue ) {
			return $translation;
		}

		$translated = $this->catalogue->translate( $text );

		if ( false === $translated || '' === $translated ) {
			return $translation;
		}

		return $translated;
	}

	/**
	 * Locale of the plugin interface.
	 *
	 * @return string
	 */
	public function locale() {
		$locale = defined( 'RCS_LOCALE' ) ? RCS_LOCALE : '';

		if ( '' === $locale || 'false' === $locale ) {
			$locale = determine_locale();
		}

		return (string) $locale;
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
