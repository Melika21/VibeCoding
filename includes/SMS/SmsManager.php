<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_SMS_SmsManager {

	private static $instance = null;

	private $sender;

	private $templates;

	private function __construct() {
		$this->sender    = RCS_SMS_SmsSender::instance();
		$this->templates = RCS_SMS_SmsTemplateManager::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_sender() {
		return $this->sender;
	}

	public function normalize_mobile( $mobile ) {
		$digits = preg_replace( '/[^0-9]/', '', (string) $mobile );

		if ( '' === $digits ) {
			return '';
		}

		if ( 0 === strpos( $digits, '0098' ) ) {
			$digits = substr( $digits, 4 );
		} elseif ( 0 === strpos( $digits, '98' ) && 12 === strlen( $digits ) ) {
			$digits = substr( $digits, 2 );
		}

		if ( 0 === strpos( $digits, '0' ) ) {
			$digits = substr( $digits, 1 );
		}

		return $digits;
	}

	public function get_admin_numbers() {
		$settings = get_option( 'rcs_sms_settings' );

		if ( ! is_array( $settings ) || empty( $settings['admin_numbers'] ) ) {
			return array();
		}

		$numbers = array();

		foreach ( (array) $settings['admin_numbers'] as $number ) {
			$normalized = $this->normalize_mobile( $number );

			if ( '' !== $normalized ) {
				$numbers[] = $normalized;
			}
		}

		return array_values( array_unique( $numbers ) );
	}

	public function create( $type, $recipient, array $values = array(), array $context = array() ) {
		global $wpdb;

		if ( ! $this->templates->is_valid_type( $type ) ) {
			return 0;
		}

		$recipient = $this->normalize_mobile( $recipient );

		if ( '' === $recipient ) {
			return 0;
		}

		$clean_values = array();

		foreach ( $values as $key => $value ) {
			$clean_values[ sanitize_key( $key ) ] = is_scalar( $value ) ? (string) $value : wp_json_encode( $value );
		}

		$inserted = $wpdb->insert(
			RCS_Database_Schema::sms(),
			array(
				'request_id'        => isset( $context['request_id'] ) ? (int) $context['request_id'] : 0,
				'user_id'           => isset( $context['user_id'] ) ? (int) $context['user_id'] : 0,
				'recipient'         => $recipient,
				'sms_type'          => $type,
				'pattern_id'        => $this->templates->get_pattern_id( $type ),
				'params'            => wp_json_encode( $clean_values ),
				'status'            => RCS_SMS_SmsStatus::PENDING,
				'provider_response' => '',
				'error_message'     => '',
				'retry_count'       => 0,
				'created_at'        => current_time( 'mysql', true ),
				'sent_at'           => null,
				'last_retry_at'     => null,
			),
			array( '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d' )
		);

		if ( ! $inserted ) {
			return 0;
		}

		return (int) $wpdb->insert_id;
	}

	public function send( $sms_id ) {
		global $wpdb;

		$sms = $this->get( $sms_id );

		if ( ! $sms ) {
			return false;
		}

		if ( RCS_SMS_SmsStatus::SENT === $sms['status'] ) {
			return false;
		}

		$result = $this->sender->send( $sms );

		$status = ! empty( $result['success'] ) ? RCS_SMS_SmsStatus::SENT : RCS_SMS_SmsStatus::FAILED;

		$wpdb->update(
			RCS_Database_Schema::sms(),
			array(
				'status'            => $status,
				'provider_response' => wp_json_encode( isset( $result['raw'] ) ? $result['raw'] : array() ),
				'error_message'     => empty( $result['success'] ) ? (string) $result['message'] : '',
				'sent_at'           => RCS_SMS_SmsStatus::SENT === $status ? current_time( 'mysql', true ) : null,
			),
			array( 'id' => (int) $sms_id )
		);

		RCS_Logs_LogManager::instance()->log(
			RCS_SMS_SmsStatus::SENT === $status ? 'SMS Sent' : 'SMS Failed',
			array(
				'sms_id'        => (int) $sms_id,
				'request_id'    => (int) $sms['request_id'],
				'result'        => RCS_SMS_SmsStatus::SENT === $status ? 'success' : 'failed',
				'error_message' => empty( $result['success'] ) ? (string) $result['message'] : '',
			)
		);

		return RCS_SMS_SmsStatus::SENT === $status;
	}

	public function send_to_customer( $type, $recipient, array $values = array(), array $context = array() ) {
		$sms_id = $this->create( $type, $recipient, $values, $context );

		if ( ! $sms_id ) {
			return 0;
		}

		$this->send( $sms_id );

		return $sms_id;
	}

	public function send_to_admin( $type, array $values = array(), array $context = array() ) {
		$ids = array();

		foreach ( $this->get_admin_numbers() as $number ) {
			$sms_id = $this->create( $type, $number, $values, $context );

			if ( ! $sms_id ) {
				continue;
			}

			$this->send( $sms_id );

			$ids[] = $sms_id;
		}

		return $ids;
	}

	public function get( $sms_id ) {
		global $wpdb;

		$table = RCS_Database_Schema::sms();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $sms_id ), ARRAY_A );

		if ( ! $row ) {
			return null;
		}

		$row['id']          = (int) $row['id'];
		$row['request_id']  = (int) $row['request_id'];
		$row['user_id']     = (int) $row['user_id'];
		$row['pattern_id']  = (int) $row['pattern_id'];
		$row['retry_count'] = (int) $row['retry_count'];

		return $row;
	}

	public function get_all( array $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'status'    => '',
				'sms_type'  => '',
				'request_id' => 0,
				'limit'     => 50,
				'offset'    => 0,
			)
		);

		$table = RCS_Database_Schema::sms();
		$where = '1=1';

		if ( '' !== $args['status'] ) {
			$where .= $wpdb->prepare( ' AND status = %s', $args['status'] );
		}

		if ( '' !== $args['sms_type'] ) {
			$where .= $wpdb->prepare( ' AND sms_type = %s', $args['sms_type'] );
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
			$rows[ $index ]['id']          = (int) $row['id'];
			$rows[ $index ]['request_id']  = (int) $row['request_id'];
			$rows[ $index ]['user_id']     = (int) $row['user_id'];
			$rows[ $index ]['pattern_id']  = (int) $row['pattern_id'];
			$rows[ $index ]['retry_count'] = (int) $row['retry_count'];
		}

		return $rows;
	}

	public function count_by_status( $status ) {
		global $wpdb;

		$table = RCS_Database_Schema::sms();

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", $status ) );
	}
}
