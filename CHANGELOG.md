# Changelog

All notable changes to Oli Contact Form will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
- No submission database storage (email only)
- File uploads are attached to email then deleted (not stored permanently)
- Basic phone number validation (format not country-specific)
- CF7 advanced features not supported (conditional logic, dynamic dropdowns, etc.)

## [Unreleased]

### Planned for Future Releases
- Optional submission database storage with admin viewing
- Export submissions to CSV
- More advanced conditional logic
- Email template editor with preview
- Additional field types (hidden, range, color)
- Multi-step forms
- Spam submission logging
- Webhook integration
- More anti-spam options (hCaptcha, etc.)
