<?php
/**
 * Handle email sending.
 *
 * @package Oli_Contact_Form
 */

/**
 * Mailer class.
 */
class Oli_CF_Mailer {

	/**
	 * Send form submission email.
	 *
	 * @param int   $form_id Form ID.
	 * @param array $data    Posted data.
	 * @param array $files   Uploaded files.
	 * @return bool True on success, false on failure.
	 */
	public static function send( $form_id, $data, $files = array() ) {
		$mail_settings = get_post_meta( $form_id, '_oli_cf_mail', true );
		$mail_2_settings = get_post_meta( $form_id, '_oli_cf_mail_2', true );

		if ( empty( $mail_settings ) ) {
			return false;
		}

		// Send main email.
		$result = self::send_mail( $mail_settings, $data, $files );

		// Send autoresponder if enabled.
		if ( ! empty( $mail_2_settings['enabled'] ) ) {
			self::send_mail( $mail_2_settings, $data, $files );
		}

		return $result;
	}

	/**
	 * Send a single email.
	 *
	 * @param array $settings Mail settings.
	 * @param array $data     Posted data.
	 * @param array $files    Uploaded files.
	 * @return bool True on success, false on failure.
	 */
	private static function send_mail( $settings, $data, $files ) {
		$to = self::replace_tags( $settings['to'], $data );
		$subject = self::replace_tags( $settings['subject'], $data );
		$body = self::replace_tags( $settings['body'], $data );
		$headers = array();

		// From header.
		if ( ! empty( $settings['from'] ) ) {
			$from = self::replace_tags( $settings['from'], $data );
			$headers[] = 'From: ' . $from;
		}

		// Reply-To header.
		if ( ! empty( $settings['reply_to'] ) ) {
			$reply_to = self::replace_tags( $settings['reply_to'], $data );
			$headers[] = 'Reply-To: ' . $reply_to;
		}

		// Additional headers.
		if ( ! empty( $settings['additional_headers'] ) ) {
			$additional = explode( "\n", $settings['additional_headers'] );
			foreach ( $additional as $header ) {
				$header = self::replace_tags( trim( $header ), $data );
				if ( ! empty( $header ) ) {
					$headers[] = $header;
				}
			}
		}

		// Content type.
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		// Prepare attachments.
		$attachments = array();
		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				if ( ! empty( $file['tmp_name'] ) && is_uploaded_file( $file['tmp_name'] ) ) {
					$attachments[] = $file['tmp_name'];
				}
			}
		}

		// Convert line breaks to HTML.
		$body = nl2br( $body );

		// Send email.
		$result = wp_mail( $to, $subject, $body, $headers, $attachments );

		return $result;
	}

	/**
	 * Replace [field] tags with actual values.
	 *
	 * @param string $text Text with tags.
	 * @param array  $data Posted data.
	 * @return string Text with replaced tags.
	 */
	private static function replace_tags( $text, $data ) {
		return preg_replace_callback(
			'/\[([^\]]+)\]/',
			function( $matches ) use ( $data ) {
				$field_name = $matches[1];

				if ( isset( $data[ $field_name ] ) ) {
					$value = $data[ $field_name ];

					// Handle arrays (checkboxes).
					if ( is_array( $value ) ) {
						return implode( ', ', array_map( 'sanitize_text_field', $value ) );
					}

					return sanitize_text_field( $value );
				}

				return $matches[0];
			},
			$text
		);
	}
}
