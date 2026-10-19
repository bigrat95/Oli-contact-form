<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Oli_Contact_Form
 */

/**
 * Deactivator class.
 */
class Oli_CF_Deactivator {

	/**
	 * Deactivate the plugin.
	 */
	public static function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
