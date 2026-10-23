<?php
/**
 * Handle form submissions.
 *
 * @package Oli_Contact_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Submission handler class.
 */
class Oli_CF_Submission {

	/**
	 * Handle AJAX form submission.
	 */
	public function handle_ajax_submission() {
		check_ajax_referer( 'oli_cf_submit', 'nonce' );

		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;

		if ( ! $form_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid form ID.', 'oli-contact-form' ),
				)
			);
		}

		$result = $this->process_submission( $form_id, $_POST, $_FILES );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Handle non-AJAX form submission (fallback).
	 */
	public function handle_non_ajax_submission() {
		if ( ! isset( $_POST['oli_cf_submit'] ) || ! isset( $_POST['oli_cf_form_id'] ) ) {
			return;
		}

		$form_id = absint( $_POST['oli_cf_form_id'] );

		if ( ! $form_id ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['oli_cf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oli_cf_nonce'] ) ), 'oli_cf_submit_' . $form_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'oli-contact-form' ) );
		}

		$result = $this->process_submission( $form_id, $_POST, $_FILES );

		// Redirect with result.
		$redirect_url = wp_get_referer();

		$status_args = array(
			'oli_cf_status_nonce' => wp_create_nonce( 'oli_cf_status_' . $form_id ),
		);

		if ( $result['success'] ) {
			$status_args['oli_cf_success'] = $form_id;
		} else {
			$status_args['oli_cf_error'] = $form_id;
		}

		$redirect_url = add_query_arg( $status_args, $redirect_url );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Process form submission.
	 *
	 * @param int   $form_id Form ID.
	 * @param array $post    POST data.
	 * @param array $files   FILES data.
	 * @return array Result array with success status and message.
	 */
	private function process_submission( $form_id, $post, $files ) {
		$form = get_post( $form_id );

		if ( ! $form || 'oli_contact_form' !== $form->post_type ) {
			return array(
				'success' => false,
				'message' => __( 'Form not found.', 'oli-contact-form' ),
			);
		}

		$template = get_post_meta( $form_id, '_oli_cf_form_template', true );
		$messages = get_post_meta( $form_id, '_oli_cf_messages', true );

		if ( empty( $template ) ) {
			return array(
				'success' => false,
				'message' => __( 'Form configuration error.', 'oli-contact-form' ),
			);
		}

		// Hook: Before validation.
		do_action( 'oli_cf_before_validation', $form_id, $post, $files );

		// Anti-spam check.
		$spam_check = Oli_CF_Anti_Spam::check( $post );
		if ( is_array( $spam_check ) ) {
			return array(
				'success' => false,
				'message' => $spam_check['message'],
			);
		}

		// Validate form data.
		$validation = Oli_CF_Validator::validate( $post, $template, $files );

		if ( is_array( $validation ) ) {
			return array(
				'success' => false,
				'message' => ! empty( $messages['validation_error'] ) ? $messages['validation_error'] : __( 'Please correct the errors below.', 'oli-contact-form' ),
				'errors'  => $validation,
			);
		}

		// Hook: Before send.
		do_action( 'oli_cf_before_send', $form_id, $post, $files );

		// Send email.
		$sent = Oli_CF_Mailer::send( $form_id, $post, $files );

		if ( $sent ) {
			Oli_CF_Message_Store::maybe_save( $form_id, $post, $files );
		}

		// Clean up uploaded files.
		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				if ( ! empty( $file['tmp_name'] ) && file_exists( $file['tmp_name'] ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
					unlink( $file['tmp_name'] );
				}
			}
		}

		if ( ! $sent ) {
			return array(
				'success' => false,
				'message' => ! empty( $messages['mail_sent_ng'] ) ? $messages['mail_sent_ng'] : __( 'There was an error sending your message. Please try again.', 'oli-contact-form' ),
			);
		}

		// Hook: After send.
		do_action( 'oli_cf_after_send', $form_id, $post, $files );

		return array(
			'success' => true,
			'message' => ! empty( $messages['mail_sent_ok'] ) ? $messages['mail_sent_ok'] : __( 'Thank you for your message. It has been sent.', 'oli-contact-form' ),
		);
	}
}
