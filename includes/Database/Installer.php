<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Database_Installer {

	public static function install() {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		foreach ( RCS_Database_Schema::create_statements() as $statement ) {
			dbDelta( $statement );
		}

		update_option( 'rcs_db_version', RCS_DB_VERSION );
	}

	public static function maybe_upgrade() {
		if ( ! get_option( 'rcs_version' ) ) {
			return;
		}

		$installed = get_option( 'rcs_db_version' );

		if ( RCS_DB_VERSION === $installed ) {
			return;
		}

		self::install();
	}

	public static function drop() {
		RCS_Database_Schema::drop();

		delete_option( 'rcs_db_version' );
	}
}
