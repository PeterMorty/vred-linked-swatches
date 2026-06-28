(function ($) {
	'use strict';

	function initColorPickers() {
		$('.vred-linked-swatches-color-picker').each(function () {
			var $field = $(this);

			if ($field.data('wpWpColorPicker')) {
				return;
			}

			$field.wpColorPicker();
		});
	}

	function getMediaFrame($field) {
		var frame = $field.data('vredLinkedSwatchesMediaFrame');

		if (frame) {
			return frame;
		}

		frame = wp.media({
			title: vredLinkedSwatchesAdmin.selectImageTitle,
			button: {
				text: vredLinkedSwatchesAdmin.useImageButton
			},
			multiple: false
		});

		$field.data('vredLinkedSwatchesMediaFrame', frame);
		return frame;
	}


	function setPreview($field, attachment) {
		var $id = $field.find('[data-vred-linked-swatches-image-id]');
		var $preview = $field.find('[data-vred-linked-swatches-image-preview]');
		var $remove = $field.find('[data-vred-linked-swatches-remove-image]');
		var url = '';

		if (attachment && attachment.id) {
			url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
			$id.val(attachment.id);
			$preview.empty().append($('<img>', {
				src: url,
				alt: ''
			})).removeClass('is-empty');
			$remove.removeClass('hidden');
			return;
		}

		$id.val('');
		$preview.empty().addClass('is-empty');
		$remove.addClass('hidden');
	}

	function bindImageField() {
		$(document).on('click', '[data-vred-linked-swatches-select-image]', function (event) {
			var $field = $(this).closest('.vred-linked-swatches-image-field');
			var frame;

			event.preventDefault();

			if (!window.wp || !wp.media) {
				return;
			}

			frame = getMediaFrame($field);
			frame.off('select').on('select', function () {
				var attachment = frame.state().get('selection').first();

				if (attachment) {
					setPreview($field, attachment.toJSON());
				}
			});
			frame.open();
		});

		$(document).on('click', '[data-vred-linked-swatches-remove-image]', function (event) {
			var $field = $(this).closest('.vred-linked-swatches-image-field');

			event.preventDefault();
			setPreview($field, null);
		});
	}

	$(function () {
		initColorPickers();
		bindImageField();
	});
})(jQuery);
