# Testing Guide for Oli Contact Form v0.1.0

This document provides comprehensive testing instructions for Oli Contact Form.

## Prerequisites

- WordPress 6.5 or higher (tested up to 7.1)
- PHP 7.4 or higher
- Contact Form 7 plugin (for testing the importer feature)

## Installation Testing

1. **Plugin Activation**
   - Upload plugin to `/wp-content/plugins/oli-contact-form/`
   - Activate via WordPress admin
   - Verify no errors during activation
   - Check that "Oli Contact Forms" menu appears in admin

2. **Default Settings**
   - Navigate to Settings page
   - Verify default settings are saved
   - Confirm no warnings/errors

## Form Creation Testing

### Basic Form Creation

1. **Create a Simple Form**
   - Go to "Oli Contact Forms" > "Add New"
   - Title: "Contact Form Test"
   - Use default template (should include name, email, subject, message, optional consent, submit)
   - Publish the form
   - Note the form ID for shortcode testing

2. **Tag Generator Testing**
   - Click each tag generator button:
     - text, email, tel, url, number, date
     - textarea
     - select, checkbox, radio
     - acceptance
     - file
     - submit
   - Verify each button prompts for appropriate options
   - Confirm tags are inserted into the template

3. **Required Fields**
   - Create fields with asterisk: `[text* your-name]`
   - Create fields without asterisk: `[text your-company]`
   - Verify required fields are marked in the form

### Field Type Testing

Test each field type individually:

1. **Text Field**: `[text* your-name placeholder:"Your name"]`
2. **Email Field**: `[email* your-email]`
3. **Tel Field**: `[tel your-phone]`
4. **URL Field**: `[url your-website]`
5. **Number Field**: `[number your-age]`
6. **Date Field**: `[date appointment-date]`
7. **Textarea**: `[textarea your-message rows:5]`
8. **Select**: `[select* topic "General Inquiry" "Support" "Sales"]`
9. **Checkbox**: `[checkbox interests "Web Design" "SEO" "Marketing"]`
10. **Radio**: `[radio* contact-method "Email" "Phone"]`
11. **Acceptance**: `[acceptance terms "I accept the terms and conditions"]`
12. **File Upload**: `[file your-file limit:2mb filetypes:jpg|png|pdf]`

## Mail Configuration Testing

### Main Email (Mail Tab)

1. **Configure Mail Settings**
   - To: Your email address
   - From: site admin email (not `[your-email]`, to avoid SPF/DMARC failures)
   - Subject: `New message from [your-name]`
   - Body: Include all field placeholders
   - Reply-To: `[your-email]`
   - Save form

2. **Test Email Sending**
   - Submit form with valid data
   - Check email received at configured address
   - Verify all placeholders are replaced with actual values
   - Confirm Reply-To header works

### Autoresponder (Mail 2 Tab)

1. **Enable Mail 2**
   - Check "Enable autoresponder"
   - To: `[your-email]`
   - From: Your site email
   - Subject: "Thank you for contacting us"
   - Body: Custom thank you message
   - Save form

2. **Test Autoresponder**
   - Submit form
   - Verify submitter receives autoresponder email
   - Confirm both emails are sent (main + autoresponder)

## Frontend Display Testing

### Shortcode Testing

1. **Classic Editor**
   - Create new page
   - Insert shortcode: `[oli_contact_form id="123"]`
   - Publish and view page
   - Verify form displays correctly

2. **Block Editor**
   - Create new page in block editor
   - Add "Oli Contact Form" block
   - Select form from dropdown
   - Publish and view page
   - Verify form displays correctly

### Form Submission Testing

1. **AJAX Submission (JavaScript Enabled)**
   - Fill out form completely
   - Submit form
   - Verify page doesn't reload
   - Check for success message
   - Confirm form resets after successful submission

2. **No-JS Fallback**
   - Disable JavaScript in browser
   - Submit form
   - Verify page redirects with success parameter
   - Confirm success message displays

