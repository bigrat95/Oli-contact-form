<?php
/**
 * Parse CF7-like tag syntax and generate HTML.
 *
 * @package Oli_Contact_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form parser class.
 */
class Oli_CF_Form_Parser {

	/**
	 * Parse form template and return HTML.
	 *
	 * @param string $template Form template with CF7-like tags.
	 * @param int    $form_id  Form ID.
	 * @return string Parsed HTML.
	 */
	public static function parse( $template, $form_id ) {
		$html = $template;

		// Parse all tags.
		$html = preg_replace_callback(
			'/\[([^\]]+)\]/',
			function ( $matches ) use ( $form_id ) {
				return self::parse_tag( $matches[1], $form_id );
			},
			$html
		);

		return $html;
	}

	/**
	 * Parse a single tag.
	 *
	 * @param string $tag     Tag content.
	 * @param int    $form_id Form ID.
	 * @return string HTML for the tag.
	 */
	private static function parse_tag( $tag, $form_id ) {
		$tag   = trim( $tag );
		$parts = preg_split( '/\s+/', $tag );

		if ( empty( $parts ) ) {
			return '';
		}

		$type_raw = array_shift( $parts );
		$required = false;

		// Check for required marker (*).
		if ( substr( $type_raw, -1 ) === '*' ) {
			$required = true;
			$type     = rtrim( $type_raw, '*' );
		} else {
			$type = $type_raw;
		}

		$name    = ! empty( $parts ) ? array_shift( $parts ) : '';
		$options = $parts;

		// Parse type-specific tags.
		switch ( $type ) {
			case 'text':
			case 'email':
			case 'tel':
			case 'url':
			case 'number':
			case 'date':
				return self::render_input_field( $type, $name, $required, $options, $form_id );

			case 'textarea':
				return self::render_textarea( $name, $required, $options, $form_id );

			case 'select':
				return self::render_select( $name, $required, $options, $form_id );

			case 'checkbox':
				return self::render_checkbox( $name, $required, $options, $form_id );

			case 'radio':
				return self::render_radio( $name, $required, $options, $form_id );

			case 'acceptance':
				return self::render_acceptance( $name, $required, $options, $form_id );

			case 'file':
				return self::render_file( $name, $required, $options, $form_id );

			case 'submit':
				return self::render_submit( $name, $options, $form_id );

			default:
				return '';
		}
	}

	/**
	 * Render input field (text, email, tel, url, number, date).
	 *
	 * @param string $type     Field type.
	 * @param string $name     Field name.
	 * @param bool   $required Is required.
	 * @param array  $options  Field options.
	 * @param int    $form_id  Form ID.
	 * @return string HTML.
	 */
	private static function render_input_field( $type, $name, $required, $options, $form_id ) {
		$attributes = self::parse_options( $options );
		$id         = 'oli-cf-' . $form_id . '-' . sanitize_key( $name );
		$class      = 'oli-cf-field oli-cf-' . $type;

		if ( $required ) {
			$class .= ' oli-cf-required';
		}

		$placeholder = isset( $attributes['placeholder'] ) ? $attributes['placeholder'] : '';
		$default     = isset( $attributes['default'] ) ? $attributes['default'] : '';

		$html = sprintf(
			'<span class="oli-cf-form-control-wrap" data-name="%s">',
			esc_attr( $name )
		);

		$html .= sprintf(
			'<input type="%s" name="%s" id="%s" class="%s" %s %s %s aria-invalid="false" />',
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( $class ),
			$required ? 'required="required" aria-required="true"' : '',
			$placeholder ? 'placeholder="' . esc_attr( $placeholder ) . '"' : '',
			$default ? 'value="' . esc_attr( $default ) . '"' : ''
		);

		$html .= '</span>';

		return $html;
	}

