<?php

namespace VRED_Linked_Swatches;

if (! defined('ABSPATH')) {
	exit;
}

/** Asset registration and scoped loading */
final class Assets {
	private static $frontend_style_enqueued = false;

	public static function boot() : void {
		add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin']);
	}

	public static function register_frontend() : void {
		wp_register_style(
			'vred-linked-swatches-frontend',
			VRED_LINKED_SWATCHES_URL . 'assets/css/frontend.css',
			[],
			VRED_LINKED_SWATCHES_VERSION
		);
	}

	public static function enqueue_frontend() : void {
		self::enqueue_frontend_style();
	}

	public static function enqueue_frontend_style() : void {
		if (self::$frontend_style_enqueued) {
			return;
		}

		self::$frontend_style_enqueued = true;
		self::register_frontend();
		wp_enqueue_style('vred-linked-swatches-frontend');
	}

	public static function enqueue_vred_panel_script() : void {
		if (function_exists('vred_elements_register_panel_script')) {
			wp_enqueue_script(vred_elements_register_panel_script());
		}
	}

	public static function enqueue_admin(string $hook) : void {
		if (! in_array($hook, ['post.php', 'post-new.php'], true)) {
			return;
		}

		$screen = function_exists('get_current_screen') ? get_current_screen() : null;

		if (! $screen || $screen->post_type !== 'product') {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style('woocommerce_admin_styles');
		wp_enqueue_style(
			'vred-linked-swatches-admin',
			VRED_LINKED_SWATCHES_URL . 'assets/css/admin.css',
			[],
			VRED_LINKED_SWATCHES_VERSION
		);

		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('wc-enhanced-select');
		wp_enqueue_script(
			'vred-linked-swatches-admin',
			VRED_LINKED_SWATCHES_URL . 'assets/js/admin.js',
			['jquery', 'wp-color-picker', 'wc-enhanced-select'],
			VRED_LINKED_SWATCHES_VERSION,
			true
		);
		wp_localize_script(
			'vred-linked-swatches-admin',
			'vredLinkedSwatchesAdmin',
			[
				'selectImageTitle' => __('Select texture image', 'vred-linked-swatches'),
				'useImageButton' => __('Use image', 'vred-linked-swatches'),
			]
		);
		wp_script_add_data('vred-linked-swatches-admin', 'strategy', 'defer');
	}

}