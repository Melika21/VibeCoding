<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Logs_LogRepository {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function insert( array $data ) {
		global $wpdb;

		$defaults = array(
			'actor_user_id' => 0,
			'actor_role'    => '',
			'action'        => '',
			'request_id'    => 0,
			'revision_id'   => 0,
			'coupon_id'     => 0,
			'sms_id'        => 0,
			'ip_address'    => '',
			'result'        => 'success',
			'error_message' => '',
			'created_at'    => current_time( 'mysql', true ),
		);

		$row = wp_parse_args( $data, $defaults );

		$inserted = $wpdb->insert(
			RCS_Database_Schema::logs(),
			$row,
			array( '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return 0;
		}

		return (int) $wpdb->insert_id;
	}
}
