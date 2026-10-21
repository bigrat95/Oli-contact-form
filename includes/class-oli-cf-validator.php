<?php
/**
 * Validate form submissions.
 *
 * @package Oli_Contact_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validator class.
 */
class Oli_CF_Validator {

	/**
	 * Validate form data.
	 *
	 * @param array  $data       Posted data.
	 * @param string $template   Form template.
	 * @param array  $files      Uploaded files.
	 * @return array|bool Array of errors or true if valid.
	 */
	public static function validate( $data, $template, $files = array() ) {
		$errors = array();

		// Extract all field tags from template.
		preg_match_all( '/\[([^\]]+)\]/', $template, $matches );

		if ( empty( $matches[1] ) ) {
			return true;
		}

		foreach ( $matches[1] as $tag ) {
			$tag   = trim( $tag );
			$parts = preg_split( '/\s+/', $tag );

			if ( empty( $parts ) ) {
				continue;
			}

			$type_raw = array_shift( $parts );
			$required = false;

			// Check for required marker.
			if ( substr( $type_raw, -1 ) === '*' ) {
				$required = true;
				$type     = rtrim( $type_raw, '*' );
			} else {
				$type = $type_raw;
			}

			$name = ! empty( $parts ) ? array_shift( $parts ) : '';

			if ( empty( $name ) || 'submit' === $type ) {
				continue;
			}

			// Validate based on type.
			$error = self::validate_field( $type, $name, $data, $files, $required, $parts );

			if ( $error ) {
				$errors[ $name ] = $error;
			}
		}

		return empty( $errors ) ? true : $errors;
	}

	/**
	 * Validate a single field.
	 *
	 * @param string $type     Field type.
	 * @param string $name     Field name.
	 * @param array  $data     Posted data.
	 * @param array  $files    Uploaded files.
	 * @param bool   $required Is required.
	 * @param array  $options  Field options.
	 * @return string|false Error message or false if valid.
	 */
	private static function validate_field( $type, $name, $data, $files, $required, $options ) {
		$value = isset( $data[ $name ] ) ? $data[ $name ] : '';

		// Check required.
		if ( $required ) {
			if ( 'file' === $type ) {
				if ( empty( $files[ $name ] ) || empty( $files[ $name ]['name'] ) ) {
					return __( 'This field is required.', 'oli-contact-form' );
				}
			} elseif ( 'checkbox' === $type ) {
				if ( empty( $value ) || ! is_array( $value ) ) {
					return __( 'This field is required.', 'oli-contact-form' );
				}
			} elseif ( 'acceptance' === $type ) {
				if ( empty( $value ) || '1' !== $value ) {
					return __( 'You must accept this to proceed.', 'oli-contact-form' );
				}
			} elseif ( empty( $value ) && '0' !== $value ) {
				return __( 'This field is required.', 'oli-contact-form' );
			}
		}

		// Skip type validation if empty and not required.
		if ( empty( $value ) && ! $required ) {
			return false;
		}

		// Type-specific validation.
		switch ( $type ) {
			case 'email':
				if ( ! is_email( $value ) ) {
					return __( 'Please enter a valid email address.', 'oli-contact-form' );
				}
				break;

			case 'url':
				if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
					return __( 'Please enter a valid URL.', 'oli-contact-form' );
				}
				break;

			case 'tel':
				// Basic phone validation.
				if ( ! preg_match( '/^[\d\s\+\-\(\)]+$/', $value ) ) {
					return __( 'Please enter a valid phone number.', 'oli-contact-form' );
				}
				break;

			case 'number':
				if ( ! is_numeric( $value ) ) {
					return __( 'Please enter a valid number.', 'oli-contact-form' );
				}
				break;

			case 'date':
				$timestamp = strtotime( $value );
				if ( false === $timestamp ) {
					return __( 'Please enter a valid date.', 'oli-contact-form' );
				}
				break;

			case 'file':
				return self::validate_file( $name, $files, $options );
		}

		return false;
	}

	/**
	 * Validate uploaded file.
	 *
	 * @param string $name    Field name.
	 * @param array  $files   Uploaded files.
	 * @param array  $options Field options.
	 * @return string|false Error message or false if valid.
	 */
	private static function validate_file( $name, $files, $options ) {
		if ( empty( $files[ $name ] ) ) {
			return false;
		}

		$file = $files[ $name ];

		// Check upload errors.
		if ( ! empty( $file['error'] ) ) {
			switch ( $file['error'] ) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					return __( 'The uploaded file is too large.', 'oli-contact-form' );
				case UPLOAD_ERR_PARTIAL:
					return __( 'The file was only partially uploaded.', 'oli-contact-form' );
				case UPLOAD_ERR_NO_FILE:
					return __( 'No file was uploaded.', 'oli-contact-form' );
				default:
					return __( 'File upload failed.', 'oli-contact-form' );
			}
		}

		// Parse options.
		$attributes = array();
		foreach ( $options as $option ) {
			if ( strpos( $option, ':' ) !== false ) {
				list( $key, $value )        = explode( ':', $option, 2 );
				$attributes[ trim( $key ) ] = trim( $value, '"' );
			}
		}

		// Validate file size.
		if ( isset( $attributes['limit'] ) ) {
			$limit_bytes = self::parse_size( $attributes['limit'] );
			if ( $file['size'] > $limit_bytes ) {
				return sprintf(
					/* translators: %s: file size limit */
					__( 'The uploaded file exceeds the maximum allowed size of %s.', 'oli-contact-form' ),
					$attributes['limit']
				);
			}
		}

		// Validate file type.
		if ( isset( $attributes['filetypes'] ) ) {
			$allowed_types = array_map( 'trim', explode( '|', $attributes['filetypes'] ) );
			$file_ext      = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

			if ( ! in_array( $file_ext, $allowed_types, true ) ) {
				return sprintf(
					/* translators: %s: allowed file types */
					__( 'This file type is not allowed. Allowed types: %s', 'oli-contact-form' ),
					implode( ', ', $allowed_types )
				);
			}
		}

		return false;
	}

	/**
	 * Parse size string to bytes.
	 *
	 * @param string $size Size string (e.g., "2mb", "500kb").
	 * @return int Size in bytes.
	 */
	private static function parse_size( $size ) {
		$size  = strtolower( trim( $size ) );
		$value = intval( $size );

		if ( strpos( $size, 'gb' ) !== false ) {
			return $value * 1024 * 1024 * 1024;
		} elseif ( strpos( $size, 'mb' ) !== false ) {
			return $value * 1024 * 1024;
		} elseif ( strpos( $size, 'kb' ) !== false ) {
			return $value * 1024;
		}

		return $value;
	}
}
