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
		require_once plugin_dir_path( __FILE__ ) . 'class-oli-cf-message-store.php';

		Oli_CF_Message_Store::unschedule_cron();

		flush_rewrite_rules();
	}
}
