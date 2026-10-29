<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Forms_FormRepository {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function insert( array $row ) {
		global $wpdb;

		$data = wp_parse_args(
			$row,
			array(
				'name'       => '',
				'status'     => 'draft',
				'settings'   => '{}',
				'created_by' => get_current_user_id(),
				'created_at' => current_time( 'mysql', true ),
				'updated_at' => current_time( 'mysql', true ),
			)
		);

		$inserted = $wpdb->insert(
			RCS_Database_Schema::forms(),
			$data,
			array( '%s', '%s', '%s', '%d', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return 0;
		}

		return (int) $wpdb->insert_id;
	}

	public function update( $form_id, array $row ) {
		global $wpdb;

		$row['updated_at'] = current_time( 'mysql', true );

		return (bool) $wpdb->update(
			RCS_Database_Schema::forms(),
			$row,
			array( 'id' => (int) $form_id )
		);
	}

	public function delete( $form_id ) {
		global $wpdb;

		return (bool) $wpdb->delete(
			RCS_Database_Schema::forms(),
			array( 'id' => (int) $form_id ),
			array( '%d' )
		);
	}

	public function find( $form_id ) {
		global $wpdb;

		$table = RCS_Database_Schema::forms();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $form_id ), ARRAY_A );

		if ( ! $row ) {
			return null;
		}

		$row['id'] = (int) $row['id'];

		return $row;
	}

	public function query( array $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'  => '',
				'orderby' => 'created_at',
				'order'   => 'DESC',
				'limit'   => 0,
				'offset'  => 0,
			)
		);

		$table  = RCS_Database_Schema::forms();
		$where  = '1=1';
		$order  = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
		$sortby = in_array( $args['orderby'], array( 'id', 'name', 'status', 'created_at', 'updated_at' ), true ) ? $args['orderby'] : 'created_at';

		if ( '' !== $args['status'] ) {
			$where .= $wpdb->prepare( ' AND status = %s', $args['status'] );
		}

		$sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY {$sortby} {$order}";

		if ( $args['limit'] > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', (int) $args['limit'], (int) $args['offset'] );
		}

		$rows = $wpdb->get_results( $sql, ARRAY_A );

		if ( ! is_array( $rows ) ) {
			return array();
		}

		foreach ( $rows as $index => $row ) {
			$rows[ $index ]['id'] = (int) $row['id'];
		}

		return $rows;
	}
}
