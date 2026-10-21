<?php
/**
 * Admin screens for stored messages.
 *
 * @package Oli_Contact_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Messages admin page.
 */
class Oli_CF_Messages_Admin {

	/**
	 * Add the Messages submenu.
	 */
	public function add_messages_page() {
		add_submenu_page(
			'edit.php?post_type=oli_contact_form',
			__( 'Messages', 'oli-contact-form' ),
			__( 'Messages', 'oli-contact-form' ),
			'manage_options',
			'oli-cf-messages',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Handle single-row delete before output.
	 */
	public function handle_actions() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'oli-cf-messages' !== $page ) {
			return;
		}

		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id     = isset( $_GET['message'] ) ? absint( $_GET['message'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'delete' !== $action || ! $id ) {
			return;
		}

		check_admin_referer( 'oli_cf_delete_message_' . $id );

		Oli_CF_Message_Store::delete( $id );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'oli-cf-messages',
					'deleted' => 1,
				),
				admin_url( 'edit.php?post_type=oli_contact_form' )
			)
		);
		exit;
	}

	/**
	 * Render list or detail.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id     = isset( $_GET['message'] ) ? absint( $_GET['message'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'view' === $action && $id ) {
			$this->render_detail( $id );
			return;
		}

		$this->render_list();
	}

	/**
	 * Render the list table.
	 */
	private function render_list() {
		require_once OLI_CF_PLUGIN_DIR . 'admin/class-oli-cf-messages-list-table.php';

		$list_table = new Oli_CF_Messages_List_Table();
		$list_table->prepare_items();

		$deleted = isset( $_GET['deleted'] ) ? absint( $_GET['deleted'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Messages', 'oli-contact-form' ); ?></h1>

			<?php if ( $deleted ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: number of deleted messages */
								_n( '%d message deleted.', '%d messages deleted.', $deleted, 'oli-contact-form' ),
								$deleted
							)
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( ! Oli_CF_Message_Store::is_enabled() ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'Keeping a copy of messages is disabled. Existing copies stay here until you delete them or the retention purge runs. Enable the option under Settings to store new submissions.', 'oli-contact-form' ); ?></p>
				</div>
			<?php else : ?>
				<p class="description">
					<?php
					$days = Oli_CF_Message_Store::retention_days();
					if ( $days > 0 ) {
						echo esc_html(
							sprintf(
								/* translators: %d: retention in days */
								__( 'New submissions are stored. Copies older than %d days are deleted automatically.', 'oli-contact-form' ),
								$days
							)
						);
					} else {
						esc_html_e( 'New submissions are stored with no automatic expiry.', 'oli-contact-form' );
					}
					?>
				</p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'edit.php?post_type=oli_contact_form&page=oli-cf-messages' ) ); ?>">
				<input type="hidden" name="post_type" value="oli_contact_form" />
				<input type="hidden" name="page" value="oli-cf-messages" />
				<?php
				$list_table->search_box( __( 'Search messages', 'oli-contact-form' ), 'oli-cf-messages' );
				$list_table->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render a single message.
	 *
	 * @param int $id Message ID.
	 */
	private function render_detail( $id ) {
		$row = Oli_CF_Message_Store::get( $id );

		if ( ! $row ) {
			echo '<div class="wrap"><h1>' . esc_html__( 'Message', 'oli-contact-form' ) . '</h1>';
			echo '<p>' . esc_html__( 'This message could not be found.', 'oli-contact-form' ) . '</p></div>';
			return;
		}

		$fields = array();
		if ( ! empty( $row['fields'] ) ) {
			$decoded = json_decode( $row['fields'], true );
			if ( is_array( $decoded ) ) {
				$fields = $decoded;
			}
		}

		$gmt  = isset( $row['created_gmt'] ) ? $row['created_gmt'] : '';
		$date = $gmt ? get_date_from_gmt( $gmt, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) : '';

		$back_url = add_query_arg(
			array(
				'page' => 'oli-cf-messages',
			),
			admin_url( 'edit.php?post_type=oli_contact_form' )
		);

		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'page'    => 'oli-cf-messages',
					'action'  => 'delete',
					'message' => absint( $row['id'] ),
				),
				admin_url( 'edit.php?post_type=oli_contact_form' )
			),
			'oli_cf_delete_message_' . absint( $row['id'] )
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Message', 'oli-contact-form' ); ?></h1>
			<p>
				<a href="<?php echo esc_url( $back_url ); ?>">&larr; <?php esc_html_e( 'Back to messages', 'oli-contact-form' ); ?></a>
				|
				<a href="<?php echo esc_url( $delete_url ); ?>" class="submitdelete"><?php esc_html_e( 'Delete', 'oli-contact-form' ); ?></a>
			</p>

			<table class="widefat striped oli-cf-message-detail">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Date', 'oli-contact-form' ); ?></th>
						<td><?php echo esc_html( $date ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Form', 'oli-contact-form' ); ?></th>
						<td>
							<?php
							echo esc_html( isset( $row['form_title'] ) ? $row['form_title'] : '' );
							if ( ! empty( $row['form_id'] ) ) {
								echo ' <span class="description">(#' . esc_html( absint( $row['form_id'] ) ) . ')</span>';
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Sender', 'oli-contact-form' ); ?></th>
						<td>
							<?php
							echo esc_html( isset( $row['sender_name'] ) ? $row['sender_name'] : '' );
							if ( ! empty( $row['sender_email'] ) ) {
								echo '<br /><a href="' . esc_url( 'mailto:' . $row['sender_email'] ) . '">' . esc_html( $row['sender_email'] ) . '</a>';
							}
							?>
						</td>
					</tr>
					<?php foreach ( $fields as $key => $value ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( (string) $key ); ?></th>
							<td>
								<?php
								if ( is_array( $value ) ) {
									echo esc_html( implode( ', ', $value ) );
								} else {
									echo nl2br( esc_html( (string) $value ) );
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
