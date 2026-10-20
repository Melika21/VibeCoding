<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Elementor_FormWidget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'rcs_form';
	}

	public function get_title() {
		return __( 'RCS Form', 'request-coupon-system' );
	}

	public function get_categories() {
		if ( taxonomy_exists( 'elementor_element_category' ) ) {
			$term = get_term_by( 'slug', 'rcs', 'elementor_element_category' );

			if ( $term ) {
				return array( 'rcs' );
			}
		}

		return array( 'general' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_keywords() {
		return array( 'form', 'rcs', 'request' );
	}

	protected function register_controls() {
		return;
	}

	protected function render() {
		echo '<div class="rcs-form-placeholder">' . esc_html__( 'The form renderer is part of the implementation phase.', 'request-coupon-system' ) . '</div>';
	}
}
