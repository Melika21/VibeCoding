<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Elementor_Elementor {

	private static $instance = null;

	private function __construct() {
		RCS_Integrations_Elementor_ElementorIntegration::instance()->register();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
