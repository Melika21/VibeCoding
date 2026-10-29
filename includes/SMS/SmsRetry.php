<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_SMS_SmsRetry {

	private static $instance = null;

	private $manager;

	private function __construct() {
		$this->manager = RCS_SMS_SmsManager::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function retry( $sms_id ) {
		global $wpdb;

		$sms = $this->manager->get( $sms_id );

		if ( ! $sms ) {
			return false;
		}

		if ( RCS_SMS_SmsStatus::FAILED !== $sms['status'] ) {
			return false;
		}

		$wpdb->update(
			RCS_Database_Schema::sms(),
			array(
				'status'        => RCS_SMS_SmsStatus::PENDING,
				'retry_count'   => (int) $sms['retry_count'] + 1,
				'last_retry_at' => current_time( 'mysql', true ),
			),
			array( 'id' => (int) $sms_id )
		);

		RCS_Logs_LogManager::instance()->log(
			'SMS Retried',
			array(
				'sms_id'     => (int) $sms_id,
				'request_id' => (int) $sms['request_id'],
			)
		);

		return $this->manager->send( $sms_id );
	}
}
