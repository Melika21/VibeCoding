<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Admin_Pages_FormsPage {

	const NONCE_SAVE   = 'rcs_save_form';
	const NONCE_DELETE = 'rcs_delete_form';

	private static $instance = null;

	private $capability = RCS_Core_Plugin::CAP_MANAGE_FORMS;

	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function enqueue_assets() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || false === strpos( (string) $screen->id, 'request-coupon-system' ) ) {
			return;
		}

		wp_enqueue_style( 'rcs-forms-admin', RCS_URL . 'admin/assets/css/rcs-forms.css', array(), RCS_VERSION );
		wp_enqueue_script( 'rcs-forms-admin', RCS_URL . 'admin/assets/js/rcs-forms.js', array(), RCS_VERSION, true );
	}

	public function render() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die(
				esc_html__( 'You do not have permission to access this page.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		$action = isset( $_GET['rcs_form_action'] ) ? sanitize_key( wp_unslash( $_GET['rcs_form_action'] ) ) : 'list';

		if ( 'delete' === $action ) {
			$this->handle_delete();

			return;
		}

		if ( 'new' === $action || 'edit' === $action ) {
			$this->handle_save();

			$this->render_builder( $action );

			return;
		}

		$this->render_list();
	}

	private function handle_save() {
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '' ) ) {
			return;
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_SAVE ) ) {
			wp_die(
				esc_html__( 'The form request could not be verified.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		$form_id = isset( $_POST['rcs_form_id'] ) ? absint( $_POST['rcs_form_id'] ) : 0;
		$name    = isset( $_POST['rcs_form_name'] ) ? sanitize_text_field( wp_unslash( $_POST['rcs_form_name'] ) ) : '';
		$status  = isset( $_POST['rcs_form_status'] ) ? sanitize_key( wp_unslash( $_POST['rcs_form_status'] ) ) : '';
		$description = isset( $_POST['rcs_form_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['rcs_form_description'] ) ) : '';
		$fields  = isset( $_POST['rcs_fields'] ) && is_array( $_POST['rcs_fields'] ) ? wp_unslash( $_POST['rcs_fields'] ) : array();

		$data = array(
			'name'     => $name,
			'status'   => $status,
			'settings' => array( 'description' => $description ),
			'fields'   => $fields,
		);

		$manager = RCS_Forms_FormManager::instance();

		if ( $form_id ) {
			$manager->update( $form_id, $data );
			$target_id = $form_id;
		} else {
			$target_id = $manager->create( $data );
		}

		if ( ! $target_id ) {
			return;
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => RCS_Admin_Admin::MENU_SLUG,
					'rcs_form_action' => 'edit',
					'rcs_form_id'     => (int) $target_id,
					'rcs_saved'       => 1,
				),
				admin_url( 'admin.php' )
			)
		);

		exit;
	}

	private function handle_delete() {
		$form_id = isset( $_GET['rcs_form_id'] ) ? absint( $_GET['rcs_form_id'] ) : 0;
		$nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! $form_id || ! wp_verify_nonce( $nonce, self::NONCE_DELETE . '_' . $form_id ) ) {
			wp_die(
				esc_html__( 'The form request could not be verified.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		RCS_Forms_FormManager::instance()->delete( $form_id );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => RCS_Admin_Admin::MENU_SLUG,
					'rcs_form_action' => 'list',
					'rcs_deleted'     => 1,
				),
				admin_url( 'admin.php' )
			)
		);

		exit;
	}

	private function render_list() {
		$forms      = RCS_Forms_FormManager::instance()->get_all();
		$field_types = RCS_Forms_FormManager::instance()->get_field_types();
		$deleted    = isset( $_GET['rcs_deleted'] ) ? (int) $_GET['rcs_deleted'] : 0;

		include RCS_PATH . 'admin/views/forms/index.php';
	}

	private function render_builder( $action ) {
		$form_id     = isset( $_GET['rcs_form_id'] ) ? absint( $_GET['rcs_form_id'] ) : 0;
		$manager     = RCS_Forms_FormManager::instance();
		$form        = $form_id ? $manager->get_with_fields( $form_id ) : null;
		$fields      = $form ? $form['fields'] : array();
		$field_types = $manager->get_field_types();
		$statuses    = $manager->get_statuses();
		$saved       = isset( $_GET['rcs_saved'] ) ? (int) $_GET['rcs_saved'] : 0;
		$is_new      = 'new' === $action || ! $form;

		include RCS_PATH . 'admin/views/forms/builder.php';
	}
}
