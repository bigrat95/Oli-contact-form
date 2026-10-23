# Changelog

All notable changes to Oli Contact Form will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.1] - 2026-10-21

### Fixed
- Add `ABSPATH` direct-access guards to every plugin PHP file (Plugin Check).
- Keep official reCAPTCHA and Turnstile CDN/API URLs (they cannot be self-hosted). Load client scripts only when the service is enabled and the page has a form; document the exception in `readme.txt` and `REVIEW-NOTES.md`.

### Changed
- Default notification `From` is the site admin email. `[your-email]` stays in `Reply-To` to avoid SPF/DMARC failures.
- Default form template includes an optional consent checkbox (`[acceptance consent …]` without `*`).
- Distribution ZIP excludes development files via `.distignore` and `.gitattributes` `export-ignore`.

## [0.2.0] - 2026-10-21

### Added
- Optional **Keep a copy of messages** setting (disabled by default).
- `{prefix}oli_cf_messages` table for successful submissions.
- Messages admin screen (`WP_List_Table`): date, form, sender, preview, detail view.
- Single and bulk delete (`manage_options`, nonces).
- Retention period in days (default 30, `0` = unlimited) with a daily WP-Cron purge.
- Privacy exporters/erasers by email and suggested policy text via `wp_add_privacy_policy_content`.
- `uninstall.php` drops the table, options, cron, and contact form posts.

## [0.1.1] - 2026-10-21

### Changed
- Document Google reCAPTCHA v3 and Cloudflare Turnstile in `readme.txt` under `== External services ==` (when they run, data sent, terms and privacy links).
- Extract the CF7 importer "select all" inline script into `assets/js/oli-cf-importer.js` and enqueue it with `wp_enqueue_script`.
- Move the honeypot off-screen style from an inline `style` attribute into `assets/css/oli-cf-public.css`.
- Prefix captcha script handles as `oli-cf-recaptcha` and `oli-cf-turnstile`.
- Enqueue public CSS/JS and captcha scripts only when a form shortcode or block is present.
- Sign non-AJAX redirect status query args with `oli_cf_status_nonce`.
- Pin the GitHub Actions workflow to `squizlabs/php_codesniffer` 3.x so WPCS 3 can install.

## [0.1.0] - 2026-10-19

### Added
- Initial release of Oli Contact Form
- Custom post type for managing contact forms
- CF7-like tag syntax for building forms
- Tag generator buttons for all field types
- Support for field types:
  - text, email, tel, url, number, date
  - textarea
  - select, checkbox, radio
  - acceptance
  - file upload with size and type restrictions
  - submit button
- Server-side validation with comprehensive error checking
- Client-side inline validation for better UX
- AJAX form submission with loading indicator
- Graceful no-JavaScript fallback
- Main notification email configuration
- Optional autoresponder (Mail 2) support
- Field placeholders in email templates using [field-name] syntax
- Customizable success, error, and validation messages
- Built-in anti-spam protection:
  - Honeypot field
  - Time trap (minimum submission time)
- Optional Google reCAPTCHA v3 integration
- Optional Cloudflare Turnstile integration
- Settings page for anti-spam API keys
- Shortcode `[oli_contact_form id="..."]`
- Gutenberg block for visual form selection
- Contact Form 7 importer:
  - Converts form templates
  - Migrates mail settings (main + mail 2)
  - Preserves custom messages
- Optional CF7 shortcode compatibility mode
- Action hooks: `oli_cf_before_validation`, `oli_cf_before_send`, `oli_cf_after_send`
- Minimal, themeable CSS
- Accessibility features:
  - Proper label associations
  - ARIA attributes (aria-invalid, aria-required)
  - Live regions for form feedback (aria-live)
- Full internationalization support
- Translation template (.pot file)
- WordPress Plugin Check compliant
- PHPCS WordPress coding standards

### Known Limitations
- Submission copies stay off unless an administrator enables them
- File uploads are attached to email then deleted (not stored permanently)
- Basic phone number validation (format not country-specific)
- CF7 advanced features not supported (conditional logic, dynamic dropdowns, etc.)

## [Unreleased]

### Planned for Future Releases
- Export submissions to CSV
- More advanced conditional logic
- Email template editor with preview
- Additional field types (hidden, range, color)
- Multi-step forms
- Spam submission logging
- Webhook integration
- More anti-spam options (hCaptcha, etc.)
