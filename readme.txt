=== Oli Contact Form ===
Contributors: bigrat95
Tags: contact form, form, email, ajax, contact
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple, accessible contact form plugin with CF7-like tag syntax and built-in anti-spam protection.

== Description ==

Oli Contact Form is a deliberately simple alternative to Contact Form 7, designed to be lightweight and easy to use while maintaining all the essential features you need for contact forms.

**Key Features:**

* **CF7-like Tag Syntax** - Familiar tag-based form builder for easy form creation
* **Tag Generator** - Visual buttons to insert field tags without memorizing syntax
* **Multiple Field Types** - text, email, tel, url, number, date, textarea, select, checkbox, radio, acceptance, file upload
* **File Uploads** - With configurable size and type restrictions, attached to emails
* **Server-side & Client-side Validation** - Required fields and type validation
* **AJAX Submission** - Smooth form submission with graceful no-JavaScript fallback
* **Dual Email Support** - Main notification email + optional autoresponder
* **Built-in Anti-spam** - Honeypot and time trap protection included by default
* **Optional Anti-spam Services** - Google reCAPTCHA v3 and Cloudflare Turnstile support
* **Gutenberg Block** - Easy form insertion with the block editor
* **Shortcode Support** - `[oli_contact_form id="123"]` for classic editor
* **Accessibility First** - Proper labels, ARIA attributes, and live regions
* **Multilingual Ready** - Works with TranslatePress, WPML, and other translation plugins
* **CF7 Importer** - Convert existing Contact Form 7 forms with one click
* **CF7 Shortcode Compatibility** - Optional mapping so `[contact-form-7]` shortcodes work after migration
* **No Tracking** - Respects user privacy, no data sent to third parties unless you enable anti-spam services
* **Themeable CSS** - Minimal styling that adapts to your theme

Google reCAPTCHA v3 and Cloudflare Turnstile stay off until an administrator enables them and enters API keys. See **External services** below.

== External services ==

This plugin does not call any third-party API by default. The built-in honeypot and time trap run entirely on your WordPress site. The following services are used only after an administrator enables them under Oli Contact Forms → Settings.

= Google reCAPTCHA v3 =

When **Enable Google reCAPTCHA v3** is checked and a site key is saved, visitors who view a form load Google's reCAPTCHA script so a token can be generated. On submit, WordPress verifies that token with Google.

* When it runs: only if reCAPTCHA is enabled; the script is enqueued on the public site, and verification runs on each submission.
* Client script: `https://www.google.com/recaptcha/api.js` (with your site key as the `render` parameter).
* Server endpoint: `https://www.google.com/recaptcha/api/siteverify` via `wp_remote_post`.
* Data sent: the visitor's reCAPTCHA response token, your reCAPTCHA secret key, and the visitor's IP address (`remoteip`). Google may also collect device and interaction data as described in their privacy policy.
* Service: [https://www.google.com/recaptcha/](https://www.google.com/recaptcha/)
* Terms: [https://policies.google.com/terms](https://policies.google.com/terms)
* Privacy: [https://policies.google.com/privacy](https://policies.google.com/privacy)

= Cloudflare Turnstile =

When **Enable Cloudflare Turnstile** is checked and a site key is saved, visitors who view a form load Cloudflare's Turnstile widget. On submit, WordPress verifies the widget token with Cloudflare.

* When it runs: only if Turnstile is enabled; the script is enqueued on the public site, and verification runs on each submission.
* Client script: `https://challenges.cloudflare.com/turnstile/v0/api.js`
* Server endpoint: `https://challenges.cloudflare.com/turnstile/v0/siteverify` via `wp_remote_post`.
* Data sent: the visitor's Turnstile response token, your Turnstile secret key, and the visitor's IP address (`remoteip`). Cloudflare may also collect device and challenge data as described in their privacy policy.
* Service: [https://www.cloudflare.com/products/turnstile/](https://www.cloudflare.com/products/turnstile/)
* Terms: [https://www.cloudflare.com/website-terms/](https://www.cloudflare.com/website-terms/)
* Privacy: [https://www.cloudflare.com/privacypolicy/](https://www.cloudflare.com/privacypolicy/)

Secret keys are stored in the WordPress options table and are never printed in storefront HTML or JavaScript.

== Installation ==

1. Upload the `oli-contact-form` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Oli Contact Forms' in the admin menu to create your first form
4. Use the shortcode `[oli_contact_form id="123"]` or the Gutenberg block to display your form

== Frequently Asked Questions ==

= How do I create a form? =

Go to 'Oli Contact Forms' > 'Add New' in your WordPress admin. Use the tag generator buttons to add fields, or write tags directly using CF7-like syntax (e.g., `[text* your-name]` for a required text field).

= What field types are supported? =

Text, email, tel, url, number, date, textarea, select, checkbox, radio, acceptance, file upload, and submit button.

= Can I import my Contact Form 7 forms? =

Yes! Go to 'Oli Contact Forms' > 'Import from CF7' to convert your existing CF7 forms.

= Does it work without JavaScript? =

Yes. While the plugin uses AJAX for a better user experience, it has a graceful fallback for users without JavaScript.

= Is it compatible with page builders? =

Yes. Use the shortcode `[oli_contact_form id="123"]` in any page builder that supports WordPress shortcodes.

= How do I enable anti-spam protection? =

The plugin includes honeypot and time trap protection by default. For additional protection, go to 'Oli Contact Forms' > 'Settings' to enable Google reCAPTCHA v3 or Cloudflare Turnstile.

= Where are form submissions stored? =

Version 0.1.0 does not store submissions in the database. All submissions are sent via email using WordPress's `wp_mail()` function.

= Can I customize the styling? =

Yes. The plugin includes minimal CSS that you can override in your theme. All form elements have CSS classes prefixed with `oli-cf-`.

== Screenshots ==

1. Form editor with tag generator buttons
2. Mail settings configuration
3. Form displayed on the frontend
4. Gutenberg block settings
5. CF7 importer

== Changelog ==

= 0.1.1 =
* Document Google reCAPTCHA v3 and Cloudflare Turnstile under External services (what, when, data sent, terms and privacy).
* Move the CF7 importer "select all" script into an enqueued file (no inline JavaScript).

= 0.1.0 =
* Initial release
* CF7-like tag syntax with visual tag generator
* All essential field types (text, email, tel, url, number, date, textarea, select, checkbox, radio, acceptance, file)
* Server-side and client-side validation
* AJAX submission with graceful fallback
* Main email + optional autoresponder (Mail 2)
* Built-in honeypot and time trap anti-spam
* Optional Google reCAPTCHA v3 support
* Optional Cloudflare Turnstile support
* Gutenberg block and shortcode
* Contact Form 7 importer
* Optional CF7 shortcode compatibility mode
* Accessible and multilingual ready

== Upgrade Notice ==

= 0.1.1 =
Documents optional Google reCAPTCHA and Cloudflare Turnstile calls and removes inline admin JavaScript.

= 0.1.0 =
Initial release of Oli Contact Form.
