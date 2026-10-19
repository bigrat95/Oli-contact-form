/**
 * Gutenberg block for Oli Contact Form
 */

(function(wp) {
	var registerBlockType = wp.blocks.registerBlockType;
	var SelectControl = wp.components.SelectControl;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var ServerSideRender = wp.serverSideRender;
	var el = wp.element.createElement;
	var __ = wp.i18n.__;

	registerBlockType('oli-cf/contact-form', {
		title: __('Oli Contact Form', 'oli-contact-form'),
		description: __('Display a contact form', 'oli-contact-form'),
		icon: 'email',
		category: 'widgets',
		attributes: {
			formId: {
				type: 'number',
				default: 0
			}
		},
		edit: function(props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			var formOptions = [
				{ value: 0, label: __('-- Select a form --', 'oli-contact-form') }
			];

			if (oliCfBlock.forms && oliCfBlock.forms.length > 0) {
				oliCfBlock.forms.forEach(function(form) {
					formOptions.push({
						value: form.value,
						label: form.label
					});
				});
			}

			var inspectorControls = el(
				InspectorControls,
				{},
				el(
					PanelBody,
					{ title: __('Form Settings', 'oli-contact-form'), initialOpen: true },
					el(SelectControl, {
						label: __('Select Form', 'oli-contact-form'),
						value: attributes.formId,
						options: formOptions,
						onChange: function(value) {
							setAttributes({ formId: parseInt(value) });
						}
					})
				)
			);

			var blockContent;

			if (attributes.formId === 0) {
				blockContent = el(
					'div',
					{ className: 'oli-cf-block-placeholder' },
					el('p', {}, __('Please select a contact form from the block settings.', 'oli-contact-form'))
				);
			} else {
				blockContent = el(ServerSideRender, {
					block: 'oli-cf/contact-form',
					attributes: attributes
				});
			}

			return [
				inspectorControls,
				blockContent
			];
		},
		save: function() {
			// Server-side rendering
			return null;
		}
	});

})(window.wp);
