<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Integrations_Elementor_ElementorIntegration {

	const CATEGORY = 'rcs';

	private static $instance = null;

	private function __construct() {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
	}

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

		$elementor = self::plugin();

		if ( $elementor && isset( $elementor->elements_manager ) ) {
			$this->register_category( $elementor->elements_manager );
		}

		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	public function register_category( $elements_manager ) {
		if ( ! is_object( $elements_manager ) || ! method_exists( $elements_manager, 'add_category' ) ) {
			return;
		}

		$elements_manager->add_category(
			self::CATEGORY,
			array(
				'title' => __( 'Request & Coupon', 'request-coupon-system' ),
				'icon'  => 'eicon-form-horizontal',
			)
		);
	}

	public function register_widgets( $widgets_manager ) {
		try {
			require_once RCS_PATH . 'elementor/widgets/FormWidget.php';

			$widgets_manager->register( new RCS_Elementor_FormWidget() );
		} catch ( \Throwable $error ) {
			return;
		}
	}

	public function widget_categories() {
		$elementor = self::plugin();

		if ( ! $elementor || ! isset( $elementor->elements_manager ) ) {
			return array( 'general' );
		}

		$categories = $elementor->elements_manager->get_categories();

		if ( is_array( $categories ) && isset( $categories[ self::CATEGORY ] ) ) {
			return array( self::CATEGORY );
		}

		return array( 'general' );
	}

	private static function plugin() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return null;
		}

		return \Elementor\Plugin::instance();
	}
}
