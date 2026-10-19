/**
 * Admin JavaScript for Oli Contact Form
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Tag generator buttons
		$('.oli-cf-tag-gen').on('click', function(e) {
			e.preventDefault();
			
			var tagType = $(this).data('tag-type');
			var tag = generateTag(tagType);
			
			if (tag) {
				insertAtCursor($('#oli-cf-form-template')[0], tag);
			}
		});

		/**
		 * Generate tag based on type
		 */
		function generateTag(type) {
			var name = prompt('Enter field name (e.g., "your-name"):');
			
			if (!name) {
				return null;
			}

			// Sanitize name
			name = name.toLowerCase().replace(/[^a-z0-9-_]/g, '-');

			var tag = '';
			var required = confirm('Is this field required?');
			var requiredMark = required ? '*' : '';

			switch (type) {
				case 'text':
				case 'email':
				case 'tel':
				case 'url':
				case 'number':
				case 'date':
					tag = '[' + type + requiredMark + ' ' + name + ']';
					break;

				case 'textarea':
					tag = '[textarea' + requiredMark + ' ' + name + ']';
					break;

				case 'select':
					var options = prompt('Enter options (separated by spaces, quote multi-word options):');
					if (!options) return null;
					tag = '[select' + requiredMark + ' ' + name + ' ' + options + ']';
					break;

				case 'checkbox':
				case 'radio':
					var options = prompt('Enter options (separated by spaces, quote multi-word options):');
					if (!options) return null;
					tag = '[' + type + requiredMark + ' ' + name + ' ' + options + ']';
					break;

				case 'acceptance':
					var label = prompt('Enter acceptance text:', 'I accept the terms');
					if (!label) return null;
					tag = '[acceptance ' + name + ' "' + label + '"]';
					break;

				case 'file':
					var limit = prompt('File size limit (e.g., "2mb"):', '5mb');
					var types = prompt('Allowed file types (e.g., "jpg|png|pdf"):', 'jpg|png|pdf');
					tag = '[file' + requiredMark + ' ' + name;
					if (limit) tag += ' limit:' + limit;
					if (types) tag += ' filetypes:' + types;
					tag += ']';
					break;

				case 'submit':
					var label = prompt('Button text:', 'Send');
					if (!label) return null;
					tag = '[submit "' + label + '"]';
					break;

				default:
					tag = '[' + type + ' ' + name + ']';
			}

			return tag;
		}

		/**
		 * Insert text at cursor position
		 */
		function insertAtCursor(field, text) {
			if (field.selectionStart || field.selectionStart === 0) {
				var startPos = field.selectionStart;
				var endPos = field.selectionEnd;
				field.value = field.value.substring(0, startPos) + text + field.value.substring(endPos, field.value.length);
				field.selectionStart = startPos + text.length;
				field.selectionEnd = startPos + text.length;
			} else {
				field.value += text;
			}
			
			field.focus();
		}
	});

})(jQuery);
