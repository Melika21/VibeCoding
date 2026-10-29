<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Coupons_CouponManager {

	const EXPIRY_SECONDS = 604800;

	private static $instance = null;

	private $woo;

	private $validator;

	private $ownership;

	private function __construct() {
		$this->woo       = RCS_Coupons_WooCommerceCoupon::instance();
		$this->validator = RCS_Coupons_CouponValidator::instance();
		$this->ownership = RCS_Coupons_CouponOwnership::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function issue( $request_id, $user_id, array $args = array() ) {
		global $wpdb;

		if ( ! RCS_Coupons_WooCommerceCoupon::is_available() ) {
			return new WP_Error( 'rcs_woocommerce_missing', __( 'WooCommerce is not active, so the coupon could not be issued.', 'request-coupon-system' ) );
		}

		$request_id = (int) $request_id;
		$user_id    = (int) $user_id;

		$payload = wp_parse_args(
			$args,
			array(
				'request_id'     => $request_id,
				'user_id'        => $user_id,
				'amount'         => 0,
				'maximum_amount' => '',
				'code'           => '',
				'description'    => '',
				'date_expires'   => time() + self::EXPIRY_SECONDS,
			)
		);

		$payload['request_id'] = $request_id;
		$payload['user_id']    = $user_id;

		$validation = $this->validator->validate_issue( $payload );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$existing = $this->get_by_request( $request_id );

		if ( $existing ) {
			return new WP_Error( 'rcs_coupon_already_issued', __( 'This request already received a coupon.', 'request-coupon-system' ) );
		}

		if ( ! isset( $payload['email'] ) || '' === $payload['email'] ) {
			$user = get_userdata( $user_id );

			if ( $user && $user->user_email ) {
				$payload['email'] = $user->user_email;
			}
		}

		$coupon_id = $this->woo->create( $payload );

		if ( is_wp_error( $coupon_id ) ) {
			return $coupon_id;
		}

		$coupon = $this->woo->get( $coupon_id );
		$code   = $coupon ? $coupon->get_code() : $payload['code'];

		$inserted = $wpdb->insert(
			RCS_Database_Schema::coupons(),
			array(
				'request_id'   => $request_id,
				'user_id'      => $user_id,
				'wc_coupon_id' => (int) $coupon_id,
				'coupon_code'  => (string) $code,
				'created_at'   => current_time( 'mysql', true ),
				'approved_at'  => current_time( 'mysql', true ),
				'disabled_at'  => null,
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%d' )
		);

		if ( ! $inserted ) {
			$this->woo->disable( $coupon_id );

			return new WP_Error( 'rcs_coupon_relation_failed', __( 'The coupon relation could not be stored.', 'request-coupon-system' ) );
		}

		$relation_id = (int) $wpdb->insert_id;

		update_post_meta( $coupon_id, '_rcs_managed_coupon', 1 );
		update_post_meta( $coupon_id, '_rcs_request_id', $request_id );

		RCS_Logs_LogManager::instance()->log(
			'Coupon Created',
			array(
				'request_id' => $request_id,
				'coupon_id'  => $relation_id,
				'result'     => 'success',
			)
		);

		return $relation_id;
	}

	public function disable( $relation_id ) {
		global $wpdb;

		$record = $this->get( $relation_id );

		if ( ! $record ) {
			return false;
		}

		if ( ! empty( $record['disabled_at'] ) ) {
			return false;
		}

		if ( ! $this->woo->disable( (int) $record['wc_coupon_id'] ) ) {
			return false;
		}

		$wpdb->update(
			RCS_Database_Schema::coupons(),
			array( 'disabled_at' => current_time( 'mysql', true ) ),
			array( 'id' => (int) $relation_id )
		);

		RCS_Logs_LogManager::instance()->log(
			'Coupon Disabled',
			array(
				'request_id' => (int) $record['request_id'],
				'coupon_id'  => (int) $relation_id,
				'result'     => 'success',
			)
		);

		return true;
	}

	public function get( $relation_id ) {
		global $wpdb;

		$table = RCS_Database_Schema::coupons();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $relation_id ), ARRAY_A );

		if ( ! $row ) {
			return null;
		}

		return $this->cast( $row );
	}

	public function get_by_request( $request_id ) {
		global $wpdb;

		$table = RCS_Database_Schema::coupons();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE request_id = %d", (int) $request_id ), ARRAY_A );

		if ( ! $row ) {
			return null;
		}

		return $this->cast( $row );
	}

	public function get_by_wc_coupon( $wc_coupon_id ) {
		global $wpdb;

		$table = RCS_Database_Schema::coupons();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE wc_coupon_id = %d", (int) $wc_coupon_id ), ARRAY_A );

		if ( ! $row ) {
			return null;
		}

		return $this->cast( $row );
	}

	public function get_all( array $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'user_id'    => 0,
				'request_id' => 0,
				'limit'      => 50,
				'offset'     => 0,
			)
		);

		$table = RCS_Database_Schema::coupons();
		$where = '1=1';

		if ( $args['user_id'] > 0 ) {
			$where .= $wpdb->prepare( ' AND user_id = %d', (int) $args['user_id'] );
		}

		if ( $args['request_id'] > 0 ) {
			$where .= $wpdb->prepare( ' AND request_id = %d', (int) $args['request_id'] );
		}

		$sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY id DESC";

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

	public function count_all() {
		global $wpdb;

		$table = RCS_Database_Schema::coupons();

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	public function can_use( $relation_id, $user_id = 0 ) {
		$record = $this->get( $relation_id );

		if ( ! $record ) {
			return false;
		}

		return $this->ownership->can_use( $record, $user_id );
	}

	private function cast( array $row ) {
		$row['id']          = (int) $row['id'];
		$row['request_id']  = (int) $row['request_id'];
		$row['user_id']     = (int) $row['user_id'];
		$row['wc_coupon_id'] = (int) $row['wc_coupon_id'];

		return $row;
	}
}
