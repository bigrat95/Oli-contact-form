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
	 * Whether form assets were already enqueued for this request.
	 *
	 * @var bool
	 */
	private static $assets_enqueued = false;

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
	 * Register assets and enqueue them when the queried content contains a form.
	 */
	public function maybe_enqueue_assets() {
		if ( self::page_has_form() ) {
			self::enqueue_form_assets();
		}
	}

	/**
	 * Enqueue public CSS/JS and optional captcha scripts.
	 *
	 * Called from the shortcode/block when a form actually renders, and from
	 * wp_enqueue_scripts when the queried post already contains a form.
	 */
	public static function enqueue_form_assets() {
		if ( self::$assets_enqueued ) {
			return;
		}

		$settings = get_option( 'oli_cf_settings', array() );
		$deps     = array( 'jquery' );

		if ( ! empty( $settings['recaptcha_enabled'] ) && ! empty( $settings['recaptcha_site_key'] ) ) {
			wp_enqueue_script(
				'oli-cf-recaptcha',
				'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $settings['recaptcha_site_key'] ),
				array(),
				'3',
				true
			);
			$deps[] = 'oli-cf-recaptcha';
		}

		if ( ! empty( $settings['turnstile_enabled'] ) && ! empty( $settings['turnstile_site_key'] ) ) {
			wp_enqueue_script(
				'oli-cf-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js',
				array(),
				'v0',
				true
			);
			$deps[] = 'oli-cf-turnstile';
		}

		wp_enqueue_style(
			'oli-cf-public',
			OLI_CF_PLUGIN_URL . 'assets/css/oli-cf-public.css',
			array(),
			OLI_CF_VERSION,
			'all'
		);

		wp_enqueue_script(
			'oli-cf-public',
			OLI_CF_PLUGIN_URL . 'assets/js/oli-cf-public.js',
			$deps,
			OLI_CF_VERSION,
			true
		);

		wp_localize_script(
			'oli-cf-public',
			'oliCf',
			array(
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'oli_cf_submit' ),
				'recaptchaSiteKey' => ! empty( $settings['recaptcha_enabled'] ) ? $settings['recaptcha_site_key'] : '',
				'turnstileSiteKey' => ! empty( $settings['turnstile_enabled'] ) ? $settings['turnstile_site_key'] : '',
			)
		);

		self::$assets_enqueued = true;
	}

	/**
	 * Whether the main queried content contains a form shortcode or block.
	 *
	 * @return bool
	 */
	public static function page_has_form() {
		global $post;

		if ( $post instanceof WP_Post && self::content_has_form( $post->post_content ) ) {
			return true;
		}

		$queried = get_queried_object();
		if ( $queried instanceof WP_Post && self::content_has_form( $queried->post_content ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Whether a content string contains this plugin's shortcode or block.
	 *
	 * @param string $content Post content.
	 * @return bool
	 */
	public static function content_has_form( $content ) {
		if ( ! is_string( $content ) || '' === $content ) {
			return false;
		}

		if ( has_shortcode( $content, 'oli_contact_form' ) ) {
			return true;
		}

		if ( function_exists( 'has_block' ) && has_block( 'oli-cf/contact-form', $content ) ) {
			return true;
		}

		$settings = get_option( 'oli_cf_settings', array() );
		if ( ! empty( $settings['cf7_compat_enabled'] ) && has_shortcode( $content, 'contact-form-7' ) ) {
			return true;
		}

		return false;
	}
}
