<?php

/** Standalone regression checks: php tests/wp-all-import.php */
if (PHP_SAPI !== 'cli') {
	exit(1);
}

define('ABSPATH', __DIR__ . '/');
error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
	throw new ErrorException($message, 0, $severity, $file, $line);
});

$hooks = [];
$catalog = [];
$queries = [];
$can_edit = true;

function check($condition, string $message) : void {
	if (! $condition) {
		throw new RuntimeException($message);
	}
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
	$GLOBALS['hooks'][$hook][] = [$callback, $accepted_args];
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
	add_filter($hook, $callback, $priority, $accepted_args);
}

function do_action($hook, ...$args) {
	foreach ($GLOBALS['hooks'][$hook] ?? [] as [$callback, $accepted_args]) {
		call_user_func_array($callback, array_slice($args, 0, $accepted_args));
	}
}

function sanitize_text_field($value) {
	return trim(preg_replace('/[\r\n\t ]+/', ' ', strip_tags($value)));
}

function absint($value) {
	return abs((int) $value);
}

function wp_unslash($value) {
	return is_array($value) ? array_map('wp_unslash', $value) : stripslashes($value);
}

function sanitize_hex_color($value) {
	return preg_match('/^#(?:[a-f0-9]{3}){1,2}$/i', $value) ? $value : null;
}

function current_user_can($capability, $id) {
	return $GLOBALS['can_edit'];
}

function get_post_stati() {
	return ['publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft', 'inherit'];
}

function update_meta_cache($type, $ids) {
	check($type === 'post' && count($ids) <= 200, 'Metadata must be primed in bounded batches.');
}

function get_post_meta($id, $key, $single) {
	return $GLOBALS['catalog'][$id]->get_meta($key, $single);
}

function wc_get_product($id) {
	$product = $GLOBALS['catalog'][$id] ?? null;
	return $product && $product->loadable ? $product : false;
}

class WP_Query {
	public $posts;

	public function __construct(array $args) {
		$GLOBALS['queries'][] = $args;
		check($args['fields'] === 'ids' && $args['no_found_rows'], 'Discovery must query IDs without total counts.');
		check($args['orderby'] === 'ID' && $args['order'] === 'ASC', 'Pagination must use a stable order.');
		$meta = $args['meta_query'][0];
		check($meta['key'] === \VRED_Linked_Swatches\Swatch_Data::META_GROUP && $meta['compare'] === '!=' && $meta['value'] === '', 'Only grouped products should be queried.');
		$ids = [];

		foreach ($GLOBALS['catalog'] as $id => $product) {
			if ($product->post_type === $args['post_type'] && in_array($product->status, $args['post_status'], true) && $product->get_meta($meta['key'], true) !== '') {
				$ids[] = $id;
			}
		}

		sort($ids, SORT_NUMERIC);
		$this->posts = array_slice($ids, ($args['paged'] - 1) * $args['posts_per_page'], $args['posts_per_page']);
	}
}

class WC_Product {
	public $id;
	public $meta;
	public $saves = 0;
	public $post_type = 'product';
	public $status = 'publish';
	public $loadable = true;

	public function __construct(int $id, $group, $links = []) {
		$this->id = $id;
		$this->meta = [
			\VRED_Linked_Swatches\Swatch_Data::META_GROUP => $group,
			\VRED_Linked_Swatches\Swatch_Data::META_IDS => $links,
			\VRED_Linked_Swatches\Swatch_Data::META_NAME => 'Existing name',
			\VRED_Linked_Swatches\Swatch_Data::META_COLOR => '#111111',
			\VRED_Linked_Swatches\Swatch_Data::META_SECONDARY_COLOR => '#F2F0E8',
			\VRED_Linked_Swatches\Swatch_Data::META_IMAGE_ID => 42,
		];
		$GLOBALS['catalog'][$id] = $this;
	}

	public function get_id() { return $this->id; }
	public function get_meta($key, $single) { return $this->meta[$key] ?? ''; }
	public function update_meta_data($key, $value) { $this->meta[$key] = $value; }
	public function save() { ++$this->saves; }
}

require_once __DIR__ . '/../includes/Plugin.php';
require_once __DIR__ . '/../includes/Swatch_Data.php';
require_once __DIR__ . '/../includes/Admin/Swatch_Panel.php';
require_once __DIR__ . '/../includes/Integrations/WP_All_Import.php';

use VRED_Linked_Swatches\Admin\Swatch_Panel;
use VRED_Linked_Swatches\Integrations\WP_All_Import;
use VRED_Linked_Swatches\Swatch_Data;

Swatch_Data::boot();
WP_All_Import::boot();
check(isset($hooks['pmxi_after_xml_import']), 'Registration must work without WP All Import.');
do_action('pmxi_after_xml_import', 1);
check($queries === [], 'Missing WooCommerce must be a safe no-op.');

// Declare WooCommerce only after checking the missing-dependency path.
if (! class_exists('WooCommerce')) {
	class WooCommerce {}
}

$sanitize = $hooks['sanitize_post_meta_' . Swatch_Data::META_GROUP . '_for_product'][0][0];
check(call_user_func($sanitize, "  <b>100-5004-10</b>\n") === '100-5004-10', 'Group metadata must be sanitized.');
check(Swatch_Data::sanitize_group(['invalid']) === '' && Swatch_Data::sanitize_group(new stdClass()) === '', 'Malformed groups must not raise warnings.');
check(Swatch_Data::sanitize_group('0') === '0', 'Zero is a valid group identifier.');

