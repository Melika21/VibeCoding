<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Approval_ApprovalValidator {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function validate_approve( $request_id, $user_id, array $args = array() ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rcs_approval_login_required', __( 'You must be logged in to approve a request.', 'request-coupon-system' ) );
		}

		if ( ! current_user_can( RCS_Approval_ExpertRole::CAP_APPROVE ) ) {
			return new WP_Error( 'rcs_approval_forbidden', __( 'You are not allowed to approve requests.', 'request-coupon-system' ) );
		}

		$request_id = (int) $request_id;
		$user_id    = (int) $user_id;

		if ( $request_id <= 0 ) {
			return new WP_Error( 'rcs_approval_request_required', __( 'A valid request is required.', 'request-coupon-system' ) );
		}

		$request = RCS_Requests_RequestManager::instance()->get( $request_id );

		if ( ! $request ) {
			return new WP_Error( 'rcs_approval_request_missing', __( 'The request does not exist.', 'request-coupon-system' ) );
		}

		if ( ! RCS_Requests_RequestAccess::instance()->can_approve( $request ) ) {
			return new WP_Error( 'rcs_approval_request_not_pending', __( 'Only a pending request can be approved.', 'request-coupon-system' ) );
		}

		$member = get_userdata( $user_id );

		if ( ! $member ) {
			return new WP_Error( 'rcs_approval_member_required', __( 'The request owner must be a registered member of the site.', 'request-coupon-system' ) );
		}

		$mobile = isset( $args['customer_mobile'] ) ? $args['customer_mobile'] : '';

		if ( '' === trim( (string) $mobile ) ) {
			return new WP_Error( 'rcs_approval_mobile_required', __( 'The mobile number of the request owner is required.', 'request-coupon-system' ) );
		}

		if ( ! RCS_Coupons_WooCommerceCoupon::is_available() ) {
			return new WP_Error( 'rcs_approval_woocommerce_missing', __( 'WooCommerce is not active, so the coupon could not be issued.', 'request-coupon-system' ) );
		}

		$existing = RCS_Coupons_CouponManager::instance()->get_by_request( $request_id );

		if ( $existing ) {
			return new WP_Error( 'rcs_approval_already_approved', __( 'This request already received a coupon.', 'request-coupon-system' ) );
		}

		return true;
	}

	public function validate_reject( $request_id, $reason ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rcs_reject_login_required', __( 'You must be logged in to reject a request.', 'request-coupon-system' ) );
		}

		if ( ! current_user_can( RCS_Approval_ExpertRole::CAP_REJECT ) ) {
			return new WP_Error( 'rcs_reject_forbidden', __( 'You are not allowed to reject requests.', 'request-coupon-system' ) );
		}

		$request_id = (int) $request_id;

		if ( $request_id <= 0 ) {
			return new WP_Error( 'rcs_reject_request_required', __( 'A valid request is required.', 'request-coupon-system' ) );
		}

		if ( '' === trim( (string) $reason ) ) {
			return new WP_Error( 'rcs_reject_reason_required', __( 'A rejection reason is required.', 'request-coupon-system' ) );
		}

		$request = RCS_Requests_RequestManager::instance()->get( $request_id );

		if ( ! $request ) {
			return new WP_Error( 'rcs_reject_request_missing', __( 'The request does not exist.', 'request-coupon-system' ) );
		}

		if ( ! RCS_Requests_RequestAccess::instance()->can_reject( $request ) ) {
			return new WP_Error( 'rcs_reject_request_not_pending', __( 'Only a pending request can be rejected.', 'request-coupon-system' ) );
		}

		return true;
	}
}
