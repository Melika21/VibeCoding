<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( '\Elementor\Widget_Base' ) ) {

	final class RCS_Elementor_FormWidget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'rcs_form';
		}

		public function get_title() {
			return __( 'RCS Form', 'request-coupon-system' );
		}

		public function get_categories() {
			return RCS_Integrations_Elementor_ElementorIntegration::instance()->widget_categories();
		}

		public function get_icon() {
			return 'eicon-form-horizontal';
		}

		public function get_keywords() {
			return array( 'form', 'rcs', 'request', 'coupon' );
		}

		public function get_style_depends() {
			return array( RCS_Frontend_Frontend::STYLE_HANDLE );
		}

		public function get_script_depends() {
			return array();
		}

		protected function is_dynamic_content(): bool {
			return true;
		}

		protected function register_controls() {
			$this->start_controls_section(
				'rcs_form_section',
				array(
					'label' => __( 'Form', 'request-coupon-system' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_control(
				'form_id',
				array(
					'type'    => \Elementor\Controls_Manager::SELECT,
					'label'   => __( 'Form', 'request-coupon-system' ),
					'options' => $this->form_options(),
					'default' => '',
				)
			);

			$this->add_control(
				'show_title',
				array(
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label'        => __( 'Show form title', 'request-coupon-system' ),
					'default'      => 'yes',
					'return_value' => 'yes',
				)
			);

			$this->add_control(
				'show_description',
				array(
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label'        => __( 'Show form description', 'request-coupon-system' ),
					'default'      => 'yes',
					'return_value' => 'yes',
				)
			);

			$this->add_control(
				'submit_text',
				array(
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label'       => __( 'Submit button text', 'request-coupon-system' ),
					'default'     => __( 'Submit', 'request-coupon-system' ),
					'placeholder' => __( 'Submit', 'request-coupon-system' ),
				)
			);

			$this->add_control(
				'field_layout',
				array(
					'type'    => \Elementor\Controls_Manager::SELECT,
					'label'   => __( 'Fields per row', 'request-coupon-system' ),
					'options' => array(
						'1' => __( 'One column', 'request-coupon-system' ),
						'2' => __( 'Two columns', 'request-coupon-system' ),
						'3' => __( 'Three columns', 'request-coupon-system' ),
					),
					'default' => '1',
				)
			);

			$this->end_controls_section();
		}

		protected function render() {
			$settings = $this->get_settings_for_display();
			$form_id  = isset( $settings['form_id'] ) ? (int) $settings['form_id'] : 0;

			if ( ! $form_id ) {
				echo '<p class="rcs-form-placeholder">' . esc_html__( 'Select a form in the widget settings.', 'request-coupon-system' ) . '</p>';

				return;
			}

			$frontend = RCS_Frontend_Frontend::instance();

			$frontend->enqueue_assets();

			$html = $frontend->render_form(
				$form_id,
				array(
					'show_title'       => ! empty( $settings['show_title'] ),
					'show_description' => ! empty( $settings['show_description'] ),
					'submit_text'      => isset( $settings['submit_text'] ) && '' !== $settings['submit_text'] ? $settings['submit_text'] : __( 'Submit', 'request-coupon-system' ),
					'layout'           => isset( $settings['field_layout'] ) ? (string) $settings['field_layout'] : '1',
				)
			);

			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		private function form_options() {
			$options = array( '' => __( '— Select a form —', 'request-coupon-system' ) );

			$forms = RCS_Forms_FormManager::instance()->get_published();

			foreach ( $forms as $form ) {
				$options[ (int) $form['id'] ] = $form['name'];
			}

			return $options;
		}
	}
}
