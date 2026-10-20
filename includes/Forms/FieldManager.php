<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Forms_FieldManager {

	private static $instance = null;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_field_types() {
		return array(
			'text'     => __( 'Text', 'request-coupon-system' ),
			'textarea' => __( 'Textarea', 'request-coupon-system' ),
			'number'   => __( 'Number', 'request-coupon-system' ),
			'email'    => __( 'Email', 'request-coupon-system' ),
			'mobile'   => __( 'Mobile', 'request-coupon-system' ),
			'select'   => __( 'Select', 'request-coupon-system' ),
			'radio'    => __( 'Radio', 'request-coupon-system' ),
			'checkbox' => __( 'Checkbox', 'request-coupon-system' ),
			'date'     => __( 'Date', 'request-coupon-system' ),
			'file'     => __( 'File Upload', 'request-coupon-system' ),
		);
	}

	public function get_fields( $form_id ) {
		global $wpdb;

		$table = RCS_Database_Schema::form_fields();
		$rows  = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE form_id = %d ORDER BY sort_order ASC, field_id ASC", (int) $form_id ),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		foreach ( $rows as $index => $row ) {
			$rows[ $index ]['options']          = $this->decode( $row['options'] );
			$rows[ $index ]['validation']        = $this->decode( $row['validation'] );
			$rows[ $index ]['conditional_logic'] = $this->decode( $row['conditional_logic'] );
			$rows[ $index ]['field_settings']    = $this->decode( $row['field_settings'] );
		}

		return $rows;
	}

	public function save_fields( $form_id, array $fields ) {
		global $wpdb;

		$this->delete_fields( $form_id );

		$table  = RCS_Database_Schema::form_fields();
		$insert = 0;

		foreach ( $fields as $index => $field ) {
			$normalized = $this->normalize_field( $field, $index );

			if ( ! $normalized ) {
				continue;
			}

			$wpdb->insert(
				$table,
				array(
					'form_id'          => (int) $form_id,
					'field_type'       => $normalized['field_type'],
					'field_key'        => $normalized['field_key'],
					'label'            => $normalized['label'],
					'options'          => wp_json_encode( $normalized['options'] ),
					'validation'       => wp_json_encode( $normalized['validation'] ),
					'conditional_logic' => wp_json_encode( $normalized['conditional_logic'] ),
					'step'             => $normalized['step'],
					'sort_order'       => $normalized['sort_order'],
					'field_settings'   => wp_json_encode( $normalized['field_settings'] ),
				),
				array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s' )
			);

			$insert++;
		}

		return $insert;
	}

	public function delete_fields( $form_id ) {
		global $wpdb;

		return (bool) $wpdb->delete(
			RCS_Database_Schema::form_fields(),
			array( 'form_id' => (int) $form_id ),
			array( '%d' )
		);
	}

	public function normalize_field( array $raw, $index = 0 ) {
		$types = $this->get_field_types();

		$type = isset( $raw['field_type'] ) ? sanitize_key( $raw['field_type'] ) : '';

		if ( ! isset( $types[ $type ] ) ) {
			return null;
		}

		$label = isset( $raw['label'] ) ? sanitize_text_field( $raw['label'] ) : '';

		if ( '' === $label ) {
			return null;
		}

		$key = isset( $raw['field_key'] ) ? sanitize_key( $raw['field_key'] ) : '';

		if ( '' === $key ) {
			$key = sanitize_key( 'field_' . $type . '_' . ( (int) $index + 1 ) );
		}

		$validation = isset( $raw['validation'] ) && is_array( $raw['validation'] ) ? $raw['validation'] : array();

		$options = array();

		if ( isset( $raw['options'] ) ) {
			$options = is_array( $raw['options'] ) ? $raw['options'] : explode( "\n", (string) $raw['options'] );
		}

		$clean_options = array();

		foreach ( $options as $option ) {
			$option = sanitize_text_field( (string) $option );

			if ( '' !== $option ) {
				$clean_options[] = $option;
			}
		}

		$conditional_logic = isset( $raw['conditional_logic'] ) && is_array( $raw['conditional_logic'] ) ? $raw['conditional_logic'] : array();
		$field_settings   = isset( $raw['field_settings'] ) && is_array( $raw['field_settings'] ) ? $raw['field_settings'] : array();

		return array(
			'field_type'       => $type,
			'field_key'        => substr( $key, 0, 191 ),
			'label'            => substr( $label, 0, 191 ),
			'options'          => $clean_options,
			'validation'       => array(
				'required'    => ! empty( $validation['required'] ),
				'placeholder' => isset( $validation['placeholder'] ) ? sanitize_text_field( $validation['placeholder'] ) : '',
				'min'         => isset( $validation['min'] ) ? (float) $validation['min'] : null,
				'max'         => isset( $validation['max'] ) ? (float) $validation['max'] : null,
			),
			'conditional_logic' => $conditional_logic,
			'step'             => max( 1, (int) ( isset( $raw['step'] ) ? $raw['step'] : 1 ) ),
			'sort_order'       => (int) ( isset( $raw['sort_order'] ) ? $raw['sort_order'] : $index ),
			'field_settings'   => $field_settings,
		);
	}

	public function render_field( array $field ) {
		$type       = isset( $field['field_type'] ) ? $field['field_type'] : 'text';
		$key        = isset( $field['field_key'] ) ? $field['field_key'] : '';
		$label      = isset( $field['label'] ) ? $field['label'] : '';
		$options    = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
		$validation = isset( $field['validation'] ) && is_array( $field['validation'] ) ? $field['validation'] : array();
		$required   = ! empty( $validation['required'] );
		$placeholder = isset( $validation['placeholder'] ) ? $validation['placeholder'] : '';
		$name       = 'rcs_field[' . $key . ']';
		$id         = 'rcs-field-' . $key;

		ob_start();
		?>
		<label class="rcs-form-label" for="<?php echo esc_attr( $id ); ?>">
			<?php echo esc_html( $label ); ?>
			<?php if ( $required ) : ?>
				<span class="rcs-required" aria-hidden="true">*</span>
			<?php endif; ?>
		</label>
		<?php
		switch ( $type ) {
			case 'textarea':
				printf(
					'<textarea class="rcs-form-input" id="%1$s" name="%2$s" rows="4" placeholder="%3$s"%4$s></textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $placeholder ),
					$required ? ' required' : ''
				);
				break;

			case 'number':
				printf(
					'<input class="rcs-form-input" type="number" id="%1$s" name="%2$s" placeholder="%3$s"%4$s />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $placeholder ),
					$required ? ' required' : ''
				);
				break;

			case 'email':
				printf(
					'<input class="rcs-form-input" type="email" id="%1$s" name="%2$s" placeholder="%3$s"%4$s />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $placeholder ),
					$required ? ' required' : ''
				);
				break;

			case 'mobile':
				printf(
					'<input class="rcs-form-input" type="tel" id="%1$s" name="%2$s" placeholder="%3$s" inputmode="numeric"%4$s />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $placeholder ),
					$required ? ' required' : ''
				);
				break;

			case 'date':
				printf(
					'<input class="rcs-form-input" type="date" id="%1$s" name="%2$s"%3$s />',
					esc_attr( $id ),
					esc_attr( $name ),
					$required ? ' required' : ''
				);
				break;

			case 'file':
				printf(
					'<input class="rcs-form-input" type="file" id="%1$s" name="%2$s"%3$s />',
					esc_attr( $id ),
					esc_attr( $name ),
					$required ? ' required' : ''
				);
				break;

			case 'select':
				printf(
					'<select class="rcs-form-input" id="%1$s" name="%2$s"%3$s>',
					esc_attr( $id ),
					esc_attr( $name ),
					$required ? ' required' : ''
				);
				printf( '<option value="">%s</option>', esc_html__( 'Select an option', 'request-coupon-system' ) );

				foreach ( $options as $option ) {
					printf( '<option value="%1$s">%1$s</option>', esc_attr( $option ) );
				}

				echo '</select>';
				break;

			case 'radio':
				echo '<span class="rcs-form-options">';

				foreach ( $options as $option ) {
					printf(
						'<label class="rcs-form-option"><input type="radio" name="%1$s" value="%2$s"%3$s /> <span>%4$s</span></label>',
						esc_attr( $name ),
						esc_attr( $option ),
						$required ? ' required' : '',
						esc_html( $option )
					);
				}

				echo '</span>';
				break;

			case 'checkbox':
				echo '<span class="rcs-form-options">';

				foreach ( $options as $option ) {
					printf(
						'<label class="rcs-form-option"><input type="checkbox" name="%1$s[]" value="%2$s" /> <span>%3$s</span></label>',
						esc_attr( $name ),
						esc_attr( $option ),
						esc_html( $option )
					);
				}

				echo '</span>';
				break;

			default:
				printf(
					'<input class="rcs-form-input" type="text" id="%1$s" name="%2$s" placeholder="%3$s"%4$s />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $placeholder ),
					$required ? ' required' : ''
				);
				break;
		}
		?>
		<?php
		return ob_get_clean();
	}

	private function decode( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( ! is_string( $value ) || '' === $value ) {
			return array();
		}

		$decoded = json_decode( $value, true );

		return is_array( $decoded ) ? $decoded : array();
	}
}
