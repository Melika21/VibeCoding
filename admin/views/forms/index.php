<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rcs_forms      = isset( $forms ) ? $forms : array();
$rcs_field_types = isset( $field_types ) ? $field_types : array();
$rcs_deleted    = isset( $deleted ) ? (int) $deleted : 0;
$rcs_list_url   = admin_url( 'admin.php?page=' . RCS_Admin_Admin::MENU_SLUG );
?>
<div class="wrap rcs-forms">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Forms', 'request-coupon-system' ); ?></h1>
	<a href="<?php echo esc_url( add_query_arg( array( 'page' => RCS_Admin_Admin::MENU_SLUG, 'rcs_form_action' => 'new' ), admin_url( 'admin.php' ) ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add new form', 'request-coupon-system' ); ?>
	</a>
	<hr class="wp-header-end" />

	<?php if ( $rcs_deleted ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'The form was deleted.', 'request-coupon-system' ); ?></p></div>
	<?php endif; ?>

	<table class="wp-list-table widefat fixed striped rcs-forms-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Name', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Fields', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Created', 'request-coupon-system' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Actions', 'request-coupon-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rcs_forms ) ) : ?>
				<tr>
					<td colspan="5"><?php esc_html_e( 'No form has been created yet.', 'request-coupon-system' ); ?></td>
				</tr>
			<?php endif; ?>

			<?php foreach ( $rcs_forms as $rcs_form ) : ?>
				<?php
				$rcs_edit_url   = add_query_arg(
					array(
						'page'            => RCS_Admin_Admin::MENU_SLUG,
						'rcs_form_action' => 'edit',
						'rcs_form_id'     => (int) $rcs_form['id'],
					),
					admin_url( 'admin.php' )
				);
				$rcs_delete_url = add_query_arg(
					array(
						'page'            => RCS_Admin_Admin::MENU_SLUG,
						'rcs_form_action' => 'delete',
						'rcs_form_id'     => (int) $rcs_form['id'],
						'_wpnonce'        => wp_create_nonce( RCS_Admin_Pages_FormsPage::NONCE_DELETE . '_' . (int) $rcs_form['id'] ),
					),
					admin_url( 'admin.php' )
				);
				?>
				<tr>
					<td><strong><a href="<?php echo esc_url( $rcs_edit_url ); ?>"><?php echo esc_html( $rcs_form['name'] ); ?></a></strong></td>
					<td><?php echo esc_html( $rcs_form['status'] ); ?></td>
					<td><?php echo esc_html( (string) RCS_Forms_FormManager::instance()->count_fields( (int) $rcs_form['id'] ) ); ?></td>
					<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $rcs_form['created_at'] ) ); ?></td>
					<td>
						<a href="<?php echo esc_url( $rcs_edit_url ); ?>"><?php esc_html_e( 'Edit', 'request-coupon-system' ); ?></a> |
						<a href="<?php echo esc_url( $rcs_delete_url ); ?>" class="submitdelete" onclick="return confirm( '<?php echo esc_js( __( 'Delete this form and its fields?', 'request-coupon-system' ) ); ?>' );">
							<?php esc_html_e( 'Delete', 'request-coupon-system' ); ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
