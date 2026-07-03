<?php

namespace VRED_Linked_Swatches;

if (! defined('ABSPATH')) {
	exit;
}

/** WooCommerce product loop swatches */
final class Loop_Swatches {
	public static function boot() : void {
		add_action('woocommerce_after_shop_loop_item_title', [self::class, 'render'], 12);
	}

	public static function render() : void {
		$product = Swatch_Data::get_current_product();

		if (! $product instanceof \WC_Product) {
			return;
		}

		$show = apply_filters('vred_linked_swatches_show_loop_swatches', false, $product);

		if (! $show) {
			return;
		}

		$args = apply_filters('vred_linked_swatches_loop_swatches_args', [], $product);

		if (! is_array($args)) {
			$args = [];
		}

		$markup = Renderer::render_loop_swatches($product, $args);

		if ($markup === '') {
			return;
		}

		Assets::enqueue_frontend_style();

		echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