## Validation Testing

### Server-Side Validation

1. **Required Fields**
   - Leave required field empty
   - Submit form
   - Verify error message appears
   - Check field is highlighted

2. **Email Validation**
   - Enter invalid email (e.g., "notanemail")
   - Submit form
   - Verify email validation error

3. **URL Validation**
   - Enter invalid URL in url field
   - Submit form
   - Verify URL validation error

4. **Number Validation**
   - Enter text in number field
   - Submit form
   - Verify number validation error

5. **File Upload Validation**
   - Upload file exceeding size limit
   - Verify size error message
   - Upload disallowed file type
   - Verify file type error message

### Client-Side Validation

1. **Inline Validation**
   - Enable JavaScript
   - Fill out required field
   - Tab away (blur event)
   - Verify invalid fields are highlighted
   - Correct the field
   - Verify highlight is removed

2. **Real-Time Feedback**
   - Enter invalid email
   - Tab away
   - Check for visual feedback (border color change)

## Anti-Spam Testing

### Built-in Protection

1. **Honeypot Test**
   - Inspect form HTML
   - Find hidden honeypot field (`oli_cf_hp`)
   - Use browser console to fill honeypot field
   - Submit form
   - Verify submission is blocked as spam

2. **Time Trap Test**
   - Load form
   - Immediately submit (within 3 seconds)
   - Verify submission is blocked

### Google reCAPTCHA v3

1. **Enable reCAPTCHA**
   - Get reCAPTCHA keys from Google
   - Go to Settings > Anti-spam Protection
   - Enable reCAPTCHA
   - Enter site key and secret key
   - Save settings

2. **Test reCAPTCHA**
   - Submit form
   - Verify reCAPTCHA script is loaded
   - Check form submission includes reCAPTCHA token
   - Confirm submission is validated

### Cloudflare Turnstile

1. **Enable Turnstile**
   - Get Turnstile keys from Cloudflare
   - Go to Settings
   - Enable Turnstile
   - Enter site key and secret key
   - Save settings

2. **Test Turnstile**
   - Submit form
   - Verify Turnstile challenge appears
   - Complete challenge
   - Confirm submission is validated

## CF7 Importer Testing

### Prepare Test Data

1. **Install Contact Form 7**
2. **Create Test Forms in CF7**
   - Simple contact form
   - Form with file upload
   - Form with mail 2 enabled
   - Form with custom messages

### Import Testing

1. **Access Importer**
   - Go to "Oli Contact Forms" > "Import from CF7"
   - Verify CF7 forms are listed

2. **Import Forms**
   - Select forms to import
   - Click "Import Selected Forms"
   - Verify import success message
   - Check imported forms appear in forms list

3. **Verify Imported Data**
   - Open imported form
   - Check form template is preserved
   - Verify mail settings are correct
   - Confirm mail 2 settings (if enabled)
   - Check custom messages are imported

### CF7 Shortcode Compatibility

1. **Enable CF7 Compatibility**
   - Go to Settings > CF7 Compatibility
   - Enable "CF7 Shortcode Mapping"
   - Save settings

2. **Test CF7 Shortcode**
   - Create page with CF7 shortcode: `[contact-form-7 id="456"]`
   - Verify Oli Contact Form renders instead of CF7
   - Test form submission works

## Accessibility Testing

1. **Keyboard Navigation**
   - Use Tab key to navigate through form
   - Verify all fields are reachable
   - Check submit button can be activated with Enter/Space

2. **Screen Reader Testing**
   - Use screen reader (NVDA, JAWS, or VoiceOver)
   - Verify labels are read correctly
   - Check required fields are announced
   - Confirm error messages are read
   - Test aria-live region announces success/error messages

3. **ARIA Attributes**
   - Inspect HTML
   - Verify `aria-required="true"` on required fields
   - Check `aria-invalid` changes on validation
   - Confirm `aria-live` on response output

