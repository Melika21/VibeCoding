<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Core_Uninstaller {

	const COUPON_OWNER_META = '_rcs_managed_coupon';

	public static function uninstall() {
		RCS_Scheduler_Scheduler::unschedule_events();

		self::delete_plugin_coupons();

		RCS_Database_Installer::drop();

		self::delete_options();
		self::delete_capabilities();

		RCS_Approval_ExpertRole::remove();
	}

	private static function delete_plugin_coupons() {
		global $wpdb;

		try {
			if ( ! class_exists( 'WooCommerce' ) ) {
				return;
			}

			$table = RCS_Database_Schema::coupons();

			if ( ! RCS_Database_Schema::table_exists( $table ) ) {
				return;
			}

			$rows = $wpdb->get_results( "SELECT wc_coupon_id FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			foreach ( (array) $rows as $row ) {
				$coupon_id = isset( $row->wc_coupon_id ) ? (int) $row->wc_coupon_id : 0;

				if ( $coupon_id <= 0 ) {
					continue;
				}

				$coupon = get_post( $coupon_id );

				if ( ! $coupon || 'shop_coupon' !== $coupon->post_type ) {
					continue;
				}

				if ( ! get_post_meta( $coupon_id, self::COUPON_OWNER_META, true ) ) {
					continue;
				}

				wp_delete_post( $coupon_id, true );
			}
		} catch ( \Throwable $error ) {
			return;
		}
	}

	private static function delete_options() {
		$options = array(
			'rcs_db_version',
			'rcs_roles_version',
			'rcs_version',
			'rcs_sms_settings',
			'rcs_general_settings',
		);

		foreach ( $options as $option ) {
			delete_option( $option );
		}

		delete_transient( RCS_Core_Requirements::TRANSIENT_ERRORS );
	}

	private static function delete_capabilities() {
		$administrator = get_role( 'administrator' );

		if ( ! $administrator ) {
			return;
		}

		foreach ( RCS_Core_Plugin::admin_capabilities() as $capability ) {
			$administrator->remove_cap( $capability );
		}
	}
}
