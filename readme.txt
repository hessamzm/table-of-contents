=== Table of Contents ===
Contributors: hessamzm
Requires at least: 7.0
Requires PHP: 8.2
Stable tag: 0.12.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatic, extensible table of contents for WordPress content.

== Description ==

Generate a table of contents from selected H1-H6 headings for posts, pages, and WooCommerce products.

== Features ==

* Automatic TOC rendering.
* Gutenberg block and dedicated Blog/Product shortcodes.
* Heading-level selection.
* Existing ID preservation and duplicate anchor handling.
* Rank Math TOC detection compatibility.
* Classic, Minimal, and Card styles.
* Numbering, sticky behavior, and scroll spy.
* Responsive frontend behavior.
* Settings API based configuration.

== Requirements ==

* WordPress 7.0 or newer.
* PHP 8.2 or newer.
* WooCommerce 10+ for Product support when WooCommerce is installed.

== Installation ==

1. Upload the plugin to `wp-content/plugins/table-of-contents`.
2. Activate it from Plugins.
3. Configure it under Settings > Table of Contents.

== Shortcodes ==

Use `[hessamzm_blog_toc]` inside blog posts or `[hessamzm_product_toc]` inside WooCommerce products.

The General settings tab includes shortcode generators for both TOC types, so users can build a ready-to-use shortcode without manually learning shortcode attributes.

The legacy `[hessamzm_toc]` shortcode remains available for backwards compatibility.

== Development ==

Run `composer install` and `vendor/bin/phpunit` for the unit test suite.

== License ==

GPLv2 or later.
