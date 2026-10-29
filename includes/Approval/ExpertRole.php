<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Approval_ExpertRole {

	const ROLE_SLUG = 'rcs_expert';

	const CAP_REVIEW  = 'rcs_review_requests';
	const CAP_APPROVE = 'rcs_approve_requests';
	const CAP_REJECT  = 'rcs_reject_requests';

	public static function capabilities() {
		return array(
			self::CAP_REVIEW,
			self::CAP_APPROVE,
			self::CAP_REJECT,
		);
	}

	public static function create() {
		$capabilities = array( 'read' => true );

		foreach ( self::capabilities() as $capability ) {
			$capabilities[ $capability ] = true;
		}

		$role = get_role( self::ROLE_SLUG );

		if ( ! $role ) {
			add_role( self::ROLE_SLUG, __( 'RCS Expert', 'request-coupon-system' ), $capabilities );

			return;
		}

		foreach ( self::capabilities() as $capability ) {
			if ( ! $role->has_cap( $capability ) ) {
				$role->add_cap( $capability );
			}
		}
	}

	public static function remove() {
		remove_role( self::ROLE_SLUG );
	}
}
