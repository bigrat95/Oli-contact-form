<?php
/**
 * Uninstall Oli Contact Form.
 *
 * @package Oli_Contact_Form
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$table = $wpdb->prefix . 'oli_cf_messages';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- uninstall drops the plugin table.
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

delete_option( 'oli_cf_settings' );
delete_option( 'oli_cf_db_version' );

wp_clear_scheduled_hook( 'oli_cf_purge_stored_messages' );

$forms = get_posts(
	array(
		'post_type'      => 'oli_contact_form',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	)
);

foreach ( $forms as $form_id ) {
	wp_delete_post( (int) $form_id, true );
}
