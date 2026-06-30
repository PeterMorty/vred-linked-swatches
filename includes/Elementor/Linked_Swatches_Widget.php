<?php

namespace VRED_Linked_Swatches\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use VRED_Linked_Swatches\Assets;
use VRED_Linked_Swatches\Swatch_Data;
use VRED_Linked_Swatches\Renderer;

if (! defined('ABSPATH')) {
	exit;
}

/** Linked swatches list widget */
final class Linked_Swatches_Widget extends Widget_Base {
	public function get_name() : string {
		return 'vred-linked-swatches';
	}

	public function get_title() : string {
		return __('Linked Swatches', 'vred-linked-swatches');
	}

	public function get_icon() : string {
		return 'vred-linked-swatches-icon eicon-products';
	}

	public function get_categories() : array {
		return ['vred-linked-swatches'];
	}

	public function get_style_depends() : array {
		Assets::register_frontend();

		return ['vred-linked-swatches-frontend'];
	}

	public function get_keywords() : array {
		return ['vred', 'woocommerce', 'linked', 'products', 'color', 'swatch'];
	}

	protected function register_controls() : void {
		$this->register_content_controls();
		$this->register_elements_style_controls();
	}

	private function register_content_controls() : void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __('Content', 'vred-linked-swatches'),
			]
		);

		$this->add_control(
			'include_current',
			[
				'label' => __('Include current product', 'vred-linked-swatches'),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_images',
			[
				'label' => __('Show product image', 'vred-linked-swatches'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('Yes', 'vred-linked-swatches'),
				'label_off' => __('No', 'vred-linked-swatches'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'image',
				'default' => 'woocommerce_thumbnail',
				'condition' => [
					'show_images' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_elements_style_controls() : void {
		$root_selector = '{{WRAPPER}} .vred-linked-swatches-list';
		$name_selector = '{{WRAPPER}} .vred-linked-swatches-list__name';

		$this->start_controls_section(
			'section_items_style',
			[
				'label' => __('Design', 'vred-linked-swatches'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => __('Columns', 'vred-linked-swatches'),
				'type' => Controls_Manager::NUMBER,
				'default' => 2,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'min' => 1,
				'max' => 8,
				'step' => 1,
				'selectors_dictionary' => $this->get_fixed_columns_dictionary(),
				'selectors' => [
					$root_selector => '--vred-linked-swatches-items-columns: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label' => __('Separation between elements (px)', 'vred-linked-swatches'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 120,
					],
				],
				'selectors' => [
					$root_selector => '--vred-linked-swatches-items-row-gap: {{SIZE}}{{UNIT}}; --vred-linked-swatches-items-column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'item_heading',
			[
				'label' => __('Element', 'vred-linked-swatches'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'text_align',
			[
				'label' => __('Alignment', 'vred-linked-swatches'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __('Left', 'vred-linked-swatches'),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __('Center', 'vred-linked-swatches'),
						'icon' => 'eicon-h-align-center',
					],
					'right' => [
						'title' => __('Right', 'vred-linked-swatches'),
						'icon' => 'eicon-h-align-right',
					],
				],
				'selectors_dictionary' => [
					'left' => 'start; --vred-linked-swatches-items-margin-left: 0; --vred-linked-swatches-items-margin-right: auto',
					'center' => 'center; --vred-linked-swatches-items-margin-left: auto; --vred-linked-swatches-items-margin-right: auto',
					'right' => 'end; --vred-linked-swatches-items-margin-left: auto; --vred-linked-swatches-items-margin-right: 0',
				],
				'selectors' => [
					$root_selector => '--vred-linked-swatches-items-justify: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_width',
			[
				'label' => __('Item width (px)', 'vred-linked-swatches'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 80,
						'max' => 600,
					],
				],
				'selectors' => [
					$root_selector => '--vred-linked-swatches-items-display: flex; --vred-linked-swatches-items-container-width: fit-content; --vred-linked-swatches-items-fixed-width: {{SIZE}}{{UNIT}}; --vred-linked-swatches-items-max-width: min(100%, var(--vred-linked-swatches-items-fixed-max-width));',
				],
			]
		);

		$this->add_responsive_control(
			'padding',
			[
				'label' => __('Padding', 'vred-linked-swatches'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', 'rem'],
				'selectors' => [
					$root_selector => '--vred-linked-swatches-items-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'item_border',
				'label' => __('Border', 'vred-linked-swatches'),
				'selector' => $root_selector,
				'fields_options' => [
					'border' => [
						'selectors' => [
							$root_selector => '--vred-linked-swatches-items-border-style: {{VALUE}};',
						],
					],
					'width' => [
						'selectors' => [
							$root_selector => '--vred-linked-swatches-items-border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					],
					'color' => [
						'selectors' => [
							$root_selector => '--vred-linked-swatches-items-border-color: {{VALUE}};',
						],
					],
				],
			]
		);

		$this->add_control(
			'active_border_color',
			[
				'label' => __('Active border color', 'vred-linked-swatches'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					$root_selector => '--vred-linked-swatches-items-active-border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label' => __('Border radius', 'vred-linked-swatches'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 80,
					],
					'%' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					$root_selector => '--vred-linked-swatches-items-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'text_heading',
			[
				'label' => __('Text', 'vred-linked-swatches'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'trim_text',
			[
				'label' => __('Trim text', 'vred-linked-swatches'),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_responsive_control(
			'name_align',
			[
				'label' => __('Alignment', 'vred-linked-swatches'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __('Left', 'vred-linked-swatches'),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __('Center', 'vred-linked-swatches'),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __('Right', 'vred-linked-swatches'),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors_dictionary' => [
					'left' => 'left; --vred-linked-swatches-items-text-justify: flex-start',
					'center' => 'center; --vred-linked-swatches-items-text-justify: center',
					'right' => 'right; --vred-linked-swatches-items-text-justify: flex-end',
				],
				'selectors' => [
					$root_selector => '--vred-linked-swatches-items-text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'name_typography',
				'label' => __('Typography', 'vred-linked-swatches'),
				'selector' => $name_selector,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __('Text color', 'vred-linked-swatches'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					$root_selector => '--vred-linked-swatches-items-text-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'swatch_heading',
			[
				'label' => __('Swatch', 'vred-linked-swatches'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'visual_size',
			[
				'label' => __('Swatch size (px)', 'vred-linked-swatches'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 32,
						'max' => 360,
					],
				],
				'condition' => [
					'show_images' => 'yes',
				],
				'selectors' => [
					$root_selector => '--vred-linked-swatches-list-swatch-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'swatch_border',
				'label' => __('Border', 'vred-linked-swatches'),
				'selector' => $root_selector,
				'fields_options' => [
					'border' => [
						'selectors' => [
							$root_selector => '--vred-linked-swatches-swatch-border-style: {{VALUE}};',
						],
					],
					'width' => [
						'selectors' => [
							$root_selector => '--vred-linked-swatches-swatch-border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					],
					'color' => [
						'selectors' => [
							$root_selector => '--vred-linked-swatches-swatch-border-color: {{VALUE}};',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'visual_border_radius',
			[
				'label' => __('Border radius', 'vred-linked-swatches'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 999,
					],
					'%' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					$root_selector => '--vred-linked-swatches-swatch-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function get_fixed_columns_dictionary() : array {
		$dictionary = [];

		for ($columns = 1; $columns <= 8; $columns++) {
			$width_parts = [];

			for ($index = 0; $index < $columns; $index++) {
				$width_parts[] = 'var(--vred-linked-swatches-items-fixed-width)';

				if ($index < $columns - 1) {
					$width_parts[] = 'var(--vred-linked-swatches-items-column-gap)';
				}
			}

			$max_width = count($width_parts) > 1 ? 'calc(' . implode(' + ', $width_parts) . ')' : $width_parts[0];
			$dictionary[(string) $columns] = $columns . '; --vred-linked-swatches-items-fixed-max-width: ' . $max_width;
		}

		return $dictionary;
	}

	protected function render() : void {
		$product = Swatch_Data::get_current_product();

		if (! $product instanceof \WC_Product) {
			return;
		}

		$settings = $this->get_settings_for_display();
		Assets::enqueue_frontend();

		echo Renderer::render_products($product, [
			'include_current' => ! empty($settings['include_current']),
			'show_images' => ! empty($settings['show_images']),
			'image_size' => ! empty($settings['image_size']) ? (string) $settings['image_size'] : 'woocommerce_thumbnail',
			'image_custom_dimension' => ! empty($settings['image_custom_dimension']) && is_array($settings['image_custom_dimension']) ? $settings['image_custom_dimension'] : [],
			'trim_text' => ! empty($settings['trim_text']),
		]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
