<?php
/**
 * Plugin Name:       Request & Coupon System
 * Description:       Form creation, multi-step forms, expert approval, WooCommerce coupon management, SMS notifications, and audit logging.
 * Version:           1.0.0
 * Author:            Kilo
 * Author URI:        https://kilo.ai
 * License:           GPL-2.0+
 * Requires PHP:      7.4
 * Requires at least: 6.0
 * WC requires at least: 7.0
 * Text Domain:       request-coupon-system
 * Domain Path:       /languages
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants.
define( 'RCS_PLUGIN_FILE',    __FILE__ );
define( 'RCS_PLUGIN_DIR',     plugin_dir_path( __FILE__ ) );
define( 'RCS_PLUGIN_URL',     plugin_dir_url( __FILE__ ) );
define( 'RCS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'RCS_PLUGIN_VERSION', '1.0.0' );
define( 'RCS_DB_PREFIX',      'rcs_' );

/**
 * Manual class-to-file mapping for the autoloader.
 */
final class RCS_Autoloader {

    private static $instance = null;

    private $map = array(
        'Core_Plugin'                    => 'Core/Plugin.php',
        'Core_Activator'                 => 'Core/Activator.php',
        'Core_Deactivator'               => 'Core/Deactivator.php',
        'Core_Uninstaller'               => 'Core/Uninstaller.php',
        'Core_Requirements'              => 'Core/Requirements.php',

        'Forms_FormManager'              => 'Forms/FormManager.php',
        'Forms_FormRepository'           => 'Forms/FormRepository.php',
        'Forms_FieldManager'             => 'Forms/FieldManager.php',
        'Forms_SubmissionHandler'        => 'Forms/Submission-Handler.php',
        'Forms_Validation'               => 'Forms/Validation.php',
        'Forms_ConditionalLogic'         => 'Forms/ConditionalLogic.php',
        'Forms_DraftManager'             => 'Forms/DraftManager.php',
        'Forms_MultiStepManager'         => 'Forms/MultiStepManager.php',

        'Requests_RequestManager'        => 'Requests/RequestManager.php',
        'Requests_RequestRepository'     => 'Requests/RequestRepository.php',
        'Requests_RequestStatus'         => 'Requests/RequestStatus.php',
        'Requests_RequestAccess'         => 'Requests/RequestAccess.php',

        'Revisions_RevisionManager'      => 'Revisions/RevisionManager.php',
        'Revisions_RevisionRepository'   => 'Revisions/RevisionRepository.php',

        'Approval_ApprovalManager'       => 'Approval/ApprovalManager.php',
        'Approval_ApprovalValidator'     => 'Approval/ApprovalValidator.php',
        'Approval_ExpertRole'            => 'Approval/ExpertRole.php',

        'Coupons_CouponManager'          => 'Coupons/CouponManager.php',
        'Coupons_CouponValidator'        => 'Coupons/CouponValidator.php',
        'Coupons_CouponOwnership'        => 'Coupons/CouponOwnership.php',
        'Coupons_WooCommerceCoupon'      => 'Coupons/WooCommerceCoupon.php',

        'SMS_SmsManager'                 => 'SMS/SmsManager.php',
        'SMS_SmsTemplateManager'         => 'SMS/SmsTemplateManager.php',
        'SMS_SmsSender'                  => 'SMS/SmsSender.php',
        'SMS_SmsRetry'                   => 'SMS/SmsRetry.php',
        'SMS_SmsStatus'                  => 'SMS/SmsStatus.php',

        'Scheduler_Scheduler'            => 'Scheduler/Scheduler.php',

        'Logs_LogManager'                => 'Logs/LogManager.php',
        'Logs_LogRepository'             => 'Logs/LogRepository.php',

        'Reports_ReportManager'          => 'Reports/ReportManager.php',

        'Integrations_WooCommerce_WooCommerceIntegration' => 'Integrations/WooCommerce/WooCommerceIntegration.php',
        'Integrations_SMSIr_SmsIrClient'     => 'Integrations/SMSIr/SmsIrClient.php',
        'Integrations_Elementor_ElementorIntegration' => 'Integrations/Elementor/ElementorIntegration.php',

        'Database_Installer'             => 'Database/Installer.php',
        'Database_Schema'                => 'Database/Schema.php',

        'Admin_Admin'                    => '../admin/Admin.php',
        'Frontend_Frontend'              => '../frontend/Frontend.php',
        'Elementor_Elementor'            => '../elementor/Elementor.php',
        'Elementor_FormWidget'           => '../elementor/widgets/FormWidget.php',
    );

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->register();
        }
        return self::$instance;
    }

    private function register() {
        spl_autoload_register( array( $this, 'load' ) );
    }

    public function load( $class ) {
        $prefix = 'RCS_';
        $len    = strlen( $prefix );

        if ( strncmp( $prefix, $class, $len ) !== 0 ) {
            return false;
        }

        $relative = substr( $class, $len );
        $file     = isset( $this->map[ $relative ] )
            ? RCS_PLUGIN_DIR . 'includes/' . $this->map[ $relative ]
            : RCS_PLUGIN_DIR . 'includes/' . $relative . '.php';

        if ( is_readable( $file ) ) {
            require $file;
            return true;
        }

        return false;
    }
}

/**
 * Initialize the plugin.
 */
function rcs_init() {
    if ( ! class_exists( 'RCS_Core_Plugin' ) ) {
        return;
    }

    RCS_Core_Plugin::instance()->init();
}

register_activation_hook( __FILE__, array( 'RCS_Core_Activator', 'activate' ) );

register_deactivation_hook( __FILE__, array( 'RCS_Core_Deactivator', 'deactivate' ) );

register_uninstall_hook( __FILE__, array( 'RCS_Core_Uninstaller', 'uninstall' ) );

// Register autoloader immediately so it's available during plugin activation
// (plugins_loaded may not fire for an inactive plugin being activated).
RCS_Autoloader::instance();

add_action( 'plugins_loaded', 'rcs_init' );