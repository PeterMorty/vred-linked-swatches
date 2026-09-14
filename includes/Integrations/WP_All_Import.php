<?php

namespace VRED_Linked_Swatches\Integrations;

use VRED_Linked_Swatches\Plugin;
use VRED_Linked_Swatches\Swatch_Data;

if (! defined('ABSPATH')) {
	exit;
}

/** Regenerate grouped product links when WP All Import finishes. */
final class WP_All_Import {
	public static function boot() : void {
		// An unused action is harmless when WP All Import is not installed.
		add_action('pmxi_after_xml_import', [self::class, 'sync_groups'], 10, 0);
	}

	public static function sync_groups() : void {
		if (! Plugin::has_woocommerce() || ! function_exists('wc_get_product')) {
			return;
		}

		$groups = [];
		$page = 1;
		$batch_size = 200;
		$statuses = array_values(array_diff(get_post_stati(), ['trash', 'auto-draft', 'inherit']));

		// Inspect all groups so former group members are also repaired after a move or deletion.
		do {
			$query = new \WP_Query([
				'post_type' => 'product',
				'post_status' => $statuses,
				'fields' => 'ids',
				'posts_per_page' => $batch_size,
				'paged' => $page,
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'ignore_sticky_posts' => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_query' => [
					[
						'key' => Swatch_Data::META_GROUP,
						'value' => '',
						'compare' => '!=',
					],
				],
			]);
			$ids = $query->posts;

			if ($ids) {
				update_meta_cache('post', $ids);
			}

			foreach ($ids as $id) {
				$group = Swatch_Data::sanitize_group(get_post_meta($id, Swatch_Data::META_GROUP, true));

				if ($group === '') {
					continue;
				}

				// PHP string keys keep matching case-sensitive, regardless of database collation.
				$groups['group:' . $group][] = (int) $id;
			}

			++$page;
		} while (count($ids) === $batch_size);

		foreach ($groups as $ids) {
			$products = [];

			foreach (array_unique($ids) as $id) {
				$product = wc_get_product($id);

				if ($product instanceof \WC_Product) {
					$products[$id] = $product;
				}
			}

			$group_ids = array_keys($products);

			foreach ($products as $id => $product) {
				$linked_ids = array_values(array_diff($group_ids, [$id]));

				if ($product->get_meta(Swatch_Data::META_IDS, true) === $linked_ids) {
					continue;
				}

				$product->update_meta_data(Swatch_Data::META_IDS, $linked_ids);
				$product->save();
			}
		}
	}
}
