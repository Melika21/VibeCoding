<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Database_Schema {

	public static function table( $name ) {
		return RCS_TABLE_PREFIX . $name;
	}

	public static function forms() {
		return self::table( 'forms' );
	}

	public static function form_fields() {
		return self::table( 'form_fields' );
	}

	public static function requests() {
		return self::table( 'requests' );
	}

	public static function request_revisions() {
		return self::table( 'request_revisions' );
	}

	public static function coupons() {
		return self::table( 'plugin_coupons' );
	}

	public static function sms() {
		return self::table( 'sms' );
	}

	public static function logs() {
		return self::table( 'logs' );
	}

	public static function tables() {
		return array(
			self::forms(),
			self::form_fields(),
			self::requests(),
			self::request_revisions(),
			self::coupons(),
			self::sms(),
			self::logs(),
		);
	}

	public static function create_statements() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$statements   = array();
		$statements[] = 'CREATE TABLE ' . self::forms() . " (
			id bigint(20) unsigned NOT NULL auto_increment,
			name varchar(191) NOT NULL default '',
			status varchar(20) NOT NULL default 'draft',
			settings longtext NULL,
			created_by bigint(20) unsigned NOT NULL default 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY form_status (status),
			KEY form_created_by (created_by)
		) {$charset_collate};";

		$statements[] = 'CREATE TABLE ' . self::form_fields() . " (
			field_id bigint(20) unsigned NOT NULL auto_increment,
			form_id bigint(20) unsigned NOT NULL default 0,
			field_type varchar(50) NOT NULL default '',
			field_key varchar(191) NOT NULL default '',
			label varchar(191) NOT NULL default '',
			options longtext NULL,
			validation longtext NULL,
			conditional_logic longtext NULL,
			step smallint(6) unsigned NOT NULL default 1,
			sort_order int(11) NOT NULL default 0,
			field_settings longtext NULL,
			PRIMARY KEY  (field_id),
			KEY field_form_id (form_id)
		) {$charset_collate};";

		$statements[] = 'CREATE TABLE ' . self::requests() . " (
			request_id bigint(20) unsigned NOT NULL auto_increment,
			form_id bigint(20) unsigned NOT NULL default 0,
			user_id bigint(20) unsigned NOT NULL default 0,
			current_revision_id bigint(20) unsigned NOT NULL default 0,
			status varchar(20) NOT NULL default 'draft',
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			approved_at datetime NULL DEFAULT NULL,
			rejected_at datetime NULL DEFAULT NULL,
			PRIMARY KEY  (request_id),
			KEY request_user_id (user_id),
			KEY request_status (status),
			KEY request_form_id (form_id)
		) {$charset_collate};";

		$statements[] = 'CREATE TABLE ' . self::request_revisions() . " (
			revision_id bigint(20) unsigned NOT NULL auto_increment,
			request_id bigint(20) unsigned NOT NULL default 0,
			revision_number int(11) NOT NULL default 1,
			submitted_by bigint(20) unsigned NOT NULL default 0,
			submitted_data longtext NULL,
			status varchar(20) NOT NULL default 'pending',
			created_at datetime NOT NULL,
			submitted_at datetime NULL DEFAULT NULL,
			rejection_reason text NULL,
			PRIMARY KEY  (revision_id),
			KEY revision_request_id (request_id),
			KEY revision_request_number (request_id,revision_number)
		) {$charset_collate};";

		$statements[] = 'CREATE TABLE ' . self::coupons() . " (
			id bigint(20) unsigned NOT NULL auto_increment,
			request_id bigint(20) unsigned NOT NULL default 0,
			user_id bigint(20) unsigned NOT NULL default 0,
			wc_coupon_id bigint(20) unsigned NOT NULL default 0,
			coupon_code varchar(100) NOT NULL default '',
			created_at datetime NOT NULL,
			approved_at datetime NULL DEFAULT NULL,
			disabled_at datetime NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY coupon_request_id (request_id),
			KEY coupon_user_id (user_id),
			KEY coupon_wc_coupon_id (wc_coupon_id)
		) {$charset_collate};";

		$statements[] = 'CREATE TABLE ' . self::sms() . " (
			id bigint(20) unsigned NOT NULL auto_increment,
			request_id bigint(20) unsigned NOT NULL default 0,
			user_id bigint(20) unsigned NOT NULL default 0,
			recipient varchar(32) NOT NULL default '',
			sms_type varchar(50) NOT NULL default '',
			pattern_id varchar(50) NOT NULL default '',
			params longtext NULL,
			status varchar(20) NOT NULL default 'pending',
			provider_response longtext NULL,
			error_message text NULL,
			retry_count int(11) NOT NULL default 0,
			created_at datetime NOT NULL,
			sent_at datetime NULL DEFAULT NULL,
			last_retry_at datetime NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY sms_request_id (request_id),
			KEY sms_status (status),
			KEY sms_type (sms_type)
		) {$charset_collate};";

		$statements[] = 'CREATE TABLE ' . self::logs() . " (
			id bigint(20) unsigned NOT NULL auto_increment,
			actor_user_id bigint(20) unsigned NOT NULL default 0,
			actor_role varchar(50) NOT NULL default '',
			action varchar(100) NOT NULL default '',
			request_id bigint(20) unsigned NOT NULL default 0,
			revision_id bigint(20) unsigned NOT NULL default 0,
			coupon_id bigint(20) unsigned NOT NULL default 0,
			sms_id bigint(20) unsigned NOT NULL default 0,
			ip_address varchar(100) NOT NULL default '',
			result varchar(20) NOT NULL default 'success',
			error_message text NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY log_actor_user_id (actor_user_id),
			KEY log_action (action),
			KEY log_request_id (request_id),
			KEY log_created_at (created_at)
		) {$charset_collate};";

		return $statements;
	}

	public static function table_exists( $table ) {
		global $wpdb;

		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		return $found === $table;
	}

	public static function drop() {
		global $wpdb;

		foreach ( self::tables() as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}
}
