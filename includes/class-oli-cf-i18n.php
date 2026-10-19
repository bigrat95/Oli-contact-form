<?php
/**
 * Define the internationalization functionality.
 *
 * @package Oli_Contact_Form
 */

/**
 * I18n class.
 */
class Oli_CF_I18n {

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'oli-contact-form',
			false,
			dirname( OLI_CF_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
