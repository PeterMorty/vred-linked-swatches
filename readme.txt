=== VRED Linked Swatches ===
Contributors: vred
Tags: woocommerce, elementor, swatches, linked products, product options
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
WC requires at least: 8.0
WC tested up to: 10.8
Stable tag: 2.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect independent WooCommerce products as visual linked swatches for Elementor product templates.

== Description ==

VRED Linked Swatches connects independent WooCommerce products and displays them as visual swatches.

It is useful when each color, finish, texture, material, or option needs its own product page, images, SEO URL, stock, and price, while still keeping a simple way to navigate between related options.

Current features:

* WooCommerce product data panel for linked products, optional display name, texture image, and colors.
* Split swatches with primary and secondary colors.
* Texture/image swatches for fabrics, materials, and finishes.
* Trigger fallback to the product thumbnail when no texture or color is configured.
* Elementor widgets for product templates, with optional names in Linked Swatches.
* Trigger widget with custom link support by default.
* Optional VRED Elements panel integration when VRED Elements is active.
* Local frontend assets and private updater support.
* Optional compact swatches in WooCommerce product loops through a filter.
* Optional product groups synchronized automatically after WP All Import imports.

== Installation ==

1. Upload the ZIP file from Plugins > Add New > Upload Plugin.
2. Make sure WooCommerce is active.
3. Activate VRED Linked Swatches.
4. Edit a product and open the Linked Swatches product data tab.
5. Select linked products and optionally add swatch data.
6. Add the Linked Swatch Trigger or Linked Swatches widget to an Elementor product template.

== Frequently Asked Questions ==

= Does this replace WooCommerce variations? =

No. This plugin links independent products visually. It is intended for stores that prefer one product per color, finish, material, or option.

= Does it require Elementor? =

Elementor is required for the included frontend widgets. Without Elementor, the WooCommerce product data panel remains available, but frontend widgets are not registered.

= Does it require Elementor Pro? =

No.

= Does it require VRED Elements? =

No. VRED Elements is optional. When it is active, the trigger widget can open a VRED Elements panel.

= How do I show swatches in WooCommerce product loops? =

Loop swatches are disabled by default. Enable them from your child theme, Code Snippets, or a small custom plugin:

    add_filter('vred_linked_swatches_show_loop_swatches', '__return_true');

To limit them to product archive contexts:

    add_filter('vred_linked_swatches_show_loop_swatches', function ($show, $product) {
    	unset($show, $product);
    
    	return is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy();
    }, 10, 2);

By default, the plugin renders compact swatches after the loop price using the `woocommerce_after_shop_loop_item_title` hook.

= Does it use remote assets or tracking? =

No. Frontend assets are local. The only remote request is the private updater while the plugin is distributed privately.

= Can I import Linked Swatches with WP All Import? =

Yes. WP All Import is optional; without it, VRED Linked Swatches works normally. Map `_vred_linked_swatches_group`, `_vred_linked_swatches_name`, `_vred_linked_swatches_color` and the optional `_vred_linked_swatches_secondary_color` as product custom fields.

Do not import `_vred_linked_swatches_ids`. Only when an import completes (`pmxi_after_xml_import`), the plugin rebuilds links between products sharing a non-empty group, removes obsolete links and excludes duplicates and the product itself. Products without a group keep their existing manual links.

See `docs/data/vred-linked-swatches.json` and `docs/index.html` in the repository for mapping examples, reimport behavior and limitations. The documentation is not included in the plugin ZIP.

== Changelog ==

= 2.3.0 =

* Added a Show name control to hide option names in the Linked Swatches widget.
* Swatch mode without product images now skips linked products without a configured color or texture.
* Avoided empty items and metadata blocks when names or product images are hidden.
* Added a documentation site with widget guides and WP All Import mapping examples.

= 2.2.1 =

* Removed the internal Linked group field from the WooCommerce product UI.
* Linked group metadata remains available internally for WP All Import synchronization.
* Updated tests to reflect the hidden group metadata behavior.

= 2.2.0 =

* Added `_vred_linked_swatches_group` and the optional Linked group product field.
* Added regenerative group linking after WP All Import completes, preserving manual links on products without a group.

= 2.1.0 =

* Added optional compact swatches in WooCommerce product loops.
* Added filter-based activation for loop swatches.

= 2.0.0 =

* Rebuilt the plugin around Elementor widgets instead of shortcodes.
* Added WooCommerce product swatch data for linked products.
* Added visual swatches with color, split color, texture image, and product thumbnail fallback.
* Added VRED Elements panel integration for the trigger widget.
* Added private updater support.
