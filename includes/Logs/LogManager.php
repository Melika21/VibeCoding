<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Logs_LogManager {

	private static $instance = null;

	private $repository;

	private function __construct() {
		$this->repository = RCS_Logs_LogRepository::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function log( $action, array $context = array() ) {
		$row = array(
			'actor_user_id' => get_current_user_id(),
			'actor_role'    => $this->current_role(),
			'action'        => sanitize_text_field( $action ),
			'ip_address'    => $this->ip_address(),
		);

		foreach ( array( 'request_id', 'revision_id', 'coupon_id', 'sms_id' ) as $numeric ) {
			if ( isset( $context[ $numeric ] ) ) {
				$row[ $numeric ] = (int) $context[ $numeric ];
			}
		}

		if ( isset( $context['result'] ) ) {
			$row['result'] = sanitize_text_field( $context['result'] );
		}

		if ( isset( $context['error_message'] ) ) {
			$row['error_message'] = sanitize_text_field( $context['error_message'] );
		}

		return $this->repository->insert( $row );
	}

	private function current_role() {
		$user = wp_get_current_user();

		if ( ! $user || ! $user->roles ) {
			return '';
		}

		return (string) $user->roles[0];
	}

	private function ip_address() {
		if ( empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}
}
