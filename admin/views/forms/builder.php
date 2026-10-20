<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rcs_form        = isset( $form ) && $form ? $form : array();
$rcs_fields      = isset( $fields ) ? $fields : array();
$rcs_field_types = isset( $field_types ) ? $field_types : array();
$rcs_statuses    = isset( $statuses ) ? $statuses : array();
$rcs_saved       = isset( $saved ) ? (int) $saved : 0;
$rcs_is_new      = isset( $is_new ) ? (bool) $is_new : true;
$rcs_form_id     = ! empty( $rcs_form['id'] ) ? (int) $rcs_form['id'] : 0;
$rcs_name        = ! empty( $rcs_form['name'] ) ? $rcs_form['name'] : '';
$rcs_status      = ! empty( $rcs_form['status'] ) ? $rcs_form['status'] : 'draft';
$rcs_description = ! empty( $rcs_form['settings']['description'] ) ? $rcs_form['settings']['description'] : '';
?>
<div class="wrap rcs-forms rcs-form-builder">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( $rcs_is_new ? __( 'Add new form', 'request-coupon-system' ) : __( 'Edit form', 'request-coupon-system' ) ); ?>
	</h1>
	<a href="<?php echo esc_url( add_query_arg( array( 'page' => RCS_Admin_Admin::MENU_SLUG ), admin_url( 'admin.php' ) ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Back to forms', 'request-coupon-system' ); ?>
	</a>
	<hr class="wp-header-end" />

	<?php if ( $rcs_saved ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'The form was saved.', 'request-coupon-system' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => RCS_Admin_Admin::MENU_SLUG, 'rcs_form_action' => $rcs_is_new ? 'new' : 'edit', 'rcs_form_id' => $rcs_form_id ), admin_url( 'admin.php' ) ) ); ?>" id="rcs-form-builder">
		<?php wp_nonce_field( RCS_Admin_Pages_FormsPage::NONCE_SAVE ); ?>
		<input type="hidden" name="rcs_form_id" value="<?php echo esc_attr( $rcs_form_id ); ?>" />

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label for="rcs-form-name"><?php esc_html_e( 'Form name', 'request-coupon-system' ); ?></label></th>
					<td><input type="text" id="rcs-form-name" name="rcs_form_name" class="regular-text" value="<?php echo esc_attr( $rcs_name ); ?>" required /></td>
				</tr>
				<tr>
					<th scope="row"><label for="rcs-form-status"><?php esc_html_e( 'Status', 'request-coupon-system' ); ?></label></th>
					<td>
						<select id="rcs-form-status" name="rcs_form_status">
							<?php foreach ( $rcs_statuses as $rcs_status_key => $rcs_status_label ) : ?>
								<option value="<?php echo esc_attr( $rcs_status_key ); ?>" <?php selected( $rcs_status, $rcs_status_key ); ?>><?php echo esc_html( $rcs_status_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rcs-form-description"><?php esc_html_e( 'Description', 'request-coupon-system' ); ?></label></th>
					<td><textarea id="rcs-form-description" name="rcs_form_description" rows="3" class="large-text"><?php echo esc_textarea( $rcs_description ); ?></textarea></td>
				</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Fields', 'request-coupon-system' ); ?></h2>
		<p class="description"><?php esc_html_e( 'The field key is the name used in the submitted data and must be unique inside the form.', 'request-coupon-system' ); ?></p>

		<table class="widefat striped rcs-fields-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Type', 'request-coupon-system' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Key', 'request-coupon-system' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Label', 'request-coupon-system' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Required', 'request-coupon-system' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Options (one per line)', 'request-coupon-system' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Step', 'request-coupon-system' ); ?></th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody id="rcs-fields-body">
				<?php foreach ( $rcs_fields as $rcs_index => $rcs_field ) : ?>
					<?php
					$rcs_required  = ! empty( $rcs_field['validation']['required'] );
					$rcs_options   = ! empty( $rcs_field['options'] ) && is_array( $rcs_field['options'] ) ? implode( "\n", $rcs_field['options'] ) : '';
					$rcs_placeholder = ! empty( $rcs_field['validation']['placeholder'] ) ? $rcs_field['validation']['placeholder'] : '';
					?>
					<tr class="rcs-field-row">
						<td>
							<select name="rcs_fields[<?php echo esc_attr( (string) $rcs_index ); ?>][field_type]" class="rcs-field-type">
								<?php foreach ( $rcs_field_types as $rcs_type_key => $rcs_type_label ) : ?>
									<option value="<?php echo esc_attr( $rcs_type_key ); ?>" <?php selected( $rcs_field['field_type'], $rcs_type_key ); ?>><?php echo esc_html( $rcs_type_label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td><input type="text" name="rcs_fields[<?php echo esc_attr( (string) $rcs_index ); ?>][field_key]" value="<?php echo esc_attr( $rcs_field['field_key'] ); ?>" /></td>
						<td><input type="text" name="rcs_fields[<?php echo esc_attr( (string) $rcs_index ); ?>][label]" value="<?php echo esc_attr( $rcs_field['label'] ); ?>" /></td>
						<td><input type="checkbox" name="rcs_fields[<?php echo esc_attr( (string) $rcs_index ); ?>][validation][required]" value="1" <?php checked( $rcs_required ); ?> /></td>
						<td><textarea name="rcs_fields[<?php echo esc_attr( (string) $rcs_index ); ?>][options]" rows="3"><?php echo esc_textarea( $rcs_options ); ?></textarea></td>
						<td><input type="number" min="1" name="rcs_fields[<?php echo esc_attr( (string) $rcs_index ); ?>][step]" value="<?php echo esc_attr( (string) $rcs_field['step'] ); ?>" /></td>
						<td>
							<input type="hidden" name="rcs_fields[<?php echo esc_attr( (string) $rcs_index ); ?>][sort_order]" value="<?php echo esc_attr( (string) $rcs_index ); ?>" />
							<input type="hidden" name="rcs_fields[<?php echo esc_attr( (string) $rcs_index ); ?>][validation][placeholder]" value="<?php echo esc_attr( $rcs_placeholder ); ?>" />
							<button type="button" class="button rcs-remove-field"><?php esc_html_e( 'Remove', 'request-coupon-system' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p>
			<button type="button" class="button" id="rcs-add-field"><?php esc_html_e( 'Add field', 'request-coupon-system' ); ?></button>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save form', 'request-coupon-system' ); ?></button>
		</p>
	</form>

	<template id="rcs-field-template">
		<tr class="rcs-field-row">
			<td>
				<select name="rcs_fields[__INDEX__][field_type]" class="rcs-field-type">
					<?php foreach ( $rcs_field_types as $rcs_type_key => $rcs_type_label ) : ?>
						<option value="<?php echo esc_attr( $rcs_type_key ); ?>"><?php echo esc_html( $rcs_type_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td><input type="text" name="rcs_fields[__INDEX__][field_key]" value="" /></td>
			<td><input type="text" name="rcs_fields[__INDEX__][label]" value="" /></td>
			<td><input type="checkbox" name="rcs_fields[__INDEX__][validation][required]" value="1" /></td>
			<td><textarea name="rcs_fields[__INDEX__][options]" rows="3"></textarea></td>
			<td><input type="number" min="1" name="rcs_fields[__INDEX__][step]" value="1" /></td>
			<td>
				<input type="hidden" name="rcs_fields[__INDEX__][sort_order]" value="__INDEX__" />
				<input type="hidden" name="rcs_fields[__INDEX__][validation][placeholder]" value="" />
				<button type="button" class="button rcs-remove-field"><?php esc_html_e( 'Remove', 'request-coupon-system' ); ?></button>
			</td>
		</tr>
	</template>
</div>
