<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rcs_form    = isset( $form ) ? $form : array();
$rcs_fields  = isset( $fields ) ? $fields : array();
$rcs_args    = isset( $args ) ? $args : array();
$rcs_status  = isset( $_GET['rcs_form_status'] ) ? sanitize_key( wp_unslash( $_GET['rcs_form_status'] ) ) : '';
$rcs_message = isset( $_GET['rcs_form_message'] ) ? sanitize_text_field( wp_unslash( $_GET['rcs_form_message'] ) ) : '';
?>
<div class="rcs-form-wrapper rcs-layout-<?php echo esc_attr( $rcs_args['layout'] ); ?>">
	<?php if ( '' !== $rcs_status ) : ?>
		<div class="rcs-form-notice rcs-form-notice-<?php echo esc_attr( $rcs_status ); ?>" role="status">
			<?php echo esc_html( $rcs_message ); ?>
		</div>
	<?php endif; ?>

	<form class="rcs-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="<?php echo esc_attr( RCS_Forms_SubmissionHandler::ACTION ); ?>" />
		<input type="hidden" name="rcs_form_id" value="<?php echo esc_attr( $rcs_form['id'] ); ?>" />
		<?php wp_nonce_field( RCS_Forms_SubmissionHandler::ACTION . '_' . $rcs_form['id'] ); ?>

		<?php if ( ! empty( $rcs_args['show_title'] ) ) : ?>
			<h3 class="rcs-form-title"><?php echo esc_html( $rcs_form['name'] ); ?></h3>
		<?php endif; ?>

		<?php if ( ! empty( $rcs_args['show_description'] ) && ! empty( $rcs_form['settings']['description'] ) ) : ?>
			<p class="rcs-form-description"><?php echo esc_html( $rcs_form['settings']['description'] ); ?></p>
		<?php endif; ?>

		<div class="rcs-form-fields">
			<?php foreach ( $rcs_fields as $rcs_field ) : ?>
				<div class="rcs-form-field rcs-field-<?php echo esc_attr( $rcs_field['field_type'] ); ?>">
					<?php
					$rcs_field_html = RCS_Forms_FieldManager::instance()->render_field( $rcs_field );

					echo $rcs_field_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $rcs_fields ) ) : ?>
			<div class="rcs-form-actions">
				<button type="submit" class="rcs-form-submit"><?php echo esc_html( $rcs_args['submit_text'] ); ?></button>
			</div>
		<?php endif; ?>
	</form>
</div>
