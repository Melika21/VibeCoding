<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Revisions_RevisionManager {

	private static $instance = null;

	private $repository;

	private function __construct() {
		$this->repository = RCS_Revisions_RevisionRepository::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get( $revision_id ) {
		$revision = $this->repository->find( $revision_id );

		if ( ! $revision ) {
			return null;
		}

		$revision['data'] = $this->decode( isset( $revision['submitted_data'] ) ? $revision['submitted_data'] : '' );

		return $revision;
	}

	public function get_current( $request_id ) {
		$revision = $this->repository->get_current( $request_id );

		if ( ! $revision ) {
			return null;
		}

		$revision['data'] = $this->decode( isset( $revision['submitted_data'] ) ? $revision['submitted_data'] : '' );

		return $revision;
	}

	public function get_history( $request_id ) {
		$revisions = $this->repository->get_all( $request_id );

		foreach ( $revisions as $index => $revision ) {
			$revisions[ $index ]['data'] = $this->decode( isset( $revision['submitted_data'] ) ? $revision['submitted_data'] : '' );
		}

		return $revisions;
	}

	public function mark_approved( $revision_id ) {
		if ( ! $revision_id ) {
			return false;
		}

		return $this->repository->update_status( $revision_id, RCS_Requests_RequestStatus::APPROVED );
	}

	public function mark_rejected( $revision_id, $reason = '' ) {
		if ( ! $revision_id ) {
			return false;
		}

		return $this->repository->update_status( $revision_id, RCS_Requests_RequestStatus::REJECTED, $reason );
	}

	public function decode( $raw ) {
		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			return array();
		}

		$decoded = json_decode( $raw, true );

		if ( ! is_array( $decoded ) ) {
			return array( 'value' => $raw );
		}

		return $decoded;
	}
}
