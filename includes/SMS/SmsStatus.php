<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_SMS_SmsStatus {

	const PENDING = 'pending';
	const SENT    = 'sent';
	const FAILED  = 'failed';

	public static function all() {
		return array(
			self::PENDING,
			self::SENT,
			self::FAILED,
		);
	}

	public static function is_valid( $status ) {
		return in_array( $status, self::all(), true );
	}
}
