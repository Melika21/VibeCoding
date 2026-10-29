<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Admin_Pages_RequestsPage {

	const SLUG          = 'request-coupon-system-requests';
	const NONCE_APPROVE = 'rcs_approve_request';
	const NONCE_REJECT  = 'rcs_reject_request';

	const PER_PAGE = 20;

	private static $instance = null;

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

		if ( ! $screen || false === strpos( (string) $screen->id, self::SLUG ) ) {
			return;
		}

		wp_enqueue_style( 'rcs-requests-admin', RCS_URL . 'admin/assets/css/rcs-requests.css', array(), RCS_VERSION );
	}

	public function render() {
		if ( ! $this->can_view() ) {
			wp_die(
				esc_html__( 'You do not have permission to access this page.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		if ( 'POST' === $this->request_method() ) {
			$this->handle_post();
		}

		$action = isset( $_GET['rcs_request_action'] ) ? sanitize_key( wp_unslash( $_GET['rcs_request_action'] ) ) : '';

		if ( 'view' === $action ) {
			$this->render_detail();

			return;
		}

		$this->render_list();
	}

	private function render_list() {
		$manager = RCS_Requests_RequestManager::instance();

		$filters = array(
			'status'  => isset( $_GET['rcs_status'] ) ? sanitize_key( wp_unslash( $_GET['rcs_status'] ) ) : '',
			'form_id' => isset( $_GET['rcs_form_id'] ) ? absint( $_GET['rcs_form_id'] ) : 0,
			'search'  => isset( $_GET['rcs_search'] ) ? sanitize_text_field( wp_unslash( $_GET['rcs_search'] ) ) : '',
		);

		$paged   = isset( $_GET['rcs_paged'] ) ? max( 1, absint( $_GET['rcs_paged'] ) ) : 1;
		$total   = $manager->count_matching( $filters );
		$records = $manager->get_all(
			array_merge(
				$filters,
				array(
					'limit'  => self::PER_PAGE,
					'offset' => ( $paged - 1 ) * self::PER_PAGE,
				)
			)
		);

		$forms   = RCS_Forms_FormManager::instance()->get_all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
		$statuses = $manager->get_statuses();
		$counts  = $manager->count_by_status();
		$pages   = $total > 0 ? (int) ceil( $total / self::PER_PAGE ) : 1;
		$notice  = $this->take_notice();

		include RCS_PATH . 'admin/views/requests/index.php';
	}

	private function render_detail() {
		$request_id = isset( $_GET['rcs_request_id'] ) ? absint( $_GET['rcs_request_id'] ) : 0;
		$manager    = RCS_Requests_RequestManager::instance();
		$request    = $request_id ? $manager->get( $request_id ) : null;

		if ( ! $request ) {
			wp_die(
				esc_html__( 'The request does not exist.', 'request-coupon-system' ),
				'',
				array( 'response' => 404 )
			);
		}

		$form     = RCS_Forms_FormManager::instance()->get( (int) $request['form_id'] );
		$with     = RCS_Forms_FormManager::instance()->get_with_fields( (int) $request['form_id'] );
		$fields   = $with && isset( $with['fields'] ) ? $with['fields'] : array();
		$owner    = get_userdata( (int) $request['user_id'] );
		$revision = $manager->get_current_revision( $request_id );
		$history  = $manager->get_revision_history( $request_id );
		$coupon   = RCS_Coupons_CouponManager::instance()->get_by_request( $request_id );
		$mobile   = $manager->find_customer_mobile( $request_id );
		$statuses = $manager->get_statuses();

		$can_approve = $this->can_approve() && RCS_Requests_RequestAccess::instance()->can_approve( $request );
		$can_reject  = $this->can_reject() && RCS_Requests_RequestAccess::instance()->can_reject( $request );
		$notice      = $this->take_notice();

		include RCS_PATH . 'admin/views/requests/detail.php';
	}

	private function handle_post() {
		$action = isset( $_POST['rcs_request_action'] ) ? sanitize_key( wp_unslash( $_POST['rcs_request_action'] ) ) : '';

		if ( 'approve' === $action ) {
			$this->handle_approve();

			return;
		}

		if ( 'reject' === $action ) {
			$this->handle_reject();
		}
	}

	private function handle_approve() {
		$request_id = isset( $_POST['rcs_request_id'] ) ? absint( $_POST['rcs_request_id'] ) : 0;
		$nonce      = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( ! $request_id || ! wp_verify_nonce( $nonce, self::NONCE_APPROVE . '_' . $request_id ) ) {
			wp_die(
				esc_html__( 'The request could not be verified.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		if ( ! $this->can_approve() ) {
			wp_die(
				esc_html__( 'You are not allowed to approve requests.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		$request = RCS_Requests_RequestManager::instance()->get( $request_id );

		if ( ! $request ) {
			wp_die(
				esc_html__( 'The request does not exist.', 'request-coupon-system' ),
				'',
				array( 'response' => 404 )
			);
		}

		$amount         = isset( $_POST['rcs_discount'] ) ? (float) wp_unslash( $_POST['rcs_discount'] ) : 0.0;
		$maximum_amount = isset( $_POST['rcs_maximum_amount'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['rcs_maximum_amount'] ) ) ) : '';
		$mobile         = isset( $_POST['rcs_customer_mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['rcs_customer_mobile'] ) ) : '';

		$result = RCS_Approval_ApprovalManager::instance()->approve(
			$request_id,
			(int) $request['user_id'],
			array(
				'amount'          => $amount,
				'maximum_amount'  => $maximum_amount,
				'customer_mobile' => $mobile,
			)
		);

		if ( is_wp_error( $result ) ) {
			$this->set_notice( 'error', $result->get_error_message() );

			$this->redirect_to_detail( $request_id );

			return;
		}

		$this->redirect_to_detail( $request_id, 'rcs_approved', 1 );
	}

	private function handle_reject() {
		$request_id = isset( $_POST['rcs_request_id'] ) ? absint( $_POST['rcs_request_id'] ) : 0;
		$nonce      = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( ! $request_id || ! wp_verify_nonce( $nonce, self::NONCE_REJECT . '_' . $request_id ) ) {
			wp_die(
				esc_html__( 'The request could not be verified.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		if ( ! $this->can_reject() ) {
			wp_die(
				esc_html__( 'You are not allowed to reject requests.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		$reason = isset( $_POST['rcs_rejection_reason'] ) ? sanitize_textarea_field( wp_unslash( $_POST['rcs_rejection_reason'] ) ) : '';

		$result = RCS_Approval_ApprovalManager::instance()->reject( $request_id, $reason );

		if ( is_wp_error( $result ) ) {
			$this->set_notice( 'error', $result->get_error_message() );

			$this->redirect_to_detail( $request_id );

			return;
		}

		$this->redirect_to_detail( $request_id, 'rcs_rejected', 1 );
	}

	private function can_view() {
		return current_user_can( RCS_Core_Plugin::CAP_MANAGE_REQUESTS ) || current_user_can( RCS_Approval_ExpertRole::CAP_REVIEW );
	}

	private function can_approve() {
		return current_user_can( RCS_Approval_ExpertRole::CAP_APPROVE ) || current_user_can( RCS_Core_Plugin::CAP_MANAGE_REQUESTS );
	}

	private function can_reject() {
		return current_user_can( RCS_Approval_ExpertRole::CAP_REJECT ) || current_user_can( RCS_Core_Plugin::CAP_MANAGE_REQUESTS );
	}

	private function request_method() {
		return isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
	}

	private function set_notice( $type, $message ) {
		set_transient(
			'rcs_request_notice_' . get_current_user_id(),
			array(
				'type'    => $type,
				'message' => (string) $message,
			),
			60
		);
	}

	private function take_notice() {
		$key   = 'rcs_request_notice_' . get_current_user_id();
		$value = get_transient( $key );

		if ( $value ) {
			delete_transient( $key );
		}

		return is_array( $value ) ? $value : null;
	}

	private function redirect_to_detail( $request_id, $arg = '', $value = 0 ) {
		$args = array(
			'page'              => self::SLUG,
			'rcs_request_action' => 'view',
			'rcs_request_id'    => (int) $request_id,
		);

		if ( '' !== $arg ) {
			$args[ $arg ] = (int) $value;
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );

		exit;
	}
}
