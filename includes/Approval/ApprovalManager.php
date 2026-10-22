<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Approval_ApprovalManager {

	private static $instance = null;

	private $validator;

	private function __construct() {
		$this->validator = RCS_Approval_ApprovalValidator::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function approve( $request_id, $user_id, array $args = array() ) {
		$request_id = (int) $request_id;
		$user_id    = (int) $user_id;

		$args = wp_parse_args(
			$args,
			array(
				'amount'          => 0,
				'maximum_amount'  => '',
				'customer_mobile' => '',
			)
		);

		$validation = $this->validator->validate_approve( $request_id, $user_id, $args );

		if ( is_wp_error( $validation ) ) {
			RCS_Logs_LogManager::instance()->log(
				'Request Approved',
				array(
					'request_id'    => $request_id,
					'result'        => 'failed',
					'error_message' => $validation->get_error_message(),
				)
			);

			return $validation;
		}

		$coupon_args = array(
			'amount'          => (float) $args['amount'],
			'maximum_amount' => '' !== $args['maximum_amount'] && null !== $args['maximum_amount'] ? (float) $args['maximum_amount'] : '',
		);

		$relation_id = RCS_Coupons_CouponManager::instance()->issue( $request_id, $user_id, $coupon_args );

		if ( is_wp_error( $relation_id ) ) {
			RCS_Logs_LogManager::instance()->log(
				'Request Approved',
				array(
					'request_id'    => $request_id,
					'result'        => 'failed',
					'error_message' => $relation_id->get_error_message(),
				)
			);

			return $relation_id;
		}

		$record = RCS_Coupons_CouponManager::instance()->get( $relation_id );
		$member = get_userdata( $user_id );
		$values = $this->message_values( $record, $member, $request_id );

		RCS_Requests_RequestManager::instance()->mark_approved( $request_id );

		$context = array(
			'request_id' => $request_id,
			'user_id'    => $user_id,
		);

		$customer_sms_id = RCS_SMS_SmsManager::instance()->send_to_customer(
			RCS_SMS_SmsTemplateManager::TYPE_APPROVAL_CUSTOMER,
			$args['customer_mobile'],
			$values,
			$context
		);

		$admin_sms_ids = RCS_SMS_SmsManager::instance()->send_to_admin(
			RCS_SMS_SmsTemplateManager::TYPE_APPROVAL_ADMIN,
			$values,
			$context
		);

		RCS_Logs_LogManager::instance()->log(
			'Request Approved',
			array(
				'request_id' => $request_id,
				'coupon_id'  => (int) $relation_id,
				'result'     => 'success',
			)
		);

		return array(
			'coupon_id'            => (int) $relation_id,
			'coupon_code'          => isset( $record['coupon_code'] ) ? $record['coupon_code'] : '',
			'customer_sms_id'      => (int) $customer_sms_id,
			'customer_sms_status'  => $this->sms_status( $customer_sms_id ),
			'admin_sms_ids'        => $admin_sms_ids,
			'admin_sms_status'     => $this->admin_sms_status( $admin_sms_ids ),
		);
	}

	public function reject( $request_id, $reason, array $args = array() ) {
		$request_id = (int) $request_id;
		$reason     = trim( (string) $reason );

		$validation = $this->validator->validate_reject( $request_id, $reason );

		if ( is_wp_error( $validation ) ) {
			RCS_Logs_LogManager::instance()->log(
				'Request Rejected',
				array(
					'request_id'    => $request_id,
					'result'        => 'failed',
					'error_message' => $validation->get_error_message(),
				)
			);

			return $validation;
		}

		$request = RCS_Requests_RequestManager::instance()->get( $request_id );
		$user_id = $request ? (int) $request['user_id'] : 0;
		$member  = get_userdata( $user_id );

		RCS_Requests_RequestManager::instance()->mark_rejected( $request_id, $reason );

		$mobile = isset( $args['customer_mobile'] ) ? $args['customer_mobile'] : '';

		if ( '' === trim( (string) $mobile ) ) {
			$mobile = RCS_Requests_RequestManager::instance()->find_customer_mobile( $request_id );
		}

		$values = array(
			'name'    => $member ? $member->display_name : '',
			'reason'  => $reason,
			'request' => (string) $request_id,
		);

		$context = array(
			'request_id' => $request_id,
			'user_id'    => $user_id,
		);

		$customer_sms_id = RCS_SMS_SmsManager::instance()->send_to_customer(
			RCS_SMS_SmsTemplateManager::TYPE_REJECTION_CUSTOMER,
			$mobile,
			$values,
			$context
		);

		RCS_Logs_LogManager::instance()->log(
			'Request Rejected',
			array(
				'request_id' => $request_id,
				'result'     => 'success',
			)
		);

		return array(
			'request_id'          => $request_id,
			'customer_sms_id'     => (int) $customer_sms_id,
			'customer_sms_status' => $this->sms_status( $customer_sms_id ),
		);
	}

	private function message_values( $record, $member, $request_id ) {
		$values = array(
			'name'    => $member ? $member->display_name : '',
			'code'    => isset( $record['coupon_code'] ) ? $record['coupon_code'] : '',
			'request' => (string) $request_id,
		);

		$coupon = RCS_Coupons_WooCommerceCoupon::instance()->get( isset( $record['wc_coupon_id'] ) ? (int) $record['wc_coupon_id'] : 0 );

		if ( $coupon ) {
			$values['discount'] = (float) $coupon->get_amount() . '%';

			$expires = $coupon->get_date_expires();

			if ( $expires ) {
				$values['expiry'] = $expires->date( 'Y-m-d H:i' );
			}
		}

		return $values;
	}

	private function sms_status( $sms_id ) {
		if ( ! $sms_id ) {
			return '';
		}

		$record = RCS_SMS_SmsManager::instance()->get( $sms_id );

		return $record ? $record['status'] : '';
	}

	private function admin_sms_status( array $sms_ids ) {
		$statuses = array();

		foreach ( $sms_ids as $sms_id ) {
			$status = $this->sms_status( $sms_id );

			if ( '' !== $status ) {
				$statuses[] = $status;
			}
		}

		return $statuses;
	}
}