## Internationalization Testing

1. **Translation Plugin Testing**
   - Install TranslatePress or WPML
   - Translate form strings
   - Verify translated strings appear on frontend
   - Test form submission with translated strings

2. **POT File Check**
   - Open `languages/oli-contact-form.pot`
   - Verify all user-facing strings are included
   - Check context is provided where needed

## Theme Compatibility Testing

Test with various popular themes:

1. **Twenty Twenty-Four**
2. **Astra**
3. **GeneratePress**
4. **OceanWP**

For each theme:
- Install and activate theme
- Display form on page
- Verify styling is acceptable
- Test form submission
- Check responsive design

## Responsive Testing

Test on various screen sizes:

1. **Desktop** (1920x1080, 1366x768)
2. **Tablet** (768x1024)
3. **Mobile** (375x667, 414x896)

For each size:
- Verify form layout
- Check field sizing
- Test touch interactions (mobile)
- Confirm buttons are easily tappable

## Plugin Check Testing

1. **Install Plugin Check Plugin**
   ```
   wp plugin install plugin-check --activate
   ```

2. **Run Plugin Check**
   - Go to Tools > Plugin Check
   - Select "Oli Contact Form"
   - Run all checks
   - Verify no critical issues

## PHPCS Testing

Run WordPress Coding Standards check:

```bash
phpcs --standard=WordPress --extensions=php --ignore=vendor/ .
```

Expected result: No errors or warnings

## Performance Testing

1. **Page Load Time**
   - Measure page load with form
   - Should add minimal overhead (<100ms)

2. **AJAX Response Time**
   - Submit form
   - Measure time from submit to response
   - Should be under 2 seconds (depends on mail server)

3. **Asset Loading**
   - Check Network tab in browser dev tools
   - Verify only necessary scripts/styles load
   - Confirm reCAPTCHA/Turnstile only load when enabled

## Security Testing

1. **Nonce Verification**
   - Attempt to submit form without nonce
   - Verify submission is blocked

2. **Capability Checks**
   - Log out
   - Attempt to access admin pages directly
   - Verify access is denied

3. **SQL Injection**
   - Test with SQL injection strings in form fields
   - Verify data is properly sanitized

4. **XSS Testing**
   - Submit form with script tags in fields
   - Check email received has properly escaped content
   - Verify no scripts execute

## Known Issues / Limitations

Document any issues found during testing:

- [ ] File uploads larger than PHP's upload_max_filesize fail silently
- [ ] Phone number validation is basic (not country-specific)
- [ ] No submission database storage (email only)
- [ ] CF7 advanced features not supported (conditional logic, etc.)

## Test Checklist Summary

- [ ] Plugin activates without errors
- [ ] Form creation works with tag generator
- [ ] All field types render correctly
- [ ] Server-side validation works
- [ ] Client-side validation provides feedback
- [ ] AJAX submission works
- [ ] No-JS fallback works
- [ ] Email sending works (main + mail 2)
- [ ] File uploads attach to email
- [ ] Anti-spam (honeypot + time trap) works
- [ ] reCAPTCHA integration works (if enabled)
- [ ] Turnstile integration works (if enabled)
- [ ] Shortcode displays form
- [ ] Gutenberg block works
- [ ] CF7 importer converts forms correctly
- [ ] CF7 shortcode compatibility works (if enabled)
- [ ] Accessibility features work (keyboard, screen reader)
- [ ] Responsive design works on all screen sizes
- [ ] Plugin Check passes
- [ ] PHPCS WordPress standard passes
- [ ] No PHP errors in debug log
- [ ] Works with popular themes
- [ ] Translation ready (.pot file present)

## Reporting Issues

When reporting issues, include:
- WordPress version
- PHP version
- Active theme
- Active plugins
- Steps to reproduce
- Expected vs actual behavior
- Screenshots/error messages
