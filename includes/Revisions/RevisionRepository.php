<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Revisions_RevisionRepository {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function find( $revision_id ) {
		global $wpdb;

		$table = RCS_Database_Schema::request_revisions();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE revision_id = %d", (int) $revision_id ), ARRAY_A );

		if ( ! $row ) {
			return null;
		}

		return $this->cast( $row );
	}

	public function get_current( $request_id ) {
		global $wpdb;

		$table = RCS_Database_Schema::request_revisions();
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE request_id = %d ORDER BY revision_number DESC, revision_id DESC LIMIT 1",
				(int) $request_id
			),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		return $this->cast( $row );
	}

	public function get_all( $request_id ) {
		global $wpdb;

		$table = RCS_Database_Schema::request_revisions();
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE request_id = %d ORDER BY revision_number DESC, revision_id DESC",
				(int) $request_id
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		foreach ( $rows as $index => $row ) {
			$rows[ $index ] = $this->cast( $row );
		}

		return $rows;
	}

	public function update_status( $revision_id, $status, $reason = '' ) {
		global $wpdb;

		$update = array(
			'status'          => $status,
			'rejection_reason' => '' === $reason ? null : $reason,
		);

		return (bool) $wpdb->update(
			RCS_Database_Schema::request_revisions(),
			$update,
			array( 'revision_id' => (int) $revision_id )
		);
	}

	private function cast( array $row ) {
		$row['revision_id']     = (int) $row['revision_id'];
		$row['request_id']      = (int) $row['request_id'];
		$row['revision_number'] = (int) $row['revision_number'];
		$row['submitted_by']    = (int) $row['submitted_by'];

		return $row;
	}
}
