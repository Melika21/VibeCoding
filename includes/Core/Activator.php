<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Core_Activator {

	public static function activate() {
		RCS_Core_Requirements::store();

		RCS_Database_Installer::install();

		self::grant_roles();

		self::create_default_settings();
		RCS_Scheduler_Scheduler::schedule_events();

		update_option( 'rcs_version', RCS_VERSION );

		flush_rewrite_rules();
	}

	public static function grant_roles() {
		RCS_Approval_ExpertRole::create();

		self::grant_admin_capabilities();
		self::grant_expert_capabilities();

		update_option( 'rcs_roles_version', RCS_ROLES_VERSION );
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

	private static function grant_expert_capabilities() {
		$role = get_role( RCS_Approval_ExpertRole::ROLE_SLUG );

		if ( ! $role ) {
			return;
		}

		if ( ! $role->has_cap( RCS_Core_Plugin::CAP_MANAGE_REQUESTS ) ) {
			$role->add_cap( RCS_Core_Plugin::CAP_MANAGE_REQUESTS );
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
