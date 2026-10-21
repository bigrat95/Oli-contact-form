<?php
/**
 * Fired during plugin activation.
 *
 * @package Oli_Contact_Form
 */

/**
 * Activator class.
 */
class Oli_CF_Activator {

	/**
	 * Activate the plugin.
	 */
	public static function activate() {
		// Flush rewrite rules to register custom post type.
		flush_rewrite_rules();

		// Set default options if they don't exist.
		$default_options = array(
			'recaptcha_enabled'    => false,
			'recaptcha_site_key'   => '',
			'recaptcha_secret_key' => '',
			'turnstile_enabled'    => false,
			'turnstile_site_key'   => '',
			'turnstile_secret_key' => '',
			'cf7_compat_enabled'   => false,
		);

		if ( false === get_option( 'oli_cf_settings' ) ) {
			add_option( 'oli_cf_settings', $default_options );
		}
	}
}
