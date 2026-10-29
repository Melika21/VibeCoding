<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Requests_RequestManager {

	private static $instance = null;

	private $repository;

	private $revisions;

	private function __construct() {
		$this->repository = RCS_Requests_RequestRepository::instance();
		$this->revisions  = RCS_Revisions_RevisionManager::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get( $request_id ) {
		return $this->repository->find( $request_id );
	}

	public function get_all( array $args = array() ) {
		return $this->repository->query( $args );
	}

	public function count_matching( array $args = array() ) {
		return $this->repository->count_matching( $args );
	}

	public function count_by_status() {
		return $this->repository->count_by_status();
	}

	public function get_statuses() {
		return array(
			RCS_Requests_RequestStatus::DRAFT    => __( 'Draft', 'request-coupon-system' ),
			RCS_Requests_RequestStatus::PENDING  => __( 'Pending', 'request-coupon-system' ),
			RCS_Requests_RequestStatus::REJECTED => __( 'Rejected', 'request-coupon-system' ),
			RCS_Requests_RequestStatus::APPROVED => __( 'Approved', 'request-coupon-system' ),
		);
	}

	public function get_status_label( $status ) {
		$statuses = $this->get_statuses();

		if ( isset( $statuses[ $status ] ) ) {
			return $statuses[ $status ];
		}

		return (string) $status;
	}

	public function get_current_revision( $request_id ) {
		return $this->revisions->get_current( $request_id );
	}

	public function get_revision_history( $request_id ) {
		return $this->revisions->get_history( $request_id );
	}

	public function mark_approved( $request_id ) {
		$request = $this->get( $request_id );

		if ( ! $request ) {
			return false;
		}

		$updated = $this->repository->update_status(
			$request_id,
			RCS_Requests_RequestStatus::APPROVED,
			array( 'approved_at' => current_time( 'mysql', true ) )
		);

		$revision = $this->revisions->get_current( $request_id );

		if ( $revision ) {
			$this->revisions->mark_approved( $revision['revision_id'] );
		}

		return $updated;
	}

	public function mark_rejected( $request_id, $reason = '' ) {
		$request = $this->get( $request_id );

		if ( ! $request ) {
			return false;
		}

		$updated = $this->repository->update_status(
			$request_id,
			RCS_Requests_RequestStatus::REJECTED,
			array( 'rejected_at' => current_time( 'mysql', true ) )
		);

		$revision = $this->revisions->get_current( $request_id );

		if ( $revision ) {
			$this->revisions->mark_rejected( $revision['revision_id'], $reason );
		}

		return $updated;
	}

	public function find_customer_mobile( $request_id ) {
		$revision = $this->revisions->get_current( $request_id );

		if ( ! $revision || empty( $revision['data'] ) ) {
			return '';
		}

		foreach ( $revision['data'] as $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			$mobile = $this->match_mobile( (string) $value );

			if ( '' !== $mobile ) {
				return $mobile;
			}
		}

		return '';
	}

	private function match_mobile( $value ) {
		$value = trim( $value );

		if ( '' === $value ) {
			return '';
		}

		$digits = preg_replace( '/[^0-9]/', '', $value );

		if ( 12 === strlen( $digits ) && 0 === strpos( $digits, '98' ) ) {
			$digits = substr( $digits, 2 );
		}

		if ( 13 === strlen( $digits ) && 0 === strpos( $digits, '0098' ) ) {
			$digits = substr( $digits, 4 );
		}

		if ( 10 === strlen( $digits ) && 0 === strpos( $digits, '9' ) ) {
			return '0' . $digits;
		}

		if ( 11 === strlen( $digits ) && 0 === strpos( $digits, '09' ) ) {
			return $digits;
		}

		return '';
	}
}
