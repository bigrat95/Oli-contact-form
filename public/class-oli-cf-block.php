<?php
/**
 * Gutenberg block functionality.
 *
 * @package Oli_Contact_Form
 */

/**
 * Block class.
 */
class Oli_CF_Block {

	/**
	 * Register block.
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'oli-cf-block-editor',
			OLI_CF_PLUGIN_URL . 'assets/js/oli-cf-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
			OLI_CF_VERSION,
			true
		);

		$forms = $this->get_forms_list();

		wp_localize_script(
			'oli-cf-block-editor',
			'oliCfBlock',
			array(
				'forms' => $forms,
			)
		);

		register_block_type(
			'oli-cf/contact-form',
			array(
				'editor_script'   => 'oli-cf-block-editor',
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'formId' => array(
						'type'    => 'number',
						'default' => 0,
					),
				),
			)
		);
	}

	/**
	 * Render block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	public function render_block( $attributes ) {
		$form_id = isset( $attributes['formId'] ) ? absint( $attributes['formId'] ) : 0;

		if ( ! $form_id ) {
			return '';
		}

		$shortcode = new Oli_CF_Shortcode();
		return $shortcode->render_shortcode( array( 'id' => $form_id ) );
	}

	/**
	 * Get list of forms for block editor.
	 *
	 * @return array Forms list.
	 */
	private function get_forms_list() {
		$forms = get_posts(
			array(
				'post_type'      => 'oli_contact_form',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$forms_list = array();

		foreach ( $forms as $form ) {
			$forms_list[] = array(
				'value' => $form->ID,
				'label' => $form->post_title,
			);
		}

		return $forms_list;
	}
}
