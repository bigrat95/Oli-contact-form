<?php
/**
 * Anti-spam protection.
 *
 * @package Oli_Contact_Form
 */

/**
 * Anti-spam class.
 */
class Oli_CF_Anti_Spam {

	/**
	 * Check if submission passes anti-spam tests.
	 *
	 * @param array $data Posted data.
	 * @return array|bool True if valid, array with error message if spam.
	 */
	public static function check( $data ) {
		// Honeypot check.
		if ( ! empty( $data['oli_cf_hp'] ) ) {
			return array(
				'type'    => 'spam',
				'message' => __( 'Spam detected.', 'oli-contact-form' ),
			);
		}

		// Time trap check (form must take at least 3 seconds to submit).
		if ( ! empty( $data['oli_cf_timestamp'] ) ) {
			$timestamp = intval( $data['oli_cf_timestamp'] );
			$elapsed   = time() - $timestamp;

			if ( $elapsed < 3 ) {
				return array(
					'type'    => 'spam',
					'message' => __( 'Form submitted too quickly.', 'oli-contact-form' ),
				);
			}
		}

		// Check reCAPTCHA v3 if enabled.
		$settings = get_option( 'oli_cf_settings', array() );

		if ( ! empty( $settings['recaptcha_enabled'] ) && ! empty( $settings['recaptcha_secret_key'] ) ) {
			$recaptcha_result = self::verify_recaptcha( $data, $settings['recaptcha_secret_key'] );
			if ( is_array( $recaptcha_result ) ) {
				return $recaptcha_result;
			}
		}

		// Check Cloudflare Turnstile if enabled.
		if ( ! empty( $settings['turnstile_enabled'] ) && ! empty( $settings['turnstile_secret_key'] ) ) {
			$turnstile_result = self::verify_turnstile( $data, $settings['turnstile_secret_key'] );
			if ( is_array( $turnstile_result ) ) {
				return $turnstile_result;
			}
		}

		return true;
	}

	/**
	 * Verify Google reCAPTCHA v3 response.
	 *
	 * @param array  $data       Posted data.
	 * @param string $secret_key reCAPTCHA secret key.
	 * @return array|bool True if valid, array with error if failed.
	 */
	private static function verify_recaptcha( $data, $secret_key ) {
		if ( empty( $data['g-recaptcha-response'] ) ) {
			return array(
				'type'    => 'validation_failed',
				'message' => __( 'reCAPTCHA verification failed. Please try again.', 'oli-contact-form' ),
			);
		}

		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'body' => array(
					'secret'   => $secret_key,
					'response' => $data['g-recaptcha-response'],
					'remoteip' => self::get_client_ip(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'type'    => 'validation_failed',
				'message' => __( 'reCAPTCHA verification failed. Please try again.', 'oli-contact-form' ),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['success'] ) || $body['score'] < 0.5 ) {
			return array(
				'type'    => 'spam',
				'message' => __( 'Spam detected by reCAPTCHA.', 'oli-contact-form' ),
			);
		}

		return true;
	}

	/**
	 * Verify Cloudflare Turnstile response.
	 *
	 * @param array  $data       Posted data.
	 * @param string $secret_key Turnstile secret key.
	 * @return array|bool True if valid, array with error if failed.
	 */
	private static function verify_turnstile( $data, $secret_key ) {
		if ( empty( $data['cf-turnstile-response'] ) ) {
			return array(
				'type'    => 'validation_failed',
				'message' => __( 'Turnstile verification failed. Please try again.', 'oli-contact-form' ),
			);
		}

		$response = wp_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array(
				'body' => array(
					'secret'   => $secret_key,
					'response' => $data['cf-turnstile-response'],
					'remoteip' => self::get_client_ip(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'type'    => 'validation_failed',
				'message' => __( 'Turnstile verification failed. Please try again.', 'oli-contact-form' ),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['success'] ) ) {
			return array(
				'type'    => 'spam',
				'message' => __( 'Spam detected by Turnstile.', 'oli-contact-form' ),
			);
		}

		return true;
	}

	/**
	 * Get client IP address.
	 *
	 * @return string IP address.
	 */
	private static function get_client_ip() {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip;
	}
}
