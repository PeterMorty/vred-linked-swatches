=== VRED Linked Swatches ===
Contributors: vred
Tags: woocommerce, elementor, swatches, linked products, product options
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
WC requires at least: 8.0
WC tested up to: 10.8
Stable tag: 2.0.0
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
* Elementor widgets for product templates.
* Trigger widget with custom link support by default.
* Optional VRED Elements panel integration when VRED Elements is active.
* Local frontend assets and private updater support.

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

= Does it use remote assets or tracking? =

No. Frontend assets are local. The only remote request is the private updater while the plugin is distributed privately.

== Changelog ==

= 2.0.0 =

* Rebuilt the plugin around Elementor widgets instead of shortcodes.
* Added WooCommerce product swatch data for linked products.
* Added visual swatches with color, split color, texture image, and product thumbnail fallback.
* Added VRED Elements panel integration for the trigger widget.
* Added private updater support.
