<?php
/**
 * CLI tests for Oli_CF_Message_Store helpers (no WordPress bootstrap).
 *
 * Excluded from the plugin ZIP. This harness is not loaded by WordPress, so
 * it stubs functions instead of exiting when ABSPATH is undefined.
 *
 * @package Oli_Contact_Form
 */

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

/**
 * Minimal WordPress function stubs used by the store helpers.
 */
function oli_cf_test_sanitize_string( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'oli_cf_test_sanitize_string', $value );
	}

	$value = is_string( $value ) ? $value : (string) $value;
	$value = stripslashes( $value );

	return trim( strip_tags( $value ) );
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * @param mixed $value Value.
	 * @return mixed
	 */
	function sanitize_text_field( $value ) {
		return oli_cf_test_sanitize_string( $value );
	}
}

if ( ! function_exists( 'sanitize_textarea_field' ) ) {
	/**
	 * @param mixed $value Value.
	 * @return mixed
	 */
	function sanitize_textarea_field( $value ) {
		return oli_cf_test_sanitize_string( $value );
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	/**
	 * @param string $email Email.
	 * @return string
	 */
	function sanitize_email( $email ) {
		return strtolower( trim( (string) $email ) );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	/**
	 * @param string $key Key.
	 * @return string
	 */
	function sanitize_key( $key ) {
		$key = strtolower( (string) $key );

		return preg_replace( '/[^a-z0-9_\-]/', '', $key );
	}
}

if ( ! function_exists( 'sanitize_file_name' ) ) {
	/**
	 * @param string $filename Filename.
	 * @return string
	 */
	function sanitize_file_name( $filename ) {
		return basename( str_replace( array( '\\', "\0" ), '', (string) $filename ) );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	/**
	 * @param mixed $value Value.
	 * @return mixed
	 */
	function wp_unslash( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'wp_unslash', $value );
		}

		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	/**
	 * @param string $text Text.
	 * @return string
	 */
	function wp_strip_all_tags( $text ) {
		return trim( strip_tags( (string) $text ) );
	}
}

if ( ! function_exists( 'is_email' ) ) {
	/**
	 * @param string $email Email.
	 * @return bool
	 */
	function is_email( $email ) {
		return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * @param mixed $data Data.
	 * @return string
	 */
	function wp_json_encode( $data ) {
		return wp_json_encode_compat( $data );
	}
}

/**
 * @param mixed $data Data.
 * @return string
 */
function wp_json_encode_compat( $data ) {
	return wp_json_encode_raw( $data );
}

/**
 * @param mixed $data Data.
 * @return string
 */
function wp_json_encode_raw( $data ) {
	return json_encode( $data );
}

if ( ! function_exists( 'absint' ) ) {
	/**
	 * @param mixed $value Value.
	 * @return int
	 */
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * @param string $text Text.
	 * @return string
	 */
	function __( $text ) {
		return $text;
	}
}

require_once dirname( __DIR__ ) . '/includes/class-oli-cf-message-store.php';

$oli_cf_failures = 0;

/**
 * Assert two values are equal.
 *
 * @param mixed  $expected Expected.
 * @param mixed  $actual   Actual.
 * @param string $label    Label.
 */
function oli_cf_assert_same( $expected, $actual, $label ) {
	global $oli_cf_failures;

	if ( $expected !== $actual ) {
		++$oli_cf_failures;
		fwrite( STDERR, "FAIL {$label}\n  expected: " . var_export( $expected, true ) . "\n  actual:   " . var_export( $actual, true ) . "\n" );
		return;
	}

	echo "OK   {$label}\n";
}

/**
 * Assert a value is true.
 *
 * @param mixed  $actual Actual.
 * @param string $label  Label.
 */
function oli_cf_assert_true( $actual, $label ) {
	oli_cf_assert_same( true, (bool) $actual, $label );
}

$fields = Oli_CF_Message_Store::sanitize_fields(
	array(
		'your-name'            => 'Ada Lovelace',
		'your-email'           => 'ada@example.com',
		'your-message'         => 'Hello from the analytical engine.',
		'oli_cf_nonce'         => 'secret',
		'oli_cf_hp'            => 'bot',
		'g-recaptcha-response' => 'token',
		'cf-turnstile-response'=> 'token',
		'action'               => 'oli_cf_submit_form',
		'nonce'                => 'ajax-nonce',
	),
	array(
		'your-file' => array(
			'name' => 'notes.pdf',
		),
	)
);

oli_cf_assert_same( 'Ada Lovelace', $fields['your-name'], 'sanitize keeps the name' );
oli_cf_assert_same( 'ada@example.com', $fields['your-email'], 'sanitize keeps the email' );
oli_cf_assert_same( 'notes.pdf', $fields['your-file'], 'sanitize stores the file name only' );
oli_cf_assert_same( false, isset( $fields['oli_cf_nonce'] ), 'nonce is not stored' );
oli_cf_assert_same( false, isset( $fields['oli_cf_hp'] ), 'honeypot is not stored' );
oli_cf_assert_same( false, isset( $fields['g-recaptcha-response'] ), 'reCAPTCHA token is not stored' );
oli_cf_assert_same( false, isset( $fields['cf-turnstile-response'] ), 'Turnstile token is not stored' );

oli_cf_assert_same( 'ada@example.com', Oli_CF_Message_Store::find_sender_email( $fields ), 'sender email from your-email' );
oli_cf_assert_same( 'Ada Lovelace', Oli_CF_Message_Store::find_sender_name( $fields ), 'sender name from your-name' );
oli_cf_assert_same( 'Hello from the analytical engine.', Oli_CF_Message_Store::build_preview( $fields ), 'preview from your-message' );

$long = str_repeat( 'x', 200 );
oli_cf_assert_same( 120, strlen( Oli_CF_Message_Store::excerpt( $long ) ), 'excerpt is capped' );

oli_cf_assert_same( '', Oli_CF_Message_Store::cutoff_gmt( 0, '2026-10-21 12:00:00' ), 'retention 0 means no cutoff' );
oli_cf_assert_same(
	'2026-09-21 12:00:00',
	Oli_CF_Message_Store::cutoff_gmt( 30, '2026-10-21 12:00:00' ),
	'30-day cutoff is 30 days earlier'
);

$row = array(
	'id'           => 4,
	'form_title'   => 'Contact',
	'sender_name'  => 'Ada Lovelace',
	'sender_email' => 'ada@example.com',
	'created_gmt'  => '2026-10-21 12:00:00',
	'fields'       => '{"your-email":"ada@example.com","your-message":"Hello"}',
);

oli_cf_assert_true( Oli_CF_Message_Store::row_matches_email( $row, 'ada@example.com' ), 'row matches sender email' );
oli_cf_assert_true( Oli_CF_Message_Store::row_matches_email( $row, 'ADA@example.com' ), 'row match is case-insensitive' );
oli_cf_assert_same( false, Oli_CF_Message_Store::row_matches_email( $row, 'other@example.com' ), 'row does not match another email' );

$export = Oli_CF_Message_Store::export_item_from_row( $row );
oli_cf_assert_same( 'oli_cf_messages', $export['group_id'], 'export group id' );
oli_cf_assert_same( 'oli-cf-message-4', $export['item_id'], 'export item id' );
oli_cf_assert_true( is_array( $export['data'] ) && count( $export['data'] ) >= 4, 'export includes core fields' );

if ( $oli_cf_failures > 0 ) {
	fwrite( STDERR, "\n{$oli_cf_failures} test(s) failed.\n" );
	exit( 1 );
}

echo "\nAll message-store tests passed.\n";
exit( 0 );
