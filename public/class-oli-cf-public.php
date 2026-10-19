<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package Oli_Contact_Form
 */

/**
 * Public class.
 */
class Oli_CF_Public {

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			OLI_CF_PLUGIN_URL . 'assets/css/oli-cf-public.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			OLI_CF_PLUGIN_URL . 'assets/js/oli-cf-public.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		$settings = get_option( 'oli_cf_settings', array() );

		// Enqueue reCAPTCHA if enabled.
		if ( ! empty( $settings['recaptcha_enabled'] ) && ! empty( $settings['recaptcha_site_key'] ) ) {
			wp_enqueue_script(
				'google-recaptcha',
				'https://www.google.com/recaptcha/api.js?render=' . esc_attr( $settings['recaptcha_site_key'] ),
				array(),
				null,
				true
			);
		}

		// Enqueue Turnstile if enabled.
		if ( ! empty( $settings['turnstile_enabled'] ) && ! empty( $settings['turnstile_site_key'] ) ) {
			wp_enqueue_script(
				'cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js',
				array(),
				null,
				true
			);
		}

		wp_localize_script(
			$this->plugin_name,
			'oliCf',
			array(
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'oli_cf_submit' ),
				'recaptchaSiteKey' => ! empty( $settings['recaptcha_enabled'] ) ? $settings['recaptcha_site_key'] : '',
				'turnstileSiteKey' => ! empty( $settings['turnstile_enabled'] ) ? $settings['turnstile_site_key'] : '',
			)
		);
	}
}