	/**
	 * Render textarea field.
	 *
	 * @param string $name     Field name.
	 * @param bool   $required Is required.
	 * @param array  $options  Field options.
	 * @param int    $form_id  Form ID.
	 * @return string HTML.
	 */
	private static function render_textarea( $name, $required, $options, $form_id ) {
		$attributes = self::parse_options( $options );
		$id         = 'oli-cf-' . $form_id . '-' . sanitize_key( $name );
		$class      = 'oli-cf-field oli-cf-textarea';

		if ( $required ) {
			$class .= ' oli-cf-required';
		}

		$placeholder = isset( $attributes['placeholder'] ) ? $attributes['placeholder'] : '';
		$default     = isset( $attributes['default'] ) ? $attributes['default'] : '';
		$rows        = isset( $attributes['rows'] ) ? intval( $attributes['rows'] ) : 4;
		$cols        = isset( $attributes['cols'] ) ? intval( $attributes['cols'] ) : 40;

		$html = sprintf(
			'<span class="oli-cf-form-control-wrap" data-name="%s">',
			esc_attr( $name )
		);

		$html .= sprintf(
			'<textarea name="%s" id="%s" class="%s" rows="%d" cols="%d" %s %s aria-invalid="false">%s</textarea>',
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( $class ),
			$rows,
			$cols,
			$required ? 'required="required" aria-required="true"' : '',
			$placeholder ? 'placeholder="' . esc_attr( $placeholder ) . '"' : '',
			esc_textarea( $default )
		);

		$html .= '</span>';

		return $html;
	}

	/**
	 * Render select field.
	 *
	 * @param string $name     Field name.
	 * @param bool   $required Is required.
	 * @param array  $options  Field options.
	 * @param int    $form_id  Form ID.
	 * @return string HTML.
	 */
	private static function render_select( $name, $required, $options, $form_id ) {
		$id    = 'oli-cf-' . $form_id . '-' . sanitize_key( $name );
		$class = 'oli-cf-field oli-cf-select';

		if ( $required ) {
			$class .= ' oli-cf-required';
		}

		$html = sprintf(
			'<span class="oli-cf-form-control-wrap" data-name="%s">',
			esc_attr( $name )
		);

		$html .= sprintf(
			'<select name="%s" id="%s" class="%s" %s aria-invalid="false">',
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( $class ),
			$required ? 'required="required" aria-required="true"' : ''
		);

		// First option is empty if required.
		if ( $required ) {
			$html .= '<option value="">---</option>';
		}

		// Add options.
		foreach ( $options as $option ) {
			$option = trim( $option, '"' );
			$html  .= sprintf(
				'<option value="%s">%s</option>',
				esc_attr( $option ),
				esc_html( $option )
			);
		}

		$html .= '</select>';
		$html .= '</span>';

		return $html;
	}

	/**
	 * Render checkbox field.
	 *
	 * @param string $name     Field name.
	 * @param bool   $required Is required.
	 * @param array  $options  Field options.
	 * @param int    $form_id  Form ID.
	 * @return string HTML.
	 */
	private static function render_checkbox( $name, $required, $options, $form_id ) {
		$id    = 'oli-cf-' . $form_id . '-' . sanitize_key( $name );
		$class = 'oli-cf-field oli-cf-checkbox';

		if ( $required ) {
			$class .= ' oli-cf-required';
		}

		$html = sprintf(
			'<span class="oli-cf-form-control-wrap" data-name="%s">',
			esc_attr( $name )
		);

		foreach ( $options as $index => $option ) {
			$option      = trim( $option, '"' );
			$checkbox_id = $id . '-' . $index;

			$html .= sprintf(
				'<span class="oli-cf-list-item"><label><input type="checkbox" name="%s[]" value="%s" id="%s" class="%s" %s /><span class="oli-cf-list-item-label">%s</span></label></span>',
				esc_attr( $name ),
				esc_attr( $option ),
				esc_attr( $checkbox_id ),
				esc_attr( $class ),
				$required ? 'required="required" aria-required="true"' : '',
				esc_html( $option )
			);
		}

		$html .= '</span>';

		return $html;
	}

	/**
	 * Render radio field.
	 *
	 * @param string $name     Field name.
	 * @param bool   $required Is required.
	 * @param array  $options  Field options.
	 * @param int    $form_id  Form ID.
	 * @return string HTML.
	 */
	private static function render_radio( $name, $required, $options, $form_id ) {
		$id    = 'oli-cf-' . $form_id . '-' . sanitize_key( $name );
		$class = 'oli-cf-field oli-cf-radio';

		if ( $required ) {
			$class .= ' oli-cf-required';
		}

		$html = sprintf(
			'<span class="oli-cf-form-control-wrap" data-name="%s">',
			esc_attr( $name )
		);

		foreach ( $options as $index => $option ) {
			$option   = trim( $option, '"' );
			$radio_id = $id . '-' . $index;

			$html .= sprintf(
				'<span class="oli-cf-list-item"><label><input type="radio" name="%s" value="%s" id="%s" class="%s" %s /><span class="oli-cf-list-item-label">%s</span></label></span>',
				esc_attr( $name ),
				esc_attr( $option ),
				esc_attr( $radio_id ),
				esc_attr( $class ),
				$required ? 'required="required" aria-required="true"' : '',
				esc_html( $option )
			);
		}

		$html .= '</span>';

		return $html;
	}

