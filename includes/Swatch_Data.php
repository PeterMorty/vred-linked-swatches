<?php

namespace VRED_Linked_Swatches;

if (! defined('ABSPATH')) {
	exit;
}

/** Product metadata access */
final class Swatch_Data {
	public const META_NAME = '_vred_linked_swatches_name';
	public const META_COLOR = '_vred_linked_swatches_color';
	public const META_SECONDARY_COLOR = '_vred_linked_swatches_secondary_color';
	public const META_IMAGE_ID = '_vred_linked_swatches_image_id';
	public const META_IDS = '_vred_linked_swatches_ids';

	public static function get_current_product($product_id = 0) {
		$product_id = absint($product_id);

		if ($product_id > 0 && function_exists('wc_get_product')) {
			$product = wc_get_product($product_id);

			if ($product instanceof \WC_Product) {
				return $product;
			}
		}

		global $product;

		if ($product instanceof \WC_Product) {
			return $product;
		}

		$current_id = get_the_ID();

		if ($current_id && function_exists('wc_get_product')) {
			$current_product = wc_get_product($current_id);

			if ($current_product instanceof \WC_Product) {
				return $current_product;
			}
		}

		return null;
	}

	public static function get_product_option_data(\WC_Product $product) : array {
		$name = trim((string) $product->get_meta(self::META_NAME, true));
		$color = self::sanitize_color((string) $product->get_meta(self::META_COLOR, true));
		$secondary_color = self::sanitize_color((string) $product->get_meta(self::META_SECONDARY_COLOR, true));
		$image_id = absint($product->get_meta(self::META_IMAGE_ID, true));
		$image_url = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
		$product_image_id = $product->get_image_id();
		$product_image_url = $product_image_id > 0 ? wp_get_attachment_image_url($product_image_id, 'woocommerce_thumbnail') : '';

		if (! is_string($image_url)) {
			$image_url = '';
		}

		if (! is_string($product_image_url)) {
			$product_image_url = '';
		}

		if ($product_image_url === '' && function_exists('wc_placeholder_img_src')) {
			$product_image_url = wc_placeholder_img_src('woocommerce_thumbnail');
		}

		return [
			'name' => $name !== '' ? $name : $product->get_name(),
			'color' => $color,
			'secondary_color' => $secondary_color,
			'image_id' => $image_id,
			'image_url' => is_string($image_url) ? $image_url : '',
			'product_image_url' => is_string($product_image_url) ? $product_image_url : '',
		];
	}

	public static function get_linked_product_ids(\WC_Product $product) : array {
		$ids = $product->get_meta(self::META_IDS, true);

		if (! is_array($ids)) {
			$ids = [];
		}

		$ids = array_values(array_unique(array_filter(array_map('absint', $ids))));
		$current_id = $product->get_id();

		return array_values(array_filter($ids, static function (int $id) use ($current_id) : bool {
			return $id > 0 && $id !== $current_id;
		}));
	}

	public static function get_option_products(\WC_Product $product, bool $include_current = true) : array {
		$ids = self::get_linked_product_ids($product);

		if ($include_current) {
			array_unshift($ids, $product->get_id());
		}

		$ids = array_values(array_unique(array_filter($ids)));
		$products = [];

		foreach ($ids as $id) {
			$linked_product = wc_get_product($id);

			if (! $linked_product instanceof \WC_Product) {
				continue;
			}

			if ($linked_product->get_status() !== 'publish' && (int) $linked_product->get_id() !== (int) $product->get_id()) {
				continue;
			}

			$products[] = $linked_product;
		}

		return $products;
	}

	public static function sanitize_color(string $color) : string {
		$color = trim($color);

		if ($color === '') {
			return '';
		}

		$sanitized = sanitize_hex_color($color);

		return is_string($sanitized) ? $sanitized : '';
	}
}
