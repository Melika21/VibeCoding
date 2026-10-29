<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Requests_RequestRepository {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function find( $request_id ) {
		global $wpdb;

		$table = RCS_Database_Schema::requests();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE request_id = %d", (int) $request_id ), ARRAY_A );

		if ( ! $row ) {
			return null;
		}

		return $this->cast( $row );
	}

	public function query( array $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'  => '',
				'form_id' => 0,
				'user_id' => 0,
				'search'  => '',
				'orderby' => 'created_at',
				'order'   => 'DESC',
				'limit'   => 20,
				'offset'  => 0,
			)
		);

		$table  = RCS_Database_Schema::requests();
		$where  = $this->build_where( $args );
		$sortby = in_array( $args['orderby'], array( 'request_id', 'form_id', 'user_id', 'status', 'created_at', 'updated_at' ), true ) ? $args['orderby'] : 'created_at';
		$order  = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY {$sortby} {$order}";

		if ( $args['limit'] > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', (int) $args['limit'], (int) $args['offset'] );
		}

		$rows = $wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $rows ) ) {
			return array();
		}

		foreach ( $rows as $index => $row ) {
			$rows[ $index ] = $this->cast( $row );
		}

		return $rows;
	}

	public function count_matching( array $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'  => '',
				'form_id' => 0,
				'user_id' => 0,
				'search'  => '',
			)
		);

		$table = RCS_Database_Schema::requests();
		$where = $this->build_where( $args );

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where}" );
	}

	public function count_by_status() {
		global $wpdb;

		$table = RCS_Database_Schema::requests();
		$rows  = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status", ARRAY_A );

		$counts = array();

		foreach ( RCS_Requests_RequestStatus::all() as $status ) {
			$counts[ $status ] = 0;
		}

		foreach ( (array) $rows as $row ) {
			$counts[ $row['status'] ] = (int) $row['total'];
		}

		return $counts;
	}

	public function update_status( $request_id, $status, array $data = array() ) {
		global $wpdb;

		$update = array_merge(
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql', true ),
			),
			$data
		);

		return (bool) $wpdb->update(
			RCS_Database_Schema::requests(),
			$update,
			array( 'request_id' => (int) $request_id )
		);
	}

	private function build_where( array $args ) {
		global $wpdb;

		$where = '1=1';

		if ( '' !== $args['status'] && RCS_Requests_RequestStatus::is_valid( $args['status'] ) ) {
			$where .= $wpdb->prepare( ' AND status = %s', $args['status'] );
		}

		if ( $args['form_id'] > 0 ) {
			$where .= $wpdb->prepare( ' AND form_id = %d', (int) $args['form_id'] );
		}

		if ( $args['user_id'] > 0 ) {
			$where .= $wpdb->prepare( ' AND user_id = %d', (int) $args['user_id'] );
		}

		$search = trim( (string) $args['search'] );

		if ( '' !== $search ) {
			$like   = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= $wpdb->prepare( ' AND ( request_id LIKE %s OR user_id LIKE %s )', $like, $like );
		}

		return $where;
	}

	private function cast( array $row ) {
		$row['request_id']          = (int) $row['request_id'];
		$row['form_id']             = (int) $row['form_id'];
		$row['user_id']             = (int) $row['user_id'];
		$row['current_revision_id'] = (int) $row['current_revision_id'];

		return $row;
	}
}