	/**
	 * Render acceptance field.
	 *
	 * @param string $name     Field name.
	 * @param bool   $required Is required. Optional consent uses `[acceptance ...]` without `*`.
	 * @param array  $options  Field options.
	 * @param int    $form_id  Form ID.
	 * @return string HTML.
	 */
	private static function render_acceptance( $name, $required, $options, $form_id ) {
		$id    = 'oli-cf-' . $form_id . '-' . sanitize_key( $name );
		$label = ! empty( $options ) ? trim( implode( ' ', $options ), '"' ) : __( 'I accept the terms', 'oli-contact-form' );
		$class = 'oli-cf-field oli-cf-acceptance';

		if ( $required ) {
			$class .= ' oli-cf-required';
		}

		$html = sprintf(
			'<span class="oli-cf-form-control-wrap" data-name="%s">',
			esc_attr( $name )
		);

		$html .= sprintf(
			'<span class="oli-cf-list-item"><label><input type="checkbox" name="%s" value="1" id="%s" class="%s" %s aria-invalid="false" /><span class="oli-cf-list-item-label">%s</span></label></span>',
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( $class ),
			$required ? 'required="required" aria-required="true"' : '',
			wp_kses_post( $label )
		);

		$html .= '</span>';

		return $html;
	}

	/**
	 * Render file field.
	 *
	 * @param string $name     Field name.
	 * @param bool   $required Is required.
	 * @param array  $options  Field options.
	 * @param int    $form_id  Form ID.
	 * @return string HTML.
	 */
	private static function render_file( $name, $required, $options, $form_id ) {
		$attributes = self::parse_options( $options );
		$id         = 'oli-cf-' . $form_id . '-' . sanitize_key( $name );
		$class      = 'oli-cf-field oli-cf-file';

		if ( $required ) {
			$class .= ' oli-cf-required';
		}

		$accept = isset( $attributes['filetypes'] ) ? $attributes['filetypes'] : '';
		$limit  = isset( $attributes['limit'] ) ? $attributes['limit'] : '';

		$html = sprintf(
			'<span class="oli-cf-form-control-wrap" data-name="%s">',
			esc_attr( $name )
		);

		$html .= sprintf(
			'<input type="file" name="%s" id="%s" class="%s" %s %s %s aria-invalid="false" />',
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( $class ),
			$required ? 'required="required" aria-required="true"' : '',
			$accept ? 'accept="' . esc_attr( $accept ) . '"' : '',
			$limit ? 'data-size-limit="' . esc_attr( $limit ) . '"' : ''
		);

		$html .= '</span>';

		return $html;
	}

	/**
	 * Render submit button.
	 *
	 * @param string $name     Button name.
	 * @param array  $options  Button options.
	 * @param int    $form_id  Form ID.
	 * @return string HTML.
	 */
	private static function render_submit( $name, $options, $form_id ) {
		$label = ! empty( $options ) ? implode( ' ', $options ) : __( 'Send', 'oli-contact-form' );
		$label = trim( $label, '"' );

		return sprintf(
			'<button type="submit" class="oli-cf-submit" id="oli-cf-%d-submit">%s</button>',
			absint( $form_id ),
			esc_html( $label )
		);
	}

	/**
	 * Parse options from tag parts.
	 *
	 * @param array $options Options array.
	 * @return array Parsed attributes.
	 */
	private static function parse_options( $options ) {
		$attributes = array();

		foreach ( $options as $option ) {
			if ( strpos( $option, ':' ) !== false ) {
				list( $key, $value )        = explode( ':', $option, 2 );
				$attributes[ trim( $key ) ] = trim( $value, '"' );
			}
		}

		return $attributes;
	}
}
