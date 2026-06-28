<?php

namespace VRED_Linked_Swatches\Elementor;

if (! defined('ABSPATH')) {
	exit;
}

/** Elementor integration */
final class Elementor {
	private static $booted = false;

	public static function boot() : void {
		if (self::$booted) {
			return;
		}

		self::$booted = true;
		add_action('elementor/elements/categories_registered', [self::class, 'register_category']);
		add_action('elementor/widgets/register', [self::class, 'register_widgets']);
		add_action('elementor/editor/after_enqueue_styles', [self::class, 'enqueue_editor_styles']);
	}

	/** Enqueue editor-only styles */
	public static function enqueue_editor_styles() : void {
		wp_enqueue_style(
			'vred-linked-swatches-editor',
			VRED_LINKED_SWATCHES_URL . 'assets/css/editor.css',
			[],
			VRED_LINKED_SWATCHES_VERSION
		);
	}

	public static function register_category($elements_manager) : void {
		$elements_manager->add_category(
			'vred-linked-swatches',
			[
				'title' => __('VRED Linked Swatches', 'vred-linked-swatches'),
				'icon' => 'fa fa-plug',
			]
		);
	}

	public static function register_widgets($widgets_manager) : void {
		require_once VRED_LINKED_SWATCHES_PATH . 'includes/Elementor/Linked_Swatch_Trigger_Widget.php';
		require_once VRED_LINKED_SWATCHES_PATH . 'includes/Elementor/Linked_Swatches_Widget.php';

		$widgets_manager->register(new Linked_Swatch_Trigger_Widget());
		$widgets_manager->register(new Linked_Swatches_Widget());
	}
}
