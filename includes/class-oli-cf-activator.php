<?php
/**
 * Fired during plugin activation.
 *
 * @package Oli_Contact_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		require_once plugin_dir_path( __FILE__ ) . 'class-oli-cf-message-store.php';

		Oli_CF_Message_Store::maybe_install();
		Oli_CF_Message_Store::schedule_cron();

		$defaults = Oli_CF_Message_Store::get_settings();
		$current  = get_option( 'oli_cf_settings', false );

		if ( false === $current || ! is_array( $current ) ) {
			add_option( 'oli_cf_settings', $defaults );
		} else {
			update_option( 'oli_cf_settings', wp_parse_args( $current, $defaults ) );
		}
	}
}
