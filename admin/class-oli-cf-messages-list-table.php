<?php
/**
 * Stored messages list table.
 *
 * @package Oli_Contact_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Admin list of stored form messages.
 */
class Oli_CF_Messages_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'oli_cf_message',
				'plural'   => 'oli_cf_messages',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Column headers.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'date'    => __( 'Date', 'oli-contact-form' ),
			'form'    => __( 'Form', 'oli-contact-form' ),
			'sender'  => __( 'Sender', 'oli-contact-form' ),
			'preview' => __( 'Preview', 'oli-contact-form' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'date'   => array( 'created_gmt', true ),
			'form'   => array( 'form_title', false ),
			'sender' => array( 'sender_email', false ),
		);
	}

	/**
	 * Bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'oli-contact-form' ),
		);
	}

	/**
	 * Checkbox column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_cb( $item ) {
		$id = absint( $item['id'] );

		return sprintf(
			'<input type="checkbox" name="message_ids[]" value="%d" />',
			$id
		);
	}

	/**
	 * Date column with row actions.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_date( $item ) {
		$id   = absint( $item['id'] );
		$gmt  = isset( $item['created_gmt'] ) ? $item['created_gmt'] : '';
		$date = $gmt ? get_date_from_gmt( $gmt, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) : '';

		$view_url = add_query_arg(
			array(
				'page'    => 'oli-cf-messages',
				'action'  => 'view',
				'message' => $id,
			),
			admin_url( 'edit.php?post_type=oli_contact_form' )
		);

		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'page'    => 'oli-cf-messages',
					'action'  => 'delete',
					'message' => $id,
				),
				admin_url( 'edit.php?post_type=oli_contact_form' )
			),
			'oli_cf_delete_message_' . $id
		);

		$actions = array(
			'view'   => sprintf(
				'<a href="%s">%s</a>',
				esc_url( $view_url ),
				esc_html__( 'View', 'oli-contact-form' )
			),
			'delete' => sprintf(
				'<a href="%s" class="submitdelete">%s</a>',
				esc_url( $delete_url ),
				esc_html__( 'Delete', 'oli-contact-form' )
			),
		);

		return '<strong>' . esc_html( $date ) . '</strong>' . $this->row_actions( $actions );
	}

	/**
	 * Form title column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_form( $item ) {
		$title = isset( $item['form_title'] ) ? $item['form_title'] : '';
		$id    = isset( $item['form_id'] ) ? absint( $item['form_id'] ) : 0;

		if ( $id ) {
			return sprintf(
				'%s <span class="description">(#%d)</span>',
				esc_html( $title ),
				$id
			);
		}

		return esc_html( $title );
	}

	/**
	 * Sender column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_sender( $item ) {
		$name  = isset( $item['sender_name'] ) ? $item['sender_name'] : '';
		$email = isset( $item['sender_email'] ) ? $item['sender_email'] : '';

		if ( $name && $email ) {
			return sprintf(
				'%s<br /><a href="%s">%s</a>',
				esc_html( $name ),
				esc_url( 'mailto:' . $email ),
				esc_html( $email )
			);
		}

		if ( $email ) {
			return sprintf(
				'<a href="%s">%s</a>',
				esc_url( 'mailto:' . $email ),
				esc_html( $email )
			);
		}

		return esc_html( $name );
	}

	/**
	 * Preview column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_preview( $item ) {
		$preview = isset( $item['preview'] ) ? $item['preview'] : '';

		return esc_html( $preview );
	}

	/**
	 * Fallback column renderer.
	 *
	 * @param array  $item        Row.
	 * @param string $column_name Column name.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( (string) $item[ $column_name ] ) : '';
	}

	/**
	 * Empty list message.
	 */
	public function no_items() {
		esc_html_e( 'No stored messages.', 'oli-contact-form' );
	}

	/**
	 * Query and prepare rows.
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- search is read-only.

		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_gmt'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order   = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$total        = Oli_CF_Message_Store::count( $search );

		$this->items = Oli_CF_Message_Store::query(
			array(
				'search'   => $search,
				'orderby'  => $orderby,
				'order'    => $order,
				'per_page' => $per_page,
				'offset'   => ( $current_page - 1 ) * $per_page,
			)
		);

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total / $per_page ),
			)
		);
	}

	/**
	 * Handle bulk delete.
	 */
	public function process_bulk_action() {
		if ( 'delete' !== $this->current_action() ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'oli-contact-form' ) );
		}

		check_admin_referer( 'bulk-oli_cf_messages' );

		$ids = isset( $_REQUEST['message_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_REQUEST['message_ids'] ) ) : array();

		Oli_CF_Message_Store::delete_many( $ids );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'oli-cf-messages',
					'deleted' => count( $ids ),
				),
				admin_url( 'edit.php?post_type=oli_contact_form' )
			)
		);
		exit;
	}
}
