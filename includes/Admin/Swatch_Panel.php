<?php

namespace VRED_Linked_Swatches\Admin;

use VRED_Linked_Swatches\Plugin;
use VRED_Linked_Swatches\Swatch_Data;

if (! defined('ABSPATH')) {
	exit;
}

/** WooCommerce product data panel */
final class Swatch_Panel {
	public static function boot() : void {
		add_filter('woocommerce_product_data_tabs', [self::class, 'add_tab']);
		add_action('woocommerce_product_data_panels', [self::class, 'render_panel']);
		add_action('woocommerce_admin_process_product_object', [self::class, 'save_product']);
	}

	public static function add_tab(array $tabs) : array {
		if (! Plugin::has_woocommerce()) {
			return $tabs;
		}

		$tabs['vred_linked_swatches'] = [
			'label' => __('Linked Swatches', 'vred-linked-swatches'),
			'target' => 'vred_linked_swatches_data',
			'class' => [],
			'priority' => 65,
		];

		return $tabs;
	}

	public static function render_panel() : void {
		global $post;

		if (! $post instanceof \WP_Post) {
			return;
		}

		$product = wc_get_product($post->ID);

		if (! $product instanceof \WC_Product) {
			return;
		}

		$linked_ids = Swatch_Data::get_linked_product_ids($product);
		$image_id = absint($product->get_meta(Swatch_Data::META_IMAGE_ID, true));
		$image_url = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
		?>
		<div id="vred_linked_swatches_data" class="panel woocommerce_options_panel hidden">
			<div class="options_group vred-linked-swatches-admin-panel">
				<p class="form-field vred-linked-swatches-product-field">
					<label for="<?php echo esc_attr(Swatch_Data::META_IDS); ?>"><?php esc_html_e('Linked products', 'vred-linked-swatches'); ?></label>
					<select
						id="<?php echo esc_attr(Swatch_Data::META_IDS); ?>"
						class="wc-product-search"
						name="<?php echo esc_attr(Swatch_Data::META_IDS); ?>[]"
						multiple="multiple"
						style="width: 50%;"
						data-placeholder="<?php echo esc_attr__('Search products...', 'vred-linked-swatches'); ?>"
						data-action="woocommerce_json_search_products"
						data-exclude="<?php echo esc_attr($product->get_id()); ?>"
					>
						<?php foreach ($linked_ids as $linked_id) : ?>
							<?php $linked_product = wc_get_product($linked_id); ?>
							<?php if ($linked_product instanceof \WC_Product) : ?>
								<option value="<?php echo esc_attr($linked_product->get_id()); ?>" selected="selected"><?php echo esc_html($linked_product->get_formatted_name()); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</p>

				<?php
				woocommerce_wp_text_input([
					'id' => Swatch_Data::META_NAME,
					'label' => __('Option name', 'vred-linked-swatches'),
					'placeholder' => '',
					'desc_tip' => true,
					'description' => __('Visible name for this option. Example: Brown, Oak or Matte. If empty, the product name is used.', 'vred-linked-swatches'),
				]);

				?>

				<p class="form-field vred-linked-swatches-color-field">
					<label for="<?php echo esc_attr(Swatch_Data::META_COLOR); ?>"><?php esc_html_e('Primary color', 'vred-linked-swatches'); ?></label>
					<input id="<?php echo esc_attr(Swatch_Data::META_COLOR); ?>" class="vred-linked-swatches-color-picker" type="text" name="<?php echo esc_attr(Swatch_Data::META_COLOR); ?>" value="<?php echo esc_attr(Swatch_Data::sanitize_color((string) $product->get_meta(Swatch_Data::META_COLOR, true))); ?>" data-default-color="">
				</p>

				<p class="form-field vred-linked-swatches-color-field">
					<label for="<?php echo esc_attr(Swatch_Data::META_SECONDARY_COLOR); ?>"><?php esc_html_e('Secondary color', 'vred-linked-swatches'); ?></label>
					<input id="<?php echo esc_attr(Swatch_Data::META_SECONDARY_COLOR); ?>" class="vred-linked-swatches-color-picker" type="text" name="<?php echo esc_attr(Swatch_Data::META_SECONDARY_COLOR); ?>" value="<?php echo esc_attr(Swatch_Data::sanitize_color((string) $product->get_meta(Swatch_Data::META_SECONDARY_COLOR, true))); ?>" data-default-color="">
				</p>

				<p class="form-field vred-linked-swatches-image-field">
					<label for="<?php echo esc_attr(Swatch_Data::META_IMAGE_ID); ?>"><?php esc_html_e('Texture image', 'vred-linked-swatches'); ?></label>
					<input id="<?php echo esc_attr(Swatch_Data::META_IMAGE_ID); ?>" type="hidden" name="<?php echo esc_attr(Swatch_Data::META_IMAGE_ID); ?>" value="<?php echo esc_attr($image_id); ?>" data-vred-linked-swatches-image-id>
					<span class="vred-linked-swatches-image-preview<?php echo $image_url ? '' : ' is-empty'; ?>" data-vred-linked-swatches-image-preview>
						<?php if ($image_url) : ?>
							<img src="<?php echo esc_url($image_url); ?>" alt="">
						<?php endif; ?>
					</span>
					<button type="button" class="button" data-vred-linked-swatches-select-image><?php esc_html_e('Select image', 'vred-linked-swatches'); ?></button>
					<button type="button" class="button<?php echo $image_id > 0 ? '' : ' hidden'; ?>" data-vred-linked-swatches-remove-image><?php esc_html_e('Remove image', 'vred-linked-swatches'); ?></button>
				</p>
			</div>
		</div>
		<?php
	}

	public static function save_product(\WC_Product $product) : void {
		if (! current_user_can('edit_product', $product->get_id())) {
			return;
		}

		$product->update_meta_data(Swatch_Data::META_NAME, self::sanitize_text_field_from_request(Swatch_Data::META_NAME));
		$product->update_meta_data(Swatch_Data::META_COLOR, Swatch_Data::sanitize_color(self::sanitize_text_field_from_request(Swatch_Data::META_COLOR)));
		$product->update_meta_data(Swatch_Data::META_SECONDARY_COLOR, Swatch_Data::sanitize_color(self::sanitize_text_field_from_request(Swatch_Data::META_SECONDARY_COLOR)));
		$product->update_meta_data(Swatch_Data::META_IMAGE_ID, absint(self::sanitize_text_field_from_request(Swatch_Data::META_IMAGE_ID)));
		$product->update_meta_data(Swatch_Data::META_IDS, self::sanitize_ids_from_request(Swatch_Data::META_IDS, $product->get_id()));
	}

	private static function sanitize_text_field_from_request(string $key) : string {
		if (! isset($_POST[$key])) {
			return '';
		}

		$value = wp_unslash($_POST[$key]);

		return is_scalar($value) ? sanitize_text_field((string) $value) : '';
	}

	private static function sanitize_ids_from_request(string $key, int $current_product_id) : array {
		if (! isset($_POST[$key]) || ! is_array($_POST[$key])) {
			return [];
		}

		$ids = array_map('absint', wp_unslash($_POST[$key]));
		$ids = array_values(array_unique(array_filter($ids)));

		return array_values(array_filter($ids, static function (int $id) use ($current_product_id) : bool {
			return $id > 0 && $id !== $current_product_id;
		}));
	}
}
