<?php
/**
 * Optional stored copies of form submissions.
 *
 * @package Oli_Contact_Form
 */

/**
 * Message store: table, save, query, purge, and privacy helpers.
 *
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom oli_cf_messages table has no object cache group.
 */
class Oli_CF_Message_Store {

	/**
	 * Database schema version.
	 *
	 * @var string
	 */
	const DB_VERSION = '1';

	/**
	 * Option that stores the installed schema version.
	 *
	 * @var string
	 */
	const DB_VERSION_OPTION = 'oli_cf_db_version';

	/**
	 * Daily purge cron hook.
	 *
	 * @var string
	 */
	const CRON_HOOK = 'oli_cf_purge_stored_messages';

	/**
	 * Table suffix after $wpdb->prefix.
	 *
	 * @var string
	 */
	const TABLE_SUFFIX = 'oli_cf_messages';

	/**
	 * Create or update the messages table.
	 */
	public static function maybe_install() {
		$installed = get_option( self::DB_VERSION_OPTION, '' );

		if ( self::DB_VERSION === $installed ) {
			return;
		}

		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			form_id bigint(20) unsigned NOT NULL,
			form_title varchar(255) NOT NULL DEFAULT '',
			sender_name varchar(255) NOT NULL DEFAULT '',
			sender_email varchar(190) NOT NULL DEFAULT '',
			preview varchar(255) NOT NULL DEFAULT '',
			fields longtext NOT NULL,
			created_gmt datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY form_id (form_id),
			KEY sender_email (sender_email),
			KEY created_gmt (created_gmt)
		) {$charset};";

