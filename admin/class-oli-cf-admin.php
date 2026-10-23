<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package Oli_Contact_Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class.
 */
class Oli_CF_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Initialize the class.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		$screen = get_current_screen();

		$extra_screens = array(
			'oli_contact_form_page_oli-cf-settings',
			'oli-contact-forms_page_oli-cf-settings',
			'oli_contact_form_page_oli-cf-messages',
			'oli-contact-forms_page_oli-cf-messages',
		);

		if ( ! $screen || ( 'oli_contact_form' !== $screen->post_type && ! in_array( $screen->id, $extra_screens, true ) ) ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			OLI_CF_PLUGIN_URL . 'assets/css/oli-cf-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( ! $screen || 'oli_contact_form' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			OLI_CF_PLUGIN_URL . 'assets/js/oli-cf-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			$this->plugin_name,
			'oliCfAdmin',
			array(
				'strings' => array(
					'insert_tag' => __( 'Insert Tag', 'oli-contact-form' ),
				),
			)
		);
	}
}
