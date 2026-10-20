<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Forms_FormManager {

	const STATUS_DRAFT     = 'draft';
	const STATUS_PUBLISHED = 'published';

	private static $instance = null;

	private $repository;

	private $field_manager;

	private function __construct() {
		$this->repository    = RCS_Forms_FormRepository::instance();
		$this->field_manager = RCS_Forms_FieldManager::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_statuses() {
		return array(
			self::STATUS_DRAFT     => __( 'Draft', 'request-coupon-system' ),
			self::STATUS_PUBLISHED => __( 'Published', 'request-coupon-system' ),
		);
	}

	public function get_field_types() {
		return $this->field_manager->get_field_types();
	}

	public function create( array $data ) {
		$name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';

		if ( '' === $name ) {
			return 0;
		}

		$form_id = $this->repository->insert(
			array(
				'name'     => $name,
				'status'   => $this->normalize_status( isset( $data['status'] ) ? $data['status'] : '' ),
				'settings' => wp_json_encode( $this->normalize_settings( isset( $data['settings'] ) ? $data['settings'] : array() ) ),
			)
		);

		if ( ! $form_id ) {
			return 0;
		}

		if ( ! empty( $data['fields'] ) && is_array( $data['fields'] ) ) {
			$this->field_manager->save_fields( $form_id, $data['fields'] );
		}

		return $form_id;
	}

	public function update( $form_id, array $data ) {
		$form = $this->repository->find( $form_id );

		if ( ! $form ) {
			return false;
		}

		$row = array( 'updated_at' => current_time( 'mysql', true ) );

		if ( isset( $data['name'] ) ) {
			$name = sanitize_text_field( $data['name'] );

			if ( '' === $name ) {
				return false;
			}

			$row['name'] = $name;
		}

		if ( isset( $data['status'] ) ) {
			$row['status'] = $this->normalize_status( $data['status'] );
		}

		if ( isset( $data['settings'] ) ) {
			$row['settings'] = wp_json_encode( $this->normalize_settings( $data['settings'] ) );
		}

		$this->repository->update( $form_id, $row );

		if ( isset( $data['fields'] ) && is_array( $data['fields'] ) ) {
			$this->field_manager->save_fields( $form_id, $data['fields'] );
		}

		return true;
	}

	public function delete( $form_id ) {
		$form = $this->repository->find( $form_id );

		if ( ! $form ) {
			return false;
		}

		$this->field_manager->delete_fields( $form_id );

		return $this->repository->delete( $form_id );
	}

	public function get( $form_id ) {
		$form = $this->repository->find( $form_id );

		if ( ! $form ) {
			return null;
		}

		$form['settings'] = $this->decode_settings( $form['settings'] );

		return $form;
	}

	public function get_with_fields( $form_id ) {
		$form = $this->get( $form_id );

		if ( ! $form ) {
			return null;
		}

		$form['fields'] = $this->field_manager->get_fields( $form_id );

		return $form;
	}

	public function get_all( array $args = array() ) {
		$forms = $this->repository->query( $args );

		foreach ( $forms as $index => $form ) {
			$forms[ $index ]['settings'] = $this->decode_settings( $form['settings'] );
		}

		return $forms;
	}

	public function get_published() {
		return $this->repository->query(
			array(
				'status'  => self::STATUS_PUBLISHED,
				'orderby' => 'name',
				'order'   => 'ASC',
			)
		);
	}

	public function count_fields( $form_id ) {
		return count( $this->field_manager->get_fields( $form_id ) );
	}

	private function normalize_status( $status ) {
		$statuses = $this->get_statuses();

		if ( ! is_string( $status ) || ! isset( $statuses[ $status ] ) ) {
			return self::STATUS_DRAFT;
		}

		return $status;
	}

	private function normalize_settings( $settings ) {
		$clean = array();

		if ( is_array( $settings ) ) {
			if ( isset( $settings['description'] ) ) {
				$clean['description'] = sanitize_textarea_field( $settings['description'] );
			}
		}

		return $clean;
	}

	private function decode_settings( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( ! is_string( $value ) || '' === $value ) {
			return array();
		}

		$decoded = json_decode( $value, true );

		return is_array( $decoded ) ? $decoded : array();
	}
}
