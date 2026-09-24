=== Table of Contents ===
Contributors: hessamzm
Requires at least: 7.0
Requires PHP: 8.2
Stable tag: 0.7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatic, extensible table of contents for WordPress content.

== Description ==

Generate a table of contents from selected H1-H6 headings for posts, pages, and WooCommerce products.

== Features ==

* Automatic TOC rendering.
* Gutenberg block and shortcode.
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

== Shortcode ==

Use `[hessamzm_toc]`.

== Development ==

Run `composer install` and `vendor/bin/phpunit` for the unit test suite.

== License ==

GPLv2 or later.
