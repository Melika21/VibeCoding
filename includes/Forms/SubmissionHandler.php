<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Forms_SubmissionHandler {

	const ACTION = 'rcs_submit_form';

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register() {
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle' ) );
		add_action( 'admin_post_nopriv_' . self::ACTION, array( $this, 'handle' ) );
	}

	public function handle() {
		if ( ! is_user_logged_in() ) {
			$this->redirect_to_login();

			return;
		}

		$form_id = isset( $_POST['rcs_form_id'] ) ? absint( $_POST['rcs_form_id'] ) : 0;
		$nonce   = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( ! $form_id || ! wp_verify_nonce( $nonce, self::ACTION . '_' . $form_id ) ) {
			wp_die(
				esc_html__( 'The form request could not be verified.', 'request-coupon-system' ),
				'',
				array( 'response' => 403 )
			);
		}

		$form = RCS_Forms_FormManager::instance()->get_with_fields( $form_id );

		if ( ! $form ) {
			wp_die(
				esc_html__( 'The requested form does not exist.', 'request-coupon-system' ),
				'',
				array( 'response' => 404 )
			);
		}

		$values = $this->collect_values( $form['fields'] );
		$errors = $this->validate( $form['fields'], $values );

		RCS_Logs_LogManager::instance()->log(
			'Form Submitted',
			array(
				'result'        => empty( $errors ) ? 'success' : 'failed',
				'error_message' => implode( ' ', $errors ),
			)
		);

		$this->redirect_back( $form_id, empty( $errors ), $errors );
	}

	private function collect_values( array $fields ) {
		$values = array();

		foreach ( $fields as $field ) {
			$key = isset( $field['field_key'] ) ? $field['field_key'] : '';

			if ( '' === $key || ! isset( $_POST['rcs_field'][ $key ] ) ) {
				continue;
			}

			$raw = wp_unslash( $_POST['rcs_field'][ $key ] );

			if ( is_array( $raw ) ) {
				$values[ $key ] = array_map( 'sanitize_text_field', $raw );

				continue;
			}

			$values[ $key ] = sanitize_text_field( $raw );
		}

		return $values;
	}

	private function validate( array $fields, array $values ) {
		$errors = array();

		foreach ( $fields as $field ) {
			$key        = isset( $field['field_key'] ) ? $field['field_key'] : '';
			$label      = isset( $field['label'] ) ? $field['label'] : '';
			$type       = isset( $field['field_type'] ) ? $field['field_type'] : 'text';
			$validation = isset( $field['validation'] ) && is_array( $field['validation'] ) ? $field['validation'] : array();
			$required   = ! empty( $validation['required'] );
			$value      = isset( $values[ $key ] ) ? $values[ $key ] : '';

			if ( $required && ( '' === $value || array() === $value ) ) {
				$errors[] = sprintf(
					/* translators: %s: field label. */
					__( '%s is required.', 'request-coupon-system' ),
					$label
				);

				continue;
			}

			if ( '' === $value || is_array( $value ) ) {
				continue;
			}

			if ( 'email' === $type && ! is_email( $value ) ) {
				$errors[] = sprintf(
					/* translators: %s: field label. */
					__( '%s must be a valid email address.', 'request-coupon-system' ),
					$label
				);
			}

			if ( 'mobile' === $type && ! preg_match( '/^[0-9+\-\s()]{7,20}$/', $value ) ) {
				$errors[] = sprintf(
					/* translators: %s: field label. */
					__( '%s must be a valid mobile number.', 'request-coupon-system' ),
					$label
				);
			}
		}

		return $errors;
	}

	private function redirect_to_login() {
		$referer = wp_get_referer();
		$target  = $referer ? $referer : home_url( '/' );

		$url = add_query_arg( 'rcs_login_required', '1', wp_login_url( $target ) );

		wp_safe_redirect( $url );

		exit;
	}

	private function redirect_back( $form_id, $success, array $errors ) {
		$referer = wp_get_referer();
		$target  = $referer ? $referer : home_url( '/' );

		$args = array( 'rcs_form_id' => (int) $form_id );

		if ( $success ) {
			$args['rcs_form_status'] = 'received';
			$args['rcs_form_message'] = __( 'Your submission was received. Request creation is enabled in the next implementation phase.', 'request-coupon-system' );
		} else {
			$args['rcs_form_status'] = 'error';
			$args['rcs_form_message'] = implode( ' ', $errors );
		}

		wp_safe_redirect( add_query_arg( $args, $target ) );

		exit;
	}
}
