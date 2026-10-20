<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Frontend_Frontend {

	const STYLE_HANDLE = 'rcs-form';

	private static $instance = null;

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );

		RCS_Forms_SubmissionHandler::instance()->register();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register_assets() {
		wp_register_style(
			self::STYLE_HANDLE,
			RCS_URL . 'frontend/assets/css/rcs-form.css',
			array(),
			RCS_VERSION
		);
	}

	public function enqueue_assets() {
		$this->register_assets();

		wp_enqueue_style( self::STYLE_HANDLE );
	}

	public function render_form( $form_id, array $args = array() ) {
		$form = RCS_Forms_FormManager::instance()->get_with_fields( $form_id );

		if ( ! $form ) {
			return '';
		}

		$args = wp_parse_args(
			$args,
			array(
				'show_title'       => true,
				'show_description' => true,
				'submit_text'      => __( 'Submit', 'request-coupon-system' ),
				'layout'           => '1',
			)
		);

		$fields     = $form['fields'];
		$is_member  = is_user_logged_in();
		$login_url  = $this->get_login_url();

		ob_start();
		include RCS_PATH . 'frontend/views/form.php';

		return ob_get_clean();
	}

	public function get_login_url() {
		$current = home_url( '/' );

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$current = home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		}

		return wp_login_url( $current );
	}
}
