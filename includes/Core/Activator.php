<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Core_Activator {

	public static function activate() {
		RCS_Core_Requirements::store();

		RCS_Database_Installer::install();
		RCS_Approval_ExpertRole::create();

		self::grant_admin_capabilities();
		self::create_default_settings();
		RCS_Scheduler_Scheduler::schedule_events();

		update_option( 'rcs_version', RCS_VERSION );

		flush_rewrite_rules();
	}

	private static function grant_admin_capabilities() {
		$administrator = get_role( 'administrator' );

		if ( ! $administrator ) {
			return;
		}

		foreach ( RCS_Core_Plugin::admin_capabilities() as $capability ) {
			if ( ! $administrator->has_cap( $capability ) ) {
				$administrator->add_cap( $capability );
			}
		}
	}

	private static function create_default_settings() {
		add_option(
			'rcs_sms_settings',
			array(
				'api_key'       => '',
				'pattern_ids'   => array(),
				'sender_line'   => '',
				'admin_numbers' => array(),
			),
			'',
			'yes'
		);

		add_option( 'rcs_general_settings', array(), '', 'yes' );
	}
}
