<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Requests_RequestStatus {

	const DRAFT    = 'draft';
	const PENDING  = 'pending';
	const REJECTED = 'rejected';
	const APPROVED = 'approved';

	public static function all() {
		return array(
			self::DRAFT,
			self::PENDING,
			self::REJECTED,
			self::APPROVED,
		);
	}

	public static function is_valid( $status ) {
		return in_array( $status, self::all(), true );
	}
}
