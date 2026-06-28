<?php

namespace VRED_Linked_Swatches;

if (! defined('ABSPATH')) {
	exit;
}

/** Main plugin bootstrap */
final class Plugin {
	private static $instance = null;

	public static function instance() : self {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function boot() : void {
		add_action('plugins_loaded', [$this, 'load_textdomain']);
		add_action('before_woocommerce_init', [$this, 'declare_woocommerce_compatibility']);
		add_action('plugins_loaded', [$this, 'load'], 20);
	}

	/** Declare compatibility with optional WooCommerce features */
	public function declare_woocommerce_compatibility() : void {
		if (! class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
			return;
		}

		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			VRED_LINKED_SWATCHES_FILE,
			true
		);
	}

	public function load_textdomain() : void {
		load_plugin_textdomain(
			'vred-linked-swatches',
			false,
			dirname(VRED_LINKED_SWATCHES_BASENAME) . '/languages'
		);
	}

	public function load() : void {
		require_once VRED_LINKED_SWATCHES_PATH . 'includes/Assets.php';
		require_once VRED_LINKED_SWATCHES_PATH . 'includes/Swatch_Data.php';
		require_once VRED_LINKED_SWATCHES_PATH . 'includes/Renderer.php';
		require_once VRED_LINKED_SWATCHES_PATH . 'includes/Admin/Swatch_Panel.php';

		Assets::boot();
		Admin\Swatch_Panel::boot();

		if (defined('VRED_LINKED_SWATCHES_PRIVATE_UPDATER') && VRED_LINKED_SWATCHES_PRIVATE_UPDATER) {
			require_once VRED_LINKED_SWATCHES_PATH . 'includes/Updater/Updater.php';
			Updater\Updater::boot();
		}

		if (did_action('elementor/loaded')) {
			$this->maybe_load_elementor();
		} else {
			add_action('elementor/loaded', [$this, 'maybe_load_elementor']);
		}
	}

	public function maybe_load_elementor() : void {
		if (! self::has_woocommerce()) {
			return;
		}

		$elementor_file = VRED_LINKED_SWATCHES_PATH . 'includes/Elementor/Elementor.php';

		if (file_exists($elementor_file)) {
			require_once $elementor_file;

			if (class_exists('\\VRED_Linked_Swatches\\Elementor\\Elementor')) {
				Elementor\Elementor::boot();
			}
		}
	}

	public static function has_woocommerce() : bool {
		return class_exists('WooCommerce') && class_exists('WC_Product');
	}
}
