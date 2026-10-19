/**
 * Public JavaScript for Oli Contact Form
 * Handles AJAX submission, validation, and anti-spam integration
 */

(function($) {
	'use strict';

	var OliContactForm = {
		init: function() {
			this.bindEvents();
			this.initRecaptcha();
		},

		bindEvents: function() {
			var self = this;

			$(document).on('submit', '.oli-cf-form', function(e) {
				e.preventDefault();
				self.handleSubmit($(this));
			});

			// Client-side validation on blur
			$(document).on('blur', '.oli-cf-field', function() {
				self.validateField($(this));
			});
		},

		handleSubmit: function($form) {
			var self = this;
			var formId = $form.find('[name="oli_cf_form_id"]').val();
			var $container = $form.closest('.oli-cf-container');
			var $output = $container.find('.oli-cf-response-output');
			var $submit = $form.find('.oli-cf-submit');

			// Clear previous messages
			$output.empty();

			// Validate all fields
			var isValid = true;
			$form.find('.oli-cf-field').each(function() {
				if (!self.validateField($(this))) {
					isValid = false;
				}
			});

			if (!isValid) {
				$output.html('<div class="oli-cf-response-error" role="alert">' + 
					'One or more fields have an error. Please check and try again.' + 
					'</div>');
				return false;
			}

			// Disable submit button
			$submit.prop('disabled', true).append('<span class="oli-cf-spinner"></span>');

			// Handle reCAPTCHA if enabled
			if (oliCf.recaptchaSiteKey) {
				grecaptcha.ready(function() {
					grecaptcha.execute(oliCf.recaptchaSiteKey, {action: 'submit'}).then(function(token) {
						$form.find('#oli-cf-recaptcha-' + formId).val(token);
						self.submitForm($form, $container, $output, $submit);
					});
				});
			} else {
				self.submitForm($form, $container, $output, $submit);
			}

			return false;
		},

		submitForm: function($form, $container, $output, $submit) {
			var formData = new FormData($form[0]);
			formData.append('action', 'oli_cf_submit_form');
			formData.append('nonce', oliCf.nonce || wp.ajax.settings.nonce);

			$.ajax({
				url: oliCf.ajaxUrl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					$submit.prop('disabled', false).find('.oli-cf-spinner').remove();

					if (response.success) {
						$output.html('<div class="oli-cf-response-success" role="alert">' + 
							response.data.message + 
							'</div>');
						$form[0].reset();
						$form.find('.oli-cf-field').removeClass('oli-cf-invalid').attr('aria-invalid', 'false');
					} else {
						var errorHtml = '<div class="oli-cf-response-error" role="alert">' + 
							response.data.message;

						if (response.data.errors) {
							errorHtml += '<ul class="oli-cf-validation-errors">';
							$.each(response.data.errors, function(field, error) {
								errorHtml += '<li>' + error + '</li>';
								$form.find('[name="' + field + '"]').addClass('oli-cf-invalid').attr('aria-invalid', 'true');
							});
							errorHtml += '</ul>';
						}

						errorHtml += '</div>';
						$output.html(errorHtml);
					}

					// Scroll to message
					$('html, body').animate({
						scrollTop: $container.offset().top - 50
					}, 300);
				},
				error: function() {
					$submit.prop('disabled', false).find('.oli-cf-spinner').remove();
					$output.html('<div class="oli-cf-response-error" role="alert">' + 
						'An unexpected error occurred. Please try again.' + 
						'</div>');
				}
			});
		},

		validateField: function($field) {
			var value = $field.val();
			var type = $field.attr('type') || 'text';
			var required = $field.prop('required');
			var isValid = true;

			// Clear previous error state
			$field.removeClass('oli-cf-invalid').attr('aria-invalid', 'false');

			// Required check
			if (required && (!value || value.length === 0)) {
				isValid = false;
			}

			// Type-specific validation
			if (value && value.length > 0) {
				switch (type) {
					case 'email':
						var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
						if (!emailRegex.test(value)) {
							isValid = false;
						}
						break;

					case 'url':
						var urlRegex = /^https?:\/\/.+/;
						if (!urlRegex.test(value)) {
							isValid = false;
						}
						break;

					case 'tel':
						var telRegex = /^[\d\s\+\-\(\)]+$/;
						if (!telRegex.test(value)) {
							isValid = false;
						}
						break;

					case 'number':
						if (isNaN(value)) {
							isValid = false;
						}
						break;
				}
			}

			if (!isValid) {
				$field.addClass('oli-cf-invalid').attr('aria-invalid', 'true');
			}

			return isValid;
		},

		initRecaptcha: function() {
			// reCAPTCHA is initialized via the Google API script
			// Turnstile is initialized via data attributes
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		OliContactForm.init();
	});

	// Expose to global scope if needed
	window.OliContactForm = OliContactForm;

})(jQuery);