		dbDelta( $sql );
		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Prefixed table name.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . self::TABLE_SUFFIX;
	}

	/**
	 * Schedule the daily purge if it is not already scheduled.
	 */
	public static function schedule_cron() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
		}
	}

	/**
	 * Clear the purge cron.
	 */
	public static function unschedule_cron() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Whether storing copies is enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$settings = self::get_settings();

		return ! empty( $settings['store_messages'] );
	}

	/**
	 * Retention in days. 0 means keep forever.
	 *
	 * @return int
	 */
	public static function retention_days() {
		$settings = self::get_settings();

		return isset( $settings['retention_days'] ) ? absint( $settings['retention_days'] ) : 30;
	}

	/**
	 * Plugin settings with defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$defaults = array(
			'recaptcha_enabled'    => false,
			'recaptcha_site_key'   => '',
			'recaptcha_secret_key' => '',
			'turnstile_enabled'    => false,
			'turnstile_site_key'   => '',
			'turnstile_secret_key' => '',
			'cf7_compat_enabled'   => false,
			'store_messages'       => false,
			'retention_days'       => 30,
		);

		$settings = get_option( 'oli_cf_settings', array() );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Keys that must never be stored.
	 *
	 * @return array
	 */
	public static function skipped_keys() {
		return array(
			'action',
			'nonce',
			'oli_cf_nonce',
			'oli_cf_form_id',
			'oli_cf_timestamp',
			'oli_cf_hp',
			'oli_cf_submit',
			'form_id',
			'g-recaptcha-response',
			'cf-turnstile-response',
			'_wp_http_referer',
			'_wpnonce',
		);
	}

	/**
	 * Sanitize submitted fields for storage.
	 *
	 * @param array $data  Posted data.
	 * @param array $files Uploaded files (names only).
	 * @return array
	 */
	public static function sanitize_fields( $data, $files = array() ) {
		$skipped = self::skipped_keys();
		$fields  = array();

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		foreach ( $data as $key => $value ) {
			$key = sanitize_key( $key );

			if ( '' === $key || in_array( $key, $skipped, true ) ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$fields[ $key ] = array_map( 'sanitize_text_field', wp_unslash( $value ) );
				continue;
			}

			$value = wp_unslash( $value );

			if ( is_string( $value ) && strlen( $value ) > 200 ) {
				$fields[ $key ] = sanitize_textarea_field( $value );
			} else {
				$fields[ $key ] = sanitize_text_field( $value );
			}
		}

		if ( is_array( $files ) ) {
			foreach ( $files as $key => $file ) {
				$key = sanitize_key( $key );

				if ( '' === $key || empty( $file['name'] ) ) {
					continue;
				}

				$fields[ $key ] = sanitize_file_name( $file['name'] );
			}
		}

		return $fields;
	}

	/**
	 * Find a sender email in sanitized fields.
	 *
	 * @param array $fields Sanitized fields.
	 * @return string
	 */
	public static function find_sender_email( $fields ) {
		$preferred = array( 'your-email', 'your_email', 'email', 'e-mail' );

		foreach ( $preferred as $key ) {
			if ( ! empty( $fields[ $key ] ) && is_email( $fields[ $key ] ) ) {
				return $fields[ $key ];
			}
		}

		foreach ( $fields as $key => $value ) {
			if ( is_string( $value ) && is_email( $value ) && false !== strpos( $key, 'email' ) ) {
				return $value;
			}
		}

		foreach ( $fields as $value ) {
			if ( is_string( $value ) && is_email( $value ) ) {
				return $value;
			}
		}

		return '';
	}

	/**
	 * Find a sender name in sanitized fields.
	 *
	 * @param array $fields Sanitized fields.
	 * @return string
	 */
	public static function find_sender_name( $fields ) {
		$preferred = array( 'your-name', 'your_name', 'name', 'full-name', 'full_name' );

		foreach ( $preferred as $key ) {
			if ( ! empty( $fields[ $key ] ) && is_string( $fields[ $key ] ) && ! is_email( $fields[ $key ] ) ) {
				return $fields[ $key ];
			}
		}

		return '';
	}

	/**
	 * Build a short preview from sanitized fields.
	 *
	 * @param array $fields Sanitized fields.
	 * @return string
	 */
	public static function build_preview( $fields ) {
		$preferred = array( 'your-message', 'your_message', 'message', 'your-subject', 'subject' );

		foreach ( $preferred as $key ) {
			if ( ! empty( $fields[ $key ] ) && is_string( $fields[ $key ] ) ) {
				return self::excerpt( $fields[ $key ] );
			}
		}

		foreach ( $fields as $key => $value ) {
			if ( in_array( $key, array( 'your-email', 'email', 'your-name', 'name' ), true ) ) {
				continue;
			}

			if ( is_string( $value ) && '' !== $value ) {
				return self::excerpt( $value );
			}
		}

		return '';
	}

	/**
	 * Trim a string for list previews.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	public static function excerpt( $text ) {
		$text = preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $text ) );
		$text = trim( $text );

		if ( function_exists( 'mb_substr' ) && function_exists( 'mb_strlen' ) ) {
			if ( mb_strlen( $text ) > 120 ) {
				return mb_substr( $text, 0, 117 ) . '...';
			}

			return $text;
		}

		if ( strlen( $text ) > 120 ) {
			return substr( $text, 0, 117 ) . '...';
		}

		return $text;
	}

	/**
	 * GMT cutoff datetime for retention, or empty when retention is unlimited.
	 *
	 * @param int    $retention_days Days to keep. 0 = unlimited.
	 * @param string $now_gmt        Optional GMT datetime.
	 * @return string
	 */
	public static function cutoff_gmt( $retention_days, $now_gmt = '' ) {
		$days = absint( $retention_days );

		if ( $days < 1 ) {
			return '';
		}

		if ( '' === $now_gmt ) {
			$now_gmt = gmdate( 'Y-m-d H:i:s' );
		}

		$now_ts = strtotime( $now_gmt . ' UTC' );

		if ( false === $now_ts ) {
			return '';
		}

		return gmdate( 'Y-m-d H:i:s', $now_ts - ( $days * DAY_IN_SECONDS ) );
	}

	/**
	 * Save a copy after a successful send when the option is on.
	 *
	 * @param int   $form_id Form ID.
	 * @param array $data    Posted data.
	 * @param array $files   Uploaded files.
	 * @return int Inserted ID or 0.
	 */
	public static function maybe_save( $form_id, $data, $files = array() ) {
		if ( ! self::is_enabled() ) {
			return 0;
		}

		$form_id = absint( $form_id );
		$form    = get_post( $form_id );

		if ( ! $form || 'oli_contact_form' !== $form->post_type ) {
			return 0;
		}

		$fields = self::sanitize_fields( $data, $files );
		$email  = self::find_sender_email( $fields );
		$name   = self::find_sender_name( $fields );

		global $wpdb;

		$inserted = $wpdb->insert(
			self::table_name(),
			array(
				'form_id'      => $form_id,
				'form_title'   => sanitize_text_field( $form->post_title ),
				'sender_name'  => $name,
				'sender_email' => $email,
				'preview'      => self::build_preview( $fields ),
				'fields'       => wp_json_encode( $fields ),
				'created_gmt'  => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return 0;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Get one stored message.
	 *
	 * @param int $id Message ID.
	 * @return array|null
	 */
	public static function get( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( ! $id ) {
			return null;
		}

		$table = self::table_name();
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is plugin-controlled.
				$id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Delete one stored message.
	 *
	 * @param int $id Message ID.
	 * @return bool
	 */
	public static function delete( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( ! $id ) {
			return false;
		}

		$deleted = $wpdb->delete( self::table_name(), array( 'id' => $id ), array( '%d' ) );

		return false !== $deleted && $deleted > 0;
	}

	/**
	 * Delete several stored messages.
	 *
	 * @param array $ids Message IDs.
	 * @return int Number deleted.
	 */
	public static function delete_many( $ids ) {
		$ids = array_filter( array_map( 'absint', (array) $ids ) );

		if ( empty( $ids ) ) {
			return 0;
		}

		$deleted = 0;

		foreach ( $ids as $id ) {
			if ( self::delete( $id ) ) {
				++$deleted;
			}
		}

		return $deleted;
	}

	/**
	 * Count stored messages, optionally filtered by a search string.
	 *
	 * @param string $search Search string.
	 * @return int
	 */
	public static function count( $search = '' ) {
		global $wpdb;

		$table = self::table_name();
		$sql   = "SELECT COUNT(*) FROM {$table}"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is plugin-controlled.
		$args  = array();

		$search_sql = self::search_sql( $search, $args );

		if ( '' !== $search_sql ) {
			$sql .= $search_sql;
		}

		if ( ! empty( $args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- SQL is built from a whitelist plus prepare().
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, $args ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is plugin-controlled.
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Query stored messages for the list table.
	 *
	 * @param array $args Query args.
	 * @return array
	 */
	public static function query( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'search'   => '',
			'orderby'  => 'created_gmt',
			'order'    => 'DESC',
			'per_page' => 20,
			'offset'   => 0,
		);

		$args     = wp_parse_args( $args, $defaults );
		$orderby  = self::sanitize_orderby( $args['orderby'] );
		$order    = ( 'ASC' === strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';
		$per_page = max( 1, absint( $args['per_page'] ) );
		$offset   = max( 0, absint( $args['offset'] ) );
		$table    = self::table_name();
		$sql      = "SELECT * FROM {$table}"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is plugin-controlled.
		$params   = array();

		$search_sql = self::search_sql( $args['search'], $params );

		if ( '' !== $search_sql ) {
			$sql .= $search_sql;
		}

		$sql     .= " ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$params[] = $per_page;
		$params[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- orderby is whitelisted; the rest is prepared.
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Find messages that belong to an email address.
	 *
	 * @param string $email Email address.
	 * @return array
	 */
	public static function find_by_email( $email ) {
		$email = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			return array();
		}

		global $wpdb;

		$table = self::table_name();
		$like  = '%' . $wpdb->esc_like( $email ) . '%';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE sender_email = %s OR fields LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is plugin-controlled.
				$email,
				$like
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		$matched = array();

		foreach ( $rows as $row ) {
			if ( self::row_matches_email( $row, $email ) ) {
				$matched[] = $row;
			}
		}

		return $matched;
	}

	/**
	 * Whether a stored row is associated with an email.
	 *
	 * @param array  $row   Database row.
	 * @param string $email Email address.
	 * @return bool
	 */
	public static function row_matches_email( $row, $email ) {
		$email = strtolower( (string) $email );

		if ( '' === $email ) {
			return false;
		}

		if ( ! empty( $row['sender_email'] ) && strtolower( $row['sender_email'] ) === $email ) {
			return true;
		}

		$fields = array();

		if ( ! empty( $row['fields'] ) ) {
			$decoded = json_decode( $row['fields'], true );
			if ( is_array( $decoded ) ) {
				$fields = $decoded;
			}
		}

		foreach ( $fields as $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $item ) {
					if ( is_string( $item ) && strtolower( $item ) === $email ) {
						return true;
					}
				}
				continue;
			}

			if ( is_string( $value ) && strtolower( $value ) === $email ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Delete every stored copy for an email address.
	 *
	 * @param string $email Email address.
	 * @return int
	 */
	public static function delete_by_email( $email ) {
		$rows = self::find_by_email( $email );
		$ids  = array();

		foreach ( $rows as $row ) {
			if ( ! empty( $row['id'] ) ) {
				$ids[] = (int) $row['id'];
			}
		}

		return self::delete_many( $ids );
	}

	/**
	 * Delete copies older than the retention period.
	 *
	 * @return int Number deleted.
	 */
	public static function purge_expired() {
		$cutoff = self::cutoff_gmt( self::retention_days() );

		if ( '' === $cutoff ) {
			return 0;
		}

		global $wpdb;

		$table = self::table_name();

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE created_gmt < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is plugin-controlled.
				$cutoff
			)
		);

		return ( false === $deleted ) ? 0 : (int) $deleted;
	}

	/**
	 * Build the privacy exporter payload for one email.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Page number (unused; all rows are returned).
	 * @return array
	 */
	public static function privacy_exporter( $email, $page = 1 ) {
		unset( $page );

		$rows   = self::find_by_email( $email );
		$export = array();

		foreach ( $rows as $row ) {
			$export[] = self::export_item_from_row( $row );
		}

		return array(
			'data' => $export,
			'done' => true,
		);
	}

	/**
	 * Build the privacy eraser payload for one email.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Page number (unused; all rows are deleted).
	 * @return array
	 */
	public static function privacy_eraser( $email, $page = 1 ) {
		unset( $page );

		$deleted = self::delete_by_email( $email );

		return array(
			'items_removed'  => $deleted > 0,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	/**
	 * Turn a database row into a privacy export item.
	 *
	 * @param array $row Database row.
	 * @return array
	 */
	public static function export_item_from_row( $row ) {
		$fields = array();

		if ( ! empty( $row['fields'] ) ) {
			$decoded = json_decode( $row['fields'], true );
			if ( is_array( $decoded ) ) {
				$fields = $decoded;
			}
		}

		$data = array(
			array(
				'name'  => __( 'Date (GMT)', 'oli-contact-form' ),
				'value' => isset( $row['created_gmt'] ) ? $row['created_gmt'] : '',
			),
			array(
				'name'  => __( 'Form', 'oli-contact-form' ),
				'value' => isset( $row['form_title'] ) ? $row['form_title'] : '',
			),
			array(
				'name'  => __( 'Sender name', 'oli-contact-form' ),
				'value' => isset( $row['sender_name'] ) ? $row['sender_name'] : '',
			),
			array(
				'name'  => __( 'Sender email', 'oli-contact-form' ),
				'value' => isset( $row['sender_email'] ) ? $row['sender_email'] : '',
			),
		);

		foreach ( $fields as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}

			$data[] = array(
				'name'  => (string) $key,
				'value' => (string) $value,
			);
		}

		return array(
			'group_id'    => 'oli_cf_messages',
			'group_label' => __( 'Contact form messages', 'oli-contact-form' ),
			'item_id'     => 'oli-cf-message-' . ( isset( $row['id'] ) ? absint( $row['id'] ) : 0 ),
			'data'        => $data,
		);
	}

	/**
	 * Register the privacy exporter.
	 *
	 * @param array $exporters Existing exporters.
	 * @return array
	 */
	public static function register_exporter( $exporters ) {
		$exporters['oli-contact-form'] = array(
			'exporter_friendly_name' => __( 'Oli Contact Form messages', 'oli-contact-form' ),
			'callback'               => array( 'Oli_CF_Message_Store', 'privacy_exporter' ),
		);

		return $exporters;
	}

	/**
	 * Register the privacy eraser.
	 *
	 * @param array $erasers Existing erasers.
	 * @return array
	 */
	public static function register_eraser( $erasers ) {
		$erasers['oli-contact-form'] = array(
			'eraser_friendly_name' => __( 'Oli Contact Form messages', 'oli-contact-form' ),
			'callback'             => array( 'Oli_CF_Message_Store', 'privacy_eraser' ),
		);

		return $erasers;
	}

	/**
	 * Suggested privacy policy paragraph.
	 */
	public static function add_privacy_policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content  = '<p>' . esc_html__( 'By default, Oli Contact Form sends messages by email and does not keep a copy in the WordPress database.', 'oli-contact-form' ) . '</p>';
		$content .= '<p>' . esc_html__( 'If an administrator enables “Keep a copy of messages”, submitted fields (such as name, email address, and message text) are stored in the WordPress database until they are deleted by an administrator or by the automatic retention purge.', 'oli-contact-form' ) . '</p>';
		$content .= '<p>' . esc_html__( 'File uploads are attached to the notification email and are not stored. Optional anti-spam services (Google reCAPTCHA or Cloudflare Turnstile) may process visitor data according to those providers’ policies when they are enabled.', 'oli-contact-form' ) . '</p>';

		wp_add_privacy_policy_content( __( 'Oli Contact Form', 'oli-contact-form' ), $content );
	}

	/**
	 * Whitelist a list-table orderby column.
	 *
	 * @param string $orderby Requested column.
	 * @return string
	 */
	private static function sanitize_orderby( $orderby ) {
		$allowed = array(
			'created_gmt'  => 'created_gmt',
			'form_title'   => 'form_title',
			'sender_email' => 'sender_email',
			'sender_name'  => 'sender_name',
			'date'         => 'created_gmt',
			'form'         => 'form_title',
			'sender'       => 'sender_email',
		);

		return isset( $allowed[ $orderby ] ) ? $allowed[ $orderby ] : 'created_gmt';
	}

	/**
	 * Build a WHERE fragment for list search.
	 *
	 * @param string $search Search string.
	 * @param array  $args   Prepared-statement arguments (passed by reference).
	 * @return string
	 */
	private static function search_sql( $search, &$args ) {
		$search = trim( (string) $search );

		if ( '' === $search ) {
			return '';
		}

		global $wpdb;

		$like   = '%' . $wpdb->esc_like( $search ) . '%';
		$args[] = $like;
		$args[] = $like;
		$args[] = $like;
		$args[] = $like;

		return ' WHERE sender_email LIKE %s OR sender_name LIKE %s OR form_title LIKE %s OR preview LIKE %s';
	}
}
