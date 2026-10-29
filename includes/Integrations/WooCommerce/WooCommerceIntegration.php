<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Integrations_WooCommerce_WooCommerceIntegration {

	private static $instance = null;

	private function __construct() {
		if ( ! self::is_available() ) {
			return;
		}

		add_action( 'woocommerce_init', array( $this, 'on_woocommerce_init' ) );
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function is_available() {
		return class_exists( 'WooCommerce' );
	}

	public function on_woocommerce_init() {
		return;
	}
}
