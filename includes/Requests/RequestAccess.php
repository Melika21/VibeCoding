<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Requests_RequestAccess {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function is_owner( $request, $user_id = 0 ) {
		if ( ! $request ) {
			return false;
		}

		$user_id = $user_id ? (int) $user_id : get_current_user_id();

		return (int) $request['user_id'] === (int) $user_id;
	}

	public function can_view( $request, $user_id = 0 ) {
		if ( ! $request ) {
			return false;
		}

		return $this->is_owner( $request, $user_id );
	}

	public function is_pending( $request ) {
		if ( ! $request ) {
			return false;
		}

		return RCS_Requests_RequestStatus::PENDING === $request['status'];
	}

	public function is_approved( $request ) {
		if ( ! $request ) {
			return false;
		}

		return RCS_Requests_RequestStatus::APPROVED === $request['status'];
	}

	public function can_edit( $request ) {
		if ( ! $request ) {
			return false;
		}

		return ! $this->is_approved( $request );
	}

	public function can_approve( $request ) {
		return $this->is_pending( $request );
	}

	public function can_reject( $request ) {
		return $this->is_pending( $request );
	}
}
