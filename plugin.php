<?php
/**
 * Plugin Name:       Request & Coupon System
 * Plugin URI:        https://github.com/Melika21/VibeCoding
 * Description:       Modular form builder with request revisions, expert approval, WooCommerce coupon issuing and SMS.ir notifications.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Melika Sohrabi
 * Author URI:        https://github.com/Melika21
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       request-coupon-system
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RCS_VERSION', '1.0.0' );
define( 'RCS_FILE', __FILE__ );
define( 'RCS_PATH', plugin_dir_path( __FILE__ ) );
define( 'RCS_URL', plugin_dir_url( __FILE__ ) );
define( 'RCS_BASENAME', plugin_basename( __FILE__ ) );
define( 'RCS_REQUIRES_WP', '6.0' );
define( 'RCS_REQUIRES_PHP', '7.4' );
define( 'RCS_REQUIRES_WC', '7.0' );
define( 'RCS_DB_VERSION', '1.0.0' );
define( 'RCS_ROLES_VERSION', '1.0.1' );
define( 'RCS_TABLE_PREFIX', 'rcs_' );

/**
 * Locale of the plugin interface. The plugin ships a Persian translation and
 * renders in Persian by default. Define this constant as an empty string or
 * 'false' to follow the locale of the site instead.
 */
if ( ! defined( 'RCS_LOCALE' ) ) {
	define( 'RCS_LOCALE', 'fa_IR' );
}

final class RCS_Autoloader {

	private static $instance = null;

	private $map = array(
		'RCS_Core_Plugin'                                     => 'includes/Core/Plugin.php',
		'RCS_Core_Activator'                                  => 'includes/Core/Activator.php',
		'RCS_Core_Deactivator'                                => 'includes/Core/Deactivator.php',
		'RCS_Core_Uninstaller'                                => 'includes/Core/Uninstaller.php',
		'RCS_Core_Requirements'                               => 'includes/Core/Requirements.php',
		'RCS_Database_Schema'                                 => 'includes/Database/Schema.php',
		'RCS_Database_Installer'                              => 'includes/Database/Installer.php',
		'RCS_Forms_FormManager'                               => 'includes/Forms/FormManager.php',
		'RCS_Forms_FormRepository'                            => 'includes/Forms/FormRepository.php',
		'RCS_Forms_FieldManager'                              => 'includes/Forms/FieldManager.php',
		'RCS_Forms_SubmissionHandler'                         => 'includes/Forms/SubmissionHandler.php',
		'RCS_Forms_Validation'                                => 'includes/Forms/Validation.php',
		'RCS_Forms_ConditionalLogic'                          => 'includes/Forms/ConditionalLogic.php',
		'RCS_Forms_DraftManager'                              => 'includes/Forms/DraftManager.php',
		'RCS_Forms_MultiStepManager'                          => 'includes/Forms/MultiStepManager.php',
		'RCS_Requests_RequestManager'                         => 'includes/Requests/RequestManager.php',
		'RCS_Requests_RequestRepository'                      => 'includes/Requests/RequestRepository.php',
		'RCS_Requests_RequestStatus'                          => 'includes/Requests/RequestStatus.php',
		'RCS_Requests_RequestAccess'                          => 'includes/Requests/RequestAccess.php',
		'RCS_Revisions_RevisionManager'                      => 'includes/Revisions/RevisionManager.php',
		'RCS_Revisions_RevisionRepository'                   => 'includes/Revisions/RevisionRepository.php',
		'RCS_Approval_ApprovalManager'                        => 'includes/Approval/ApprovalManager.php',
		'RCS_Approval_ApprovalValidator'                      => 'includes/Approval/ApprovalValidator.php',
		'RCS_Approval_ExpertRole'                             => 'includes/Approval/ExpertRole.php',
		'RCS_Coupons_CouponManager'                           => 'includes/Coupons/CouponManager.php',
		'RCS_Coupons_CouponValidator'                         => 'includes/Coupons/CouponValidator.php',
		'RCS_Coupons_CouponOwnership'                         => 'includes/Coupons/CouponOwnership.php',
		'RCS_Coupons_WooCommerceCoupon'                       => 'includes/Coupons/WooCommerceCoupon.php',
		'RCS_SMS_SmsManager'                                  => 'includes/SMS/SmsManager.php',
		'RCS_SMS_SmsTemplateManager'                          => 'includes/SMS/SmsTemplateManager.php',
		'RCS_SMS_SmsSender'                                   => 'includes/SMS/SmsSender.php',
		'RCS_SMS_SmsRetry'                                    => 'includes/SMS/SmsRetry.php',
		'RCS_SMS_SmsStatus'                                   => 'includes/SMS/SmsStatus.php',
		'RCS_Scheduler_Scheduler'                             => 'includes/Scheduler/Scheduler.php',
		'RCS_Logs_LogManager'                                 => 'includes/Logs/LogManager.php',
		'RCS_Logs_LogRepository'                              => 'includes/Logs/LogRepository.php',
		'RCS_Reports_ReportManager'                           => 'includes/Reports/ReportManager.php',
		'RCS_Integrations_WooCommerce_WooCommerceIntegration' => 'includes/Integrations/WooCommerce/WooCommerceIntegration.php',
		'RCS_Integrations_SMSIr_SmsIrClient'                  => 'includes/Integrations/SMSIr/SmsIrClient.php',
		'RCS_Integrations_Elementor_ElementorIntegration'     => 'includes/Integrations/Elementor/ElementorIntegration.php',
		'RCS_Admin_Admin'                                     => 'admin/Admin.php',
		'RCS_Admin_Pages_FormsPage'                           => 'admin/pages/FormsPage.php',
		'RCS_Admin_Pages_RequestsPage'                        => 'admin/pages/RequestsPage.php',
		'RCS_Admin_Pages_CouponsPage'                         => 'admin/pages/CouponsPage.php',
		'RCS_Admin_Pages_SmsPage'                             => 'admin/pages/SmsPage.php',
		'RCS_Admin_Pages_ReportsPage'                         => 'admin/pages/ReportsPage.php',
		'RCS_Admin_Pages_SettingsPage'                        => 'admin/pages/SettingsPage.php',
		'RCS_Frontend_Frontend'                               => 'frontend/Frontend.php',
		'RCS_Elementor_Elementor'                             => 'elementor/Elementor.php',
		'RCS_Elementor_FormWidget'                            => 'elementor/widgets/FormWidget.php',
	);

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register() {
		spl_autoload_register( array( $this, 'load' ) );
	}

	public function load( $class ) {
		if ( ! isset( $this->map[ $class ] ) ) {
			return false;
		}

		$file = RCS_PATH . $this->map[ $class ];

		if ( ! is_readable( $file ) ) {
			return false;
		}

		require_once $file;

		return true;
	}
}

RCS_Autoloader::instance()->register();

register_activation_hook( RCS_FILE, array( 'RCS_Core_Activator', 'activate' ) );
register_deactivation_hook( RCS_FILE, array( 'RCS_Core_Deactivator', 'deactivate' ) );

if ( ! defined( 'RCS_UNINSTALLING' ) ) {
	RCS_Core_Plugin::instance()->boot();
}