$a = new WC_Product(1, 'MOG-2026', [1, 99]);
$b = new WC_Product(2, 'MOG-2026', 'invalid legacy value');
$c = new WC_Product(3, 'MOG-2026');
$manual = new WC_Product(4, '', [99, 3]);
$missing = new WC_Product(5, '', [99]);
unset($missing->meta[Swatch_Data::META_GROUP]);
$single = new WC_Product(6, 'SINGLE', [1, 2]);
$lowercase = new WC_Product(7, 'mog-2026', [1]);
$zero_a = new WC_Product(8, '0');
$zero_b = new WC_Product(9, '0');
$blank = new WC_Product(10, " \t\n", [99]);
$invalid = new WC_Product(11, ['invalid'], [99]);
$trashed = new WC_Product(12, 'MOG-2026', [99]);
$trashed->status = 'trash';
$variation = new WC_Product(13, 'MOG-2026', [99]);
$variation->post_type = 'product_variation';
$draft = new WC_Product(14, 'DRAFT');
$draft->status = 'draft';
$private = new WC_Product(15, 'DRAFT');
$private->status = 'private';
$unloadable = new WC_Product(16, 'MOG-2026', [99]);
$unloadable->loadable = false;
$auto_draft = new WC_Product(17, 'MOG-2026', [99]);
$auto_draft->status = 'auto-draft';
$padded = new WC_Product(18, '00', [99]);
$original_metadata = $a->meta;

do_action('pmxi_after_xml_import', 1, new stdClass());
check(Swatch_Data::get_linked_product_ids($a) === [2, 3], 'The existing selector data must expose regenerated links.');
check($b->meta[Swatch_Data::META_IDS] === [1, 3] && $c->meta[Swatch_Data::META_IDS] === [1, 2], 'All peers must link symmetrically without self-links.');
check($single->meta[Swatch_Data::META_IDS] === [] && $lowercase->meta[Swatch_Data::META_IDS] === [] && $padded->meta[Swatch_Data::META_IDS] === [], 'Singletons must be cleared and groups must match exactly.');
check($zero_a->meta[Swatch_Data::META_IDS] === [9] && $zero_b->meta[Swatch_Data::META_IDS] === [8], 'Zero groups must synchronize.');
check($draft->meta[Swatch_Data::META_IDS] === [15] && $private->meta[Swatch_Data::META_IDS] === [14], 'Draft and private products must participate.');

foreach ([$manual, $missing, $blank, $invalid, $trashed, $variation, $unloadable, $auto_draft] as $untouched) {
	check($untouched->saves === 0, 'Ungrouped, invalid, or excluded products must remain untouched.');
}
foreach ($original_metadata as $key => $value) {
	if ($key !== Swatch_Data::META_IDS) {
		check($a->meta[$key] === $value, 'Synchronization must preserve all other swatch metadata.');
	}
}

$saves = array_sum(array_column($catalog, 'saves'));
do_action('pmxi_after_xml_import', 1);
check(array_sum(array_column($catalog, 'saves')) === $saves, 'Repeating an import must not save unchanged products.');

$b->meta[Swatch_Data::META_GROUP] = 'SINGLE';
do_action('pmxi_after_xml_import', 2);
check($a->meta[Swatch_Data::META_IDS] === [3] && $c->meta[Swatch_Data::META_IDS] === [1], 'Moving a product must repair its former group.');
check($b->meta[Swatch_Data::META_IDS] === [6] && $single->meta[Swatch_Data::META_IDS] === [2], 'Moving a product must regenerate its new group.');
$c->meta[Swatch_Data::META_GROUP] = '';
do_action('pmxi_after_xml_import', 3);
check($a->meta[Swatch_Data::META_IDS] === [] && $c->meta[Swatch_Data::META_IDS] === [1], 'Cleared groups must preserve their own links while former peers are repaired.');
unset($catalog[2]);
do_action('pmxi_after_xml_import', 4);
check($single->meta[Swatch_Data::META_IDS] === [], 'Deleted products must be removed from remaining group links.');

// A group crossing multiple discovery pages must be synchronized only after discovery finishes.
$catalog = [];
$queries = [];
for ($id = 1; $id <= 405; ++$id) {
	new WC_Product($id, 'LARGE');
}
do_action('pmxi_after_xml_import', 5);
check(count($queries) === 3, 'Discovery must use bounded pages without one query per group or product.');
foreach ($catalog as $id => $product) {
	$links = $product->meta[Swatch_Data::META_IDS];
	check(count($links) === 404 && ! in_array($id, $links, true) && count(array_unique($links)) === 404, 'Every page must contribute peers without duplicates or self-links.');
}

$_POST = [Swatch_Data::META_GROUP => "  <b>ADMIN-GROUP</b>\n"];
Swatch_Panel::save_product($catalog[1]);
check($catalog[1]->meta[Swatch_Data::META_GROUP] === 'LARGE', 'The panel must ignore submitted group metadata.');
$_POST = [];
Swatch_Panel::save_product($catalog[1]);
check($catalog[1]->meta[Swatch_Data::META_GROUP] === 'LARGE', 'An absent field must not clear the group.');
$_POST = [Swatch_Data::META_GROUP => ''];
Swatch_Panel::save_product($catalog[1]);
check($catalog[1]->meta[Swatch_Data::META_GROUP] === 'LARGE', 'The panel must not clear internal group metadata.');
$can_edit = false;
$_POST = [Swatch_Data::META_GROUP => 'DENIED'];
Swatch_Panel::save_product($catalog[1]);
check($catalog[1]->meta[Swatch_Data::META_GROUP] === 'LARGE', 'Denied product edits must preserve internal group metadata.');

echo "WP All Import regression checks passed.\n";
