<?php
/**
 * Import Contact Form 7 forms.
 *
 * @package Oli_Contact_Form
 */

/**
 * Importer class.
 */
class Oli_CF_Importer {

	/**
	 * Add importer page to admin menu.
	 */
	public function add_importer_page() {
		add_submenu_page(
			'edit.php?post_type=oli_contact_form',
			__( 'Import from CF7', 'oli-contact-form' ),
			__( 'Import from CF7', 'oli-contact-form' ),
			'manage_options',
			'oli-cf-import-cf7',
			array( $this, 'render_importer_page' )
		);
	}

	/**
	 * Enqueue the importer "select all" script on the CF7 import screen.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( false === strpos( $hook, 'oli-cf-import-cf7' ) ) {
			return;
		}

		wp_enqueue_script(
			'oli-cf-importer',
			OLI_CF_PLUGIN_URL . 'assets/js/oli-cf-importer.js',
			array(),
			OLI_CF_VERSION,
			true
		);
	}

	/**
	 * Render importer page.
	 */
	public function render_importer_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if CF7 is active.
		$cf7_forms = $this->get_cf7_forms();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import from Contact Form 7', 'oli-contact-form' ); ?></h1>

			<?php if ( empty( $cf7_forms ) ) : ?>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'No Contact Form 7 forms found. Please make sure Contact Form 7 is installed and you have created some forms.', 'oli-contact-form' ); ?></p>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'This tool will import Contact Form 7 forms into Oli Contact Form. Form templates, mail settings, and messages will be converted.', 'oli-contact-form' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'oli_cf_import_cf7', 'oli_cf_import_nonce' ); ?>
					<input type="hidden" name="action" value="oli_cf_import_cf7" />

					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th class="check-column"><input type="checkbox" id="oli-cf-select-all" /></th>
								<th><?php esc_html_e( 'Form Title', 'oli-contact-form' ); ?></th>
								<th><?php esc_html_e( 'ID', 'oli-contact-form' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $cf7_forms as $form ) : ?>
								<tr>
									<td class="check-column">
										<input type="checkbox" name="import_forms[]" value="<?php echo absint( $form->ID ); ?>" class="oli-cf-import-checkbox" />
									</td>
									<td><?php echo esc_html( $form->post_title ); ?></td>
									<td><?php echo absint( $form->ID ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<p>
						<?php submit_button( __( 'Import Selected Forms', 'oli-contact-form' ), 'primary', 'submit', false ); ?>
					</p>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle import.
	 */
	public function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'oli-contact-form' ) );
		}

		if ( ! isset( $_POST['oli_cf_import_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oli_cf_import_nonce'] ) ), 'oli_cf_import_cf7' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'oli-contact-form' ) );
		}

		$form_ids = isset( $_POST['import_forms'] ) ? array_map( 'absint', wp_unslash( $_POST['import_forms'] ) ) : array();

		if ( empty( $form_ids ) ) {
			wp_safe_redirect( add_query_arg( 'imported', 0, admin_url( 'edit.php?post_type=oli_contact_form&page=oli-cf-import-cf7' ) ) );
			exit;
		}

		$imported_count = 0;

		foreach ( $form_ids as $form_id ) {
			if ( $this->import_cf7_form( $form_id ) ) {
				$imported_count++;
			}
		}

		wp_safe_redirect( add_query_arg( 'imported', $imported_count, admin_url( 'edit.php?post_type=oli_contact_form' ) ) );
		exit;
	}

	/**
	 * Get CF7 forms.
	 *
	 * @return array CF7 form posts.
	 */
	private function get_cf7_forms() {
		$args = array(
			'post_type'      => 'wpcf7_contact_form',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		return get_posts( $args );
	}

	/**
	 * Import a single CF7 form.
	 *
	 * @param int $cf7_form_id CF7 form ID.
	 * @return bool True on success, false on failure.
	 */
	private function import_cf7_form( $cf7_form_id ) {
		$cf7_form = get_post( $cf7_form_id );

		if ( ! $cf7_form || 'wpcf7_contact_form' !== $cf7_form->post_type ) {
			return false;
		}

		// Create new Oli Contact Form post.
		$new_form_id = wp_insert_post(
			array(
				'post_title'  => $cf7_form->post_title . ' (Imported from CF7)',
				'post_type'   => 'oli_contact_form',
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $new_form_id ) ) {
			return false;
		}

		// Get CF7 form data.
		$cf7_form_data = get_post_meta( $cf7_form_id, '_form', true );
		$cf7_mail = get_post_meta( $cf7_form_id, '_mail', true );
		$cf7_mail_2 = get_post_meta( $cf7_form_id, '_mail_2', true );
		$cf7_messages = get_post_meta( $cf7_form_id, '_messages', true );

		// Convert form template.
		$form_template = $this->convert_cf7_template( $cf7_form_data );
		update_post_meta( $new_form_id, '_oli_cf_form_template', $form_template );

		// Convert mail settings.
		$mail_settings = $this->convert_cf7_mail( $cf7_mail );
		update_post_meta( $new_form_id, '_oli_cf_mail', $mail_settings );

		// Convert mail 2 settings.
		$mail_2_settings = $this->convert_cf7_mail( $cf7_mail_2, true );
		update_post_meta( $new_form_id, '_oli_cf_mail_2', $mail_2_settings );

		// Convert messages.
		$messages = $this->convert_cf7_messages( $cf7_messages );
		update_post_meta( $new_form_id, '_oli_cf_messages', $messages );

		// Store mapping for CF7 compatibility.
		update_post_meta( $new_form_id, '_oli_cf_imported_from_cf7', $cf7_form_id );

		return true;
	}

	/**
	 * Convert CF7 form template to Oli Contact Form format.
	 *
	 * @param string $cf7_template CF7 template.
	 * @return string Converted template.
	 */
	private function convert_cf7_template( $cf7_template ) {
		// CF7 and Oli Contact Form use very similar tag syntax, so minimal conversion needed.
		// The main difference is that CF7 uses some specific options that we might not support.
		// For v0.1, we'll just pass it through with minor cleanup.
		return $cf7_template;
	}

	/**
	 * Convert CF7 mail settings.
	 *
	 * @param array $cf7_mail CF7 mail settings.
	 * @param bool  $is_mail_2 Is this mail 2.
	 * @return array Converted mail settings.
	 */
	private function convert_cf7_mail( $cf7_mail, $is_mail_2 = false ) {
		if ( empty( $cf7_mail ) ) {
			if ( $is_mail_2 ) {
				return array(
					'enabled'            => false,
					'to'                 => '',
					'from'               => '',
					'subject'            => '',
					'body'               => '',
					'reply_to'           => '',
					'additional_headers' => '',
				);
			} else {
				return array(
					'to'                 => get_option( 'admin_email' ),
					'from'               => '',
					'subject'            => '',
					'body'               => '',
					'reply_to'           => '',
					'additional_headers' => '',
				);
			}
		}

		$mail_settings = array(
			'to'                 => isset( $cf7_mail['recipient'] ) ? $cf7_mail['recipient'] : get_option( 'admin_email' ),
			'from'               => isset( $cf7_mail['sender'] ) ? $cf7_mail['sender'] : '',
			'subject'            => isset( $cf7_mail['subject'] ) ? $cf7_mail['subject'] : '',
			'body'               => isset( $cf7_mail['body'] ) ? $cf7_mail['body'] : '',
			'reply_to'           => '',
			'additional_headers' => isset( $cf7_mail['additional_headers'] ) ? $cf7_mail['additional_headers'] : '',
		);

		if ( $is_mail_2 ) {
			$mail_settings['enabled'] = ! empty( $cf7_mail['active'] );
		}

		return $mail_settings;
	}

	/**
	 * Convert CF7 messages.
	 *
	 * @param array $cf7_messages CF7 messages.
	 * @return array Converted messages.
	 */
	private function convert_cf7_messages( $cf7_messages ) {
		if ( empty( $cf7_messages ) ) {
			return array(
				'mail_sent_ok'     => __( 'Thank you for your message. It has been sent.', 'oli-contact-form' ),
				'mail_sent_ng'     => __( 'There was an error sending your message. Please try again.', 'oli-contact-form' ),
				'validation_error' => __( 'One or more fields have an error. Please check and try again.', 'oli-contact-form' ),
			);
		}

		return array(
			'mail_sent_ok'     => isset( $cf7_messages['mail_sent_ok'] ) ? $cf7_messages['mail_sent_ok'] : __( 'Thank you for your message. It has been sent.', 'oli-contact-form' ),
			'mail_sent_ng'     => isset( $cf7_messages['mail_sent_ng'] ) ? $cf7_messages['mail_sent_ng'] : __( 'There was an error sending your message. Please try again.', 'oli-contact-form' ),
			'validation_error' => isset( $cf7_messages['validation_error'] ) ? $cf7_messages['validation_error'] : __( 'One or more fields have an error. Please check and try again.', 'oli-contact-form' ),
		);
	}
}
