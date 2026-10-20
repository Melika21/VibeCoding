<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Scheduler_Scheduler {

	const HOOK_COUPON_EXPIRY_REMINDER = 'rcs_coupon_expiry_reminder';
	const HOOK_SMS_RETRY              = 'rcs_sms_retry';

	private static $instance = null;

	private function __construct() {
		add_action( self::HOOK_COUPON_EXPIRY_REMINDER, array( $this, 'run_coupon_expiry_reminder' ) );
		add_action( self::HOOK_SMS_RETRY, array( $this, 'run_sms_retry' ) );
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function schedule_events() {
		if ( ! wp_next_scheduled( self::HOOK_COUPON_EXPIRY_REMINDER ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::HOOK_COUPON_EXPIRY_REMINDER );
		}

		if ( ! wp_next_scheduled( self::HOOK_SMS_RETRY ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', self::HOOK_SMS_RETRY );
		}
	}

	public static function unschedule_events() {
		wp_clear_scheduled_hook( self::HOOK_COUPON_EXPIRY_REMINDER );
		wp_clear_scheduled_hook( self::HOOK_SMS_RETRY );
	}

	public function run_coupon_expiry_reminder() {
		return;
	}

	public function run_sms_retry() {
		return;
	}
}
