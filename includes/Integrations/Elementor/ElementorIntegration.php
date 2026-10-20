<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Integrations_Elementor_ElementorIntegration {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function is_available() {
		return did_action( 'elementor/loaded' ) > 0;
	}

	public function register() {
		if ( ! self::is_available() ) {
			return;
		}

		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	public function register_widgets( $widgets_manager ) {
		try {
			require_once RCS_PATH . 'elementor/widgets/FormWidget.php';

			$widgets_manager->register( new RCS_Elementor_FormWidget() );
		} catch ( \Throwable $error ) {
			return;
		}
	}
}
