<?php

namespace VRED_Linked_Swatches;

if (! defined('ABSPATH')) {
	exit;
}

/** Frontend markup renderer */
final class Renderer {
	public static function render_trigger(\WC_Product $product, array $args = []) : string {
		$args = wp_parse_args($args, [
			'action' => 'custom_link',
			'label' => '',
			'link_url' => '',
			'link_target' => '',
			'link_rel' => '',
			'link_class' => '',
			'panel_id' => '',
			'panel_action' => 'open',
			'icon_markup' => '',
		]);

		$data = Swatch_Data::get_product_option_data($product);
		$label = trim((string) $args['label']);
		$label = $label !== '' ? $label : __('Color', 'vred-linked-swatches');
		$products = Swatch_Data::get_option_products($product);

		if (count($products) <= 1 && $data['name'] === $product->get_name() && ! self::has_swatch_visual($data)) {
			return '';
		}

		$action = self::normalize_trigger_action($args);
		$icon_markup = trim((string) ($args['icon_markup'] ?? ''));
		$link_attributes = self::get_trigger_link_attributes($action, $args);

		ob_start();
		?>
		<div class="vred-linked-swatches" data-vred-linked-swatches-root>
			<a <?php echo self::get_attributes_markup($link_attributes); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<span class="vred-linked-swatches-trigger__label"><?php echo esc_html($label); ?></span>
				<span class="vred-linked-swatches-trigger__value">
					<span class="vred-linked-swatches-trigger__name"><?php echo esc_html($data['name']); ?></span>
					<span class="vred-linked-swatches-trigger__media">
						<?php echo self::render_swatch($data, 'vred-linked-swatches-trigger__swatch', true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="vred-linked-swatches-trigger__icon" aria-hidden="true">
							<?php echo $icon_markup !== '' ? $icon_markup : '&rsaquo;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</span>
				</span>
			</a>
		</div>
		<?php

		return trim((string) ob_get_clean());
	}

	public static function render_products(\WC_Product $product, array $args = []) : string {
		$args = self::normalize_items_args($args);
		$items = self::get_items($product, $args);

		if (empty($items)) {
			return '';
		}

		$list_classes = ['vred-linked-swatches-list'];

		if (! $args['show_images']) {
			$list_classes[] = 'vred-linked-swatches-list--visual-swatch';
		}

		if (! empty($args['trim_text'])) {
			$list_classes[] = 'vred-linked-swatches-list--trim-text';
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr(implode(' ', $list_classes)); ?>">
			<?php foreach ($items as $item) : ?>
				<?php echo self::render_item($item, $args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endforeach; ?>
		</div>
		<?php

		return trim((string) ob_get_clean());
	}

	public static function trigger_uses_vred_panel(array $args) : bool {
		return self::normalize_trigger_action($args) === 'vred_panel' && self::sanitize_panel_id((string) ($args['panel_id'] ?? '')) !== '';
	}

	private static function get_items(\WC_Product $product, array $args) : array {
		$products = Swatch_Data::get_option_products($product, ! empty($args['include_current']));
		$current_id = (int) $product->get_id();
		$items = [];

		foreach ($products as $option_product) {
			if (! $option_product instanceof \WC_Product) {
				continue;
			}

			$option_data = Swatch_Data::get_product_option_data($option_product);

			$items[] = [
				'product' => $option_product,
				'is_current' => (int) $option_product->get_id() === $current_id,
				'name' => $option_data['name'],
				'option_data' => $option_data,
				'permalink' => get_permalink($option_product->get_id()),
			];
		}

		return $items;
	}

	private static function render_item(array $item, array $args) : string {
		$product = $item['product'] ?? null;

		if (! $product instanceof \WC_Product) {
			return '';
		}

		$is_current = ! empty($item['is_current']);
		$item_classes = ['vred-linked-swatches-list__item'];

		if ($is_current) {
			$item_classes[] = 'is-active';
		}

		$tag = $is_current ? 'div' : 'a';
		$attributes = [
			'class' => implode(' ', $item_classes),
		];

		if ($is_current) {
			$attributes['aria-current'] = 'page';
		} else {
			$attributes['href'] = ! empty($item['permalink']) ? (string) $item['permalink'] : get_permalink($product->get_id());
		}

		$option_data = (array) ($item['option_data'] ?? []);
		$swatch_markup = self::has_swatch_visual($option_data) ? self::render_swatch($option_data, 'vred-linked-swatches-list__swatch') : '';

		ob_start();
		?>
		<<?php echo tag_escape($tag); ?> <?php echo self::get_attributes_markup($attributes); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php echo self::render_item_visual($item, $args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="vred-linked-swatches-list__meta">
				<?php if (! $args['show_images']) : ?>
					<span class="vred-linked-swatches-list__name"><?php echo esc_html((string) ($item['name'] ?? '')); ?></span>
				<?php else : ?>
					<?php echo $swatch_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="vred-linked-swatches-list__name"><?php echo esc_html((string) ($item['name'] ?? '')); ?></span>
				<?php endif; ?>
			</span>
		</<?php echo tag_escape($tag); ?>>
		<?php

		return trim((string) ob_get_clean());
	}

	private static function render_item_visual(array $item, array $args) : string {
		$product = $item['product'] ?? null;

		if (! $product instanceof \WC_Product) {
			return '';
		}

		if (empty($args['show_images'])) {
			$option_data = (array) ($item['option_data'] ?? []);

			if (! self::has_swatch_visual($option_data)) {
				return '';
			}

			return '<span class="vred-linked-swatches-list__visual vred-linked-swatches-list__visual--swatch">' . self::render_swatch($option_data, 'vred-linked-swatches-list__visual-swatch') . '</span>';
		}

		$image_size = ! empty($args['image_size']) ? $args['image_size'] : 'woocommerce_thumbnail';

		return '<span class="vred-linked-swatches-list__visual vred-linked-swatches-list__image">' . $product->get_image($image_size) . '</span>';
	}

	private static function render_swatch(array $data, string $class, bool $use_product_thumbnail = false) : string {
		$styles = [];
		$classes = [$class, 'vred-linked-swatches-swatch'];
		$label = ! empty($data['name']) ? (string) $data['name'] : '';
		$color = Swatch_Data::sanitize_color((string) ($data['color'] ?? ''));
		$secondary_color = Swatch_Data::sanitize_color((string) ($data['secondary_color'] ?? ''));
		$image_url = ! empty($data['image_url']) ? esc_url_raw((string) $data['image_url']) : '';
		$product_image_url = ! empty($data['product_image_url']) ? esc_url_raw((string) $data['product_image_url']) : '';

		if ($color === '' && $secondary_color !== '') {
			$color = $secondary_color;
			$secondary_color = '';
		}

		if ($image_url !== '') {
			$classes[] = 'has-image';
			$styles[] = 'background-image: url(' . esc_url($image_url) . ')';
		} elseif ($color !== '' && $secondary_color !== '') {
			$classes[] = 'has-secondary-color';
			$styles[] = '--vred-linked-swatches-color: ' . $color;
			$styles[] = '--vred-linked-swatches-secondary-color: ' . $secondary_color;
		} elseif ($color !== '') {
			$styles[] = '--vred-linked-swatches-color: ' . $color;
		} elseif ($use_product_thumbnail && $product_image_url !== '') {
			$classes[] = 'has-image';
			$classes[] = 'is-product-thumbnail';
			$styles[] = 'background-image: url(' . esc_url($product_image_url) . ')';
		} else {
			$classes[] = 'has-no-color';
		}

		return sprintf(
			'<span class="%1$s" style="%2$s" aria-label="%3$s"></span>',
			esc_attr(implode(' ', $classes)),
			esc_attr(implode('; ', $styles)),
			esc_attr($label)
		);
	}

	private static function has_swatch_visual(array $data) : bool {
		$color = Swatch_Data::sanitize_color((string) ($data['color'] ?? ''));
		$secondary_color = Swatch_Data::sanitize_color((string) ($data['secondary_color'] ?? ''));
		$image_url = ! empty($data['image_url']) ? esc_url_raw((string) $data['image_url']) : '';

		return $image_url !== '' || $color !== '' || $secondary_color !== '';
	}

	private static function normalize_items_args(array $args = []) : array {
		$args = wp_parse_args($args, [
			'include_current' => true,
			'show_images' => true,
			'image_size' => 'woocommerce_thumbnail',
			'image_custom_dimension' => [],
			'trim_text' => true,
		]);

		$args['show_images'] = ! empty($args['show_images']);
		$args['include_current'] = ! empty($args['include_current']);
		$args['trim_text'] = ! empty($args['trim_text']);
		$args['image_size'] = self::normalize_image_size((string) $args['image_size'], is_array($args['image_custom_dimension']) ? $args['image_custom_dimension'] : []);

		return $args;
	}

	private static function normalize_image_size(string $size, array $custom_dimension = []) {
		$size = sanitize_key($size);

		if ($size === 'custom') {
			$width = isset($custom_dimension['width']) ? absint($custom_dimension['width']) : 0;
			$height = isset($custom_dimension['height']) ? absint($custom_dimension['height']) : 0;

			if ($width > 0 && $height > 0) {
				return [$width, $height];
			}

			return 'woocommerce_thumbnail';
		}

		if ($size === 'full') {
			return 'full';
		}

		if (in_array($size, get_intermediate_image_sizes(), true)) {
			return $size;
		}

		return 'woocommerce_thumbnail';
	}

	private static function normalize_trigger_action(array $args) : string {
		$action = ! empty($args['action']) ? sanitize_key((string) $args['action']) : 'custom_link';
		$map = [
			'custom_link' => 'custom_link',
			'vred_panel' => 'vred_panel',
		];

		return $map[$action] ?? 'custom_link';
	}

	private static function get_trigger_link_attributes(string $action, array $args) : array {
		$classes = ['vred-linked-swatches-trigger'];
		$extra_classes = $action === 'custom_link' ? self::sanitize_class_list((string) ($args['link_class'] ?? '')) : '';

		if ($extra_classes !== '') {
			$classes[] = $extra_classes;
		}

		$attributes = [
			'class' => implode(' ', $classes),
			'href' => '#',
		];

		if ($action === 'vred_panel') {
			$panel_id = self::sanitize_panel_id((string) ($args['panel_id'] ?? ''));
			$panel_action = ! empty($args['panel_action']) && sanitize_key((string) $args['panel_action']) === 'toggle' ? 'toggle' : 'open';

			if ($panel_id !== '') {
				$attributes['href'] = '#' . $panel_id;
				$attributes['data-vred-panel-' . $panel_action] = $panel_id;
				$attributes['aria-expanded'] = 'false';
				$attributes['role'] = 'button';
			}

			return $attributes;
		}

		$link_url = trim((string) ($args['link_url'] ?? ''));
		$target = self::sanitize_link_target((string) ($args['link_target'] ?? ''));
		$rel = self::sanitize_rel_list((string) ($args['link_rel'] ?? ''));

		$attributes['href'] = $link_url !== '' ? esc_url($link_url) : '#';

		if ($target !== '') {
			$attributes['target'] = $target;
		}

		if ($rel !== '') {
			$attributes['rel'] = $rel;
		}

		return $attributes;
	}

	private static function get_attributes_markup(array $attributes) : string {
		$markup = [];

		foreach ($attributes as $name => $value) {
			$name = sanitize_key((string) $name);

			if ($name === '') {
				continue;
			}

			if ($value === '') {
				$markup[] = esc_attr($name);
				continue;
			}

			$markup[] = esc_attr($name) . '="' . esc_attr((string) $value) . '"';
		}

		return implode(' ', $markup);
	}

	private static function sanitize_class_list(string $classes) : string {
		$classes = preg_split('/\s+/', trim($classes));

		if (! is_array($classes)) {
			return '';
		}

		$classes = array_filter(array_map('sanitize_html_class', $classes));

		return implode(' ', array_unique($classes));
	}

	private static function sanitize_panel_id(string $panel_id) : string {
		return sanitize_title(trim($panel_id));
	}

	private static function sanitize_link_target(string $target) : string {
		$target = strtolower(trim($target));

		return in_array($target, ['_blank', '_self', '_parent', '_top'], true) ? $target : '';
	}

	private static function sanitize_rel_list(string $rel) : string {
		$values = preg_split('/\s+/', strtolower(trim($rel)));

		if (! is_array($values)) {
			return '';
		}

		$values = array_filter(array_map('sanitize_key', $values));

		return implode(' ', array_unique($values));
	}
}
