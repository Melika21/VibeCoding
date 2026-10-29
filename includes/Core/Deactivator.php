<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Core_Deactivator {

	public static function deactivate() {
		RCS_Scheduler_Scheduler::unschedule_events();

		flush_rewrite_rules();
	}
}
