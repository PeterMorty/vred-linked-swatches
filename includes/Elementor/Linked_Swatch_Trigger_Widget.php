<?php

namespace VRED_Linked_Swatches\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;
use VRED_Linked_Swatches\Assets;
use VRED_Linked_Swatches\Swatch_Data;
use VRED_Linked_Swatches\Renderer;

if (! defined('ABSPATH')) {
	exit;
}

/** Linked swatch trigger widget */
final class Linked_Swatch_Trigger_Widget extends Widget_Base {
	public function get_name() : string {
		return 'vred-linked-swatch-trigger';
	}

	public function get_title() : string {
		return __('Linked Swatch Trigger', 'vred-linked-swatches');
	}

	public function get_icon() : string {
		return 'vred-linked-swatches-icon eicon-button';
	}

	public function get_categories() : array {
		return ['vred-linked-swatches'];
	}

	public function get_style_depends() : array {
		Assets::register_frontend();

		return ['vred-linked-swatches-frontend'];
	}

	public function get_keywords() : array {
		return ['vred', 'woocommerce', 'linked', 'product', 'color', 'swatch'];
	}

	protected function register_controls() : void {
		$this->register_content_controls();
		$this->register_trigger_style_controls();
	}

	private function register_content_controls() : void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __('Content', 'vred-linked-swatches'),
			]
		);

		$has_vred_panel = self::has_vred_elements_panel_support();
		$custom_link_condition = $has_vred_panel ? ['action' => 'custom_link'] : [];

		if ($has_vred_panel) {
			$this->add_control(
				'action',
				[
					'label' => __('Action', 'vred-linked-swatches'),
					'type' => Controls_Manager::SELECT,
					'default' => 'custom_link',
					'options' => [
						'custom_link' => __('Custom link', 'vred-linked-swatches'),
						'vred_panel' => __('VRED Elements panel', 'vred-linked-swatches'),
					],
				]
			);
		}

		$this->add_control(
			'label',
			[
				'label' => __('Label', 'vred-linked-swatches'),
				'type' => Controls_Manager::TEXT,
				'default' => __('Color', 'vred-linked-swatches'),
				'placeholder' => __('Color', 'vred-linked-swatches'),
			]
		);

		$this->add_control(
			'link',
			[
				'label' => __('Link', 'vred-linked-swatches'),
				'type' => Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => 'https://example.com',
				'show_external' => true,
				'default' => [
					'url' => '',
					'is_external' => '',
					'nofollow' => '',
					'custom_attributes' => '',
				],
				'condition' => $custom_link_condition,
			]
		);

		$this->add_control(
			'link_class',
			[
				'label' => __('Extra CSS classes', 'vred-linked-swatches'),
				'type' => Controls_Manager::TEXT,
				'description' => __('Optional classes for external popup, off-canvas, or selector-based integrations.', 'vred-linked-swatches'),
				'default' => '',
				'condition' => $custom_link_condition,
			]
		);

		if ($has_vred_panel) {
			$this->add_control(
				'panel_id',
				[
					'label' => __('Panel ID', 'vred-linked-swatches'),
					'type' => Controls_Manager::TEXT,
					'description' => __('Enter the VRED Elements Panel ID without #. The target panel must be rendered on the same page.', 'vred-linked-swatches'),
					'default' => '',
					'condition' => [
						'action' => 'vred_panel',
					],
				]
			);

			$this->add_control(
				'panel_action',
				[
					'label' => __('Panel action', 'vred-linked-swatches'),
					'type' => Controls_Manager::SELECT,
					'default' => 'open',
					'options' => [
						'open' => __('Open panel', 'vred-linked-swatches'),
						'toggle' => __('Toggle panel', 'vred-linked-swatches'),
					],
					'condition' => [
						'action' => 'vred_panel',
					],
				]
			);
		}
		$this->end_controls_section();
	}

	private function register_trigger_style_controls() : void {
		$this->start_controls_section(
			'section_style_trigger',
			[
				'label' => __('Trigger', 'vred-linked-swatches'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'label' => __('Label typography', 'vred-linked-swatches'),
				'selector' => '{{WRAPPER}} .vred-linked-swatches-trigger__label',
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => __('Label color', 'vred-linked-swatches'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .vred-linked-swatches-trigger__label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'value_typography',
				'label' => __('Value typography', 'vred-linked-swatches'),
				'selector' => '{{WRAPPER}} .vred-linked-swatches-trigger__name',
			]
		);

		$this->add_control(
			'value_color',
			[
				'label' => __('Value color', 'vred-linked-swatches'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .vred-linked-swatches-trigger__name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'swatch_style_heading',
			[
				'label' => __('Swatch', 'vred-linked-swatches'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'swatch_size',
			[
				'label' => __('Swatch size (px)', 'vred-linked-swatches'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 8,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .vred-linked-swatches' => '--vred-linked-swatches-swatch-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'swatch_border',
				'label' => __('Border', 'vred-linked-swatches'),
				'selector' => '{{WRAPPER}} .vred-linked-swatches',
				'fields_options' => [
					'border' => [
						'selectors' => [
							'{{WRAPPER}} .vred-linked-swatches' => '--vred-linked-swatches-swatch-border-style: {{VALUE}};',
						],
					],
					'width' => [
						'selectors' => [
							'{{WRAPPER}} .vred-linked-swatches' => '--vred-linked-swatches-swatch-border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					],
					'color' => [
						'selectors' => [
							'{{WRAPPER}} .vred-linked-swatches' => '--vred-linked-swatches-swatch-border-color: {{VALUE}};',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'swatch_border_radius',
			[
				'label' => __('Border radius', 'vred-linked-swatches'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .vred-linked-swatches' => '--vred-linked-swatches-swatch-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'trigger_icon_heading',
			[
				'label' => __('Icon', 'vred-linked-swatches'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'trigger_icon',
			[
				'label' => '',
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-right',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => __('Icon size', 'vred-linked-swatches'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'default' => [
					'unit' => 'px',
					'size' => 16,
				],
				'range' => [
					'px' => [
						'min' => 8,
						'max' => 80,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .vred-linked-swatches' => '--vred-linked-swatches-trigger-icon-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'trigger_icon_gap',
			[
				'label' => __('Swatch spacing (px)', 'vred-linked-swatches'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 80,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .vred-linked-swatches' => '--vred-linked-swatches-trigger-icon-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'trigger_icon_horizontal_offset',
			[
				'label' => __('Horizontal offset (px)', 'vred-linked-swatches'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => -80,
						'max' => 80,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .vred-linked-swatches' => '--vred-linked-swatches-trigger-icon-offset-x: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'trigger_icon_vertical_offset',
			[
				'label' => __('Vertical offset (px)', 'vred-linked-swatches'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => -20,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .vred-linked-swatches' => '--vred-linked-swatches-trigger-icon-offset: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => __('Icon color', 'vred-linked-swatches'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .vred-linked-swatches' => '--vred-linked-swatches-trigger-icon-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}


	protected function render() : void {
		$product = Swatch_Data::get_current_product();

		if (! $product instanceof \WC_Product) {
			return;
		}

		$settings = $this->get_settings_for_display();
		$args = $this->get_render_args(is_array($settings) ? $settings : []);

		Assets::enqueue_frontend();

		if (Renderer::trigger_uses_vred_panel($args)) {
			Assets::enqueue_vred_panel_script();
		}

		echo Renderer::render_trigger($product, $args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	private function get_render_args(array $settings) : array {
		$link = isset($settings['link']) && is_array($settings['link']) ? $settings['link'] : [];
		$rel = [];

		if (! empty($link['nofollow'])) {
			$rel[] = 'nofollow';
		}

		if (! empty($link['is_external'])) {
			$rel[] = 'noopener';
		}

		return [
			'icon_markup' => self::get_icon_markup(isset($settings['trigger_icon']) && is_array($settings['trigger_icon']) ? $settings['trigger_icon'] : []),
			'action' => ! empty($settings['action']) ? (string) $settings['action'] : 'custom_link',
			'label' => ! empty($settings['label']) ? (string) $settings['label'] : __('Color', 'vred-linked-swatches'),
			'link_url' => ! empty($link['url']) ? (string) $link['url'] : '',
			'link_target' => ! empty($link['is_external']) ? '_blank' : '',
			'link_rel' => implode(' ', $rel),
			'link_class' => ! empty($settings['link_class']) ? (string) $settings['link_class'] : '',
			'panel_id' => ! empty($settings['panel_id']) ? (string) $settings['panel_id'] : '',
			'panel_action' => ! empty($settings['panel_action']) ? (string) $settings['panel_action'] : 'open',
		];
	}

	private static function get_icon_markup(array $icon) : string {
		if (empty($icon['value'])) {
			$icon = [
				'value' => 'fas fa-chevron-right',
				'library' => 'fa-solid',
			];
		}

		ob_start();

		Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']);

		return trim((string) ob_get_clean());
	}

	private static function has_vred_elements_panel_support() : bool {
		return defined('VRED_ELEMENTS_VERSION') || function_exists('vred_elements_register_panel_script');
	}
}
