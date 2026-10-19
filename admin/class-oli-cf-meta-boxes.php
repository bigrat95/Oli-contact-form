<?php
/**
 * Meta boxes for form edit screen.
 *
 * @package Oli_Contact_Form
 */

/**
 * Meta boxes class.
 */
class Oli_CF_Meta_Boxes {

	/**
	 * Add meta boxes.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'oli_cf_form_editor',
			__( 'Form', 'oli-contact-form' ),
			array( $this, 'render_form_editor' ),
			'oli_contact_form',
			'normal',
			'high'
		);

		add_meta_box(
			'oli_cf_mail_settings',
			__( 'Mail', 'oli-contact-form' ),
			array( $this, 'render_mail_settings' ),
			'oli_contact_form',
			'normal',
			'high'
		);

		add_meta_box(
			'oli_cf_mail_2_settings',
			__( 'Mail (2)', 'oli-contact-form' ),
			array( $this, 'render_mail_2_settings' ),
			'oli_contact_form',
			'normal',
			'high'
		);

		add_meta_box(
			'oli_cf_messages',
			__( 'Messages', 'oli-contact-form' ),
			array( $this, 'render_messages' ),
			'oli_contact_form',
			'normal',
			'default'
		);

		add_meta_box(
			'oli_cf_shortcode',
			__( 'Shortcode', 'oli-contact-form' ),
			array( $this, 'render_shortcode' ),
			'oli_contact_form',
			'side',
			'default'
		);
	}

	/**
	 * Render form editor meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_form_editor( $post ) {
		wp_nonce_field( 'oli_cf_save_form', 'oli_cf_nonce' );

		$template = get_post_meta( $post->ID, '_oli_cf_form_template', true );

		if ( empty( $template ) ) {
			$template = $this->get_default_template();
		}
		?>
		<div class="oli-cf-form-editor">
			<div class="oli-cf-tag-generator">
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="text"><?php esc_html_e( 'text', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="email"><?php esc_html_e( 'email', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="tel"><?php esc_html_e( 'tel', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="url"><?php esc_html_e( 'url', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="number"><?php esc_html_e( 'number', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="date"><?php esc_html_e( 'date', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="textarea"><?php esc_html_e( 'textarea', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="select"><?php esc_html_e( 'select', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="checkbox"><?php esc_html_e( 'checkbox', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="radio"><?php esc_html_e( 'radio', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="acceptance"><?php esc_html_e( 'acceptance', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="file"><?php esc_html_e( 'file', 'oli-contact-form' ); ?></button>
				<button type="button" class="button oli-cf-tag-gen" data-tag-type="submit"><?php esc_html_e( 'submit', 'oli-contact-form' ); ?></button>
			</div>
			<textarea name="oli_cf_form_template" id="oli-cf-form-template" rows="15" class="large-text code"><?php echo esc_textarea( $template ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Use CF7-like tag syntax. Example: [text* your-name placeholder:"Your name"] [email* your-email] [textarea your-message] [submit "Send"]', 'oli-contact-form' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render mail settings meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_mail_settings( $post ) {
		$mail = get_post_meta( $post->ID, '_oli_cf_mail', true );

		if ( empty( $mail ) ) {
			$mail = $this->get_default_mail();
		}
		?>
		<table class="form-table">
			<tr>
				<th><label for="oli-cf-mail-to"><?php esc_html_e( 'To', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_mail[to]" id="oli-cf-mail-to" value="<?php echo esc_attr( $mail['to'] ); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th><label for="oli-cf-mail-from"><?php esc_html_e( 'From', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_mail[from]" id="oli-cf-mail-from" value="<?php echo esc_attr( $mail['from'] ); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th><label for="oli-cf-mail-subject"><?php esc_html_e( 'Subject', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_mail[subject]" id="oli-cf-mail-subject" value="<?php echo esc_attr( $mail['subject'] ); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th><label for="oli-cf-mail-body"><?php esc_html_e( 'Message Body', 'oli-contact-form' ); ?></label></th>
				<td>
					<textarea name="oli_cf_mail[body]" id="oli-cf-mail-body" rows="10" class="large-text code"><?php echo esc_textarea( $mail['body'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Use [field-name] to insert field values.', 'oli-contact-form' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="oli-cf-mail-reply-to"><?php esc_html_e( 'Reply-To', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_mail[reply_to]" id="oli-cf-mail-reply-to" value="<?php echo esc_attr( $mail['reply_to'] ); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th><label for="oli-cf-mail-additional-headers"><?php esc_html_e( 'Additional Headers', 'oli-contact-form' ); ?></label></th>
				<td><textarea name="oli_cf_mail[additional_headers]" id="oli-cf-mail-additional-headers" rows="3" class="large-text code"><?php echo esc_textarea( $mail['additional_headers'] ); ?></textarea></td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render mail 2 settings meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_mail_2_settings( $post ) {
		$mail_2 = get_post_meta( $post->ID, '_oli_cf_mail_2', true );

		if ( empty( $mail_2 ) ) {
			$mail_2 = array(
				'enabled'            => false,
				'to'                 => '[your-email]',
				'from'               => get_option( 'admin_email' ),
				'subject'            => 'Thank you for your message',
				'body'               => "Thank you for contacting us.\n\nWe will get back to you soon.",
				'reply_to'           => '',
				'additional_headers' => '',
			);
		}
		?>
		<p>
			<label>
				<input type="checkbox" name="oli_cf_mail_2[enabled]" value="1" <?php checked( ! empty( $mail_2['enabled'] ) ); ?> />
				<?php esc_html_e( 'Enable autoresponder (Mail 2)', 'oli-contact-form' ); ?>
			</label>
		</p>
		<table class="form-table">
			<tr>
				<th><label for="oli-cf-mail-2-to"><?php esc_html_e( 'To', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_mail_2[to]" id="oli-cf-mail-2-to" value="<?php echo esc_attr( $mail_2['to'] ); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th><label for="oli-cf-mail-2-from"><?php esc_html_e( 'From', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_mail_2[from]" id="oli-cf-mail-2-from" value="<?php echo esc_attr( $mail_2['from'] ); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th><label for="oli-cf-mail-2-subject"><?php esc_html_e( 'Subject', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_mail_2[subject]" id="oli-cf-mail-2-subject" value="<?php echo esc_attr( $mail_2['subject'] ); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th><label for="oli-cf-mail-2-body"><?php esc_html_e( 'Message Body', 'oli-contact-form' ); ?></label></th>
				<td>
					<textarea name="oli_cf_mail_2[body]" id="oli-cf-mail-2-body" rows="10" class="large-text code"><?php echo esc_textarea( $mail_2['body'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Use [field-name] to insert field values.', 'oli-contact-form' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="oli-cf-mail-2-reply-to"><?php esc_html_e( 'Reply-To', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_mail_2[reply_to]" id="oli-cf-mail-2-reply-to" value="<?php echo esc_attr( $mail_2['reply_to'] ); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th><label for="oli-cf-mail-2-additional-headers"><?php esc_html_e( 'Additional Headers', 'oli-contact-form' ); ?></label></th>
				<td><textarea name="oli_cf_mail_2[additional_headers]" id="oli-cf-mail-2-additional-headers" rows="3" class="large-text code"><?php echo esc_textarea( $mail_2['additional_headers'] ); ?></textarea></td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render messages meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_messages( $post ) {
		$messages = get_post_meta( $post->ID, '_oli_cf_messages', true );

		if ( empty( $messages ) ) {
			$messages = $this->get_default_messages();
		}
		?>
		<table class="form-table">
			<tr>
				<th><label for="oli-cf-msg-sent-ok"><?php esc_html_e( 'Success Message', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_messages[mail_sent_ok]" id="oli-cf-msg-sent-ok" value="<?php echo esc_attr( $messages['mail_sent_ok'] ); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th><label for="oli-cf-msg-sent-ng"><?php esc_html_e( 'Error Message', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_messages[mail_sent_ng]" id="oli-cf-msg-sent-ng" value="<?php echo esc_attr( $messages['mail_sent_ng'] ); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th><label for="oli-cf-msg-validation-error"><?php esc_html_e( 'Validation Error', 'oli-contact-form' ); ?></label></th>
				<td><input type="text" name="oli_cf_messages[validation_error]" id="oli-cf-msg-validation-error" value="<?php echo esc_attr( $messages['validation_error'] ); ?>" class="large-text" /></td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render shortcode meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_shortcode( $post ) {
		if ( 'publish' !== $post->post_status ) {
			echo '<p>' . esc_html__( 'Publish the form to get the shortcode.', 'oli-contact-form' ) . '</p>';
			return;
		}
		?>
		<p>
			<code>[oli_contact_form id="<?php echo absint( $post->ID ); ?>"]</code>
		</p>
		<p class="description">
			<?php esc_html_e( 'Copy this shortcode and paste it into your post, page, or text widget.', 'oli-contact-form' ); ?>
		</p>
		<?php
	}

	/**
	 * Save form data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_form_data( $post_id, $post ) {
		// Check nonce.
		if ( ! isset( $_POST['oli_cf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oli_cf_nonce'] ) ), 'oli_cf_save_form' ) ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save form template.
		if ( isset( $_POST['oli_cf_form_template'] ) ) {
			update_post_meta( $post_id, '_oli_cf_form_template', wp_kses_post( wp_unslash( $_POST['oli_cf_form_template'] ) ) );
		}

		// Save mail settings.
		if ( isset( $_POST['oli_cf_mail'] ) ) {
			$mail = array_map( 'sanitize_text_field', wp_unslash( $_POST['oli_cf_mail'] ) );
			$mail['body'] = isset( $_POST['oli_cf_mail']['body'] ) ? sanitize_textarea_field( wp_unslash( $_POST['oli_cf_mail']['body'] ) ) : '';
			$mail['additional_headers'] = isset( $_POST['oli_cf_mail']['additional_headers'] ) ? sanitize_textarea_field( wp_unslash( $_POST['oli_cf_mail']['additional_headers'] ) ) : '';
			update_post_meta( $post_id, '_oli_cf_mail', $mail );
		}

		// Save mail 2 settings.
		if ( isset( $_POST['oli_cf_mail_2'] ) ) {
			$mail_2 = array_map( 'sanitize_text_field', wp_unslash( $_POST['oli_cf_mail_2'] ) );
			$mail_2['enabled'] = ! empty( $_POST['oli_cf_mail_2']['enabled'] );
			$mail_2['body'] = isset( $_POST['oli_cf_mail_2']['body'] ) ? sanitize_textarea_field( wp_unslash( $_POST['oli_cf_mail_2']['body'] ) ) : '';
			$mail_2['additional_headers'] = isset( $_POST['oli_cf_mail_2']['additional_headers'] ) ? sanitize_textarea_field( wp_unslash( $_POST['oli_cf_mail_2']['additional_headers'] ) ) : '';
			update_post_meta( $post_id, '_oli_cf_mail_2', $mail_2 );
		}

		// Save messages.
		if ( isset( $_POST['oli_cf_messages'] ) ) {
			$messages = array_map( 'sanitize_text_field', wp_unslash( $_POST['oli_cf_messages'] ) );
			update_post_meta( $post_id, '_oli_cf_messages', $messages );
		}
	}

	/**
	 * Get default form template.
	 *
	 * @return string Default template.
	 */
	private function get_default_template() {
		return '<label> ' . __( 'Your name', 'oli-contact-form' ) . "\n" .
			'    [text* your-name] </label>' . "\n\n" .
			'<label> ' . __( 'Your email', 'oli-contact-form' ) . "\n" .
			'    [email* your-email] </label>' . "\n\n" .
			'<label> ' . __( 'Subject', 'oli-contact-form' ) . "\n" .
			'    [text* your-subject] </label>' . "\n\n" .
			'<label> ' . __( 'Your message', 'oli-contact-form' ) . "\n" .
			'    [textarea your-message] </label>' . "\n\n" .
			'[submit "' . __( 'Send', 'oli-contact-form' ) . '"]';
	}

	/**
	 * Get default mail settings.
	 *
	 * @return array Default mail settings.
	 */
	private function get_default_mail() {
		return array(
			'to'                 => get_option( 'admin_email' ),
			'from'               => '[your-email]',
			'subject'            => 'New message from [your-name]',
			'body'               => "From: [your-name] <[your-email]>\nSubject: [your-subject]\n\nMessage:\n[your-message]",
			'reply_to'           => '[your-email]',
			'additional_headers' => '',
		);
	}

	/**
	 * Get default messages.
	 *
	 * @return array Default messages.
	 */
	private function get_default_messages() {
		return array(
			'mail_sent_ok'      => __( 'Thank you for your message. It has been sent.', 'oli-contact-form' ),
			'mail_sent_ng'      => __( 'There was an error sending your message. Please try again.', 'oli-contact-form' ),
			'validation_error'  => __( 'One or more fields have an error. Please check and try again.', 'oli-contact-form' ),
		);
	}
}
