<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Frontend_Frontend {

	private static $instance = null;

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register_assets() {
		return;
	}
}
