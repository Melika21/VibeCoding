<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Revisions_RevisionRepository {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
