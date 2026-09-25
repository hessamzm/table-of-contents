# Table of Contents

Production-oriented WordPress Table of Contents plugin by hessamzm.

> **Documentation:** [فارسی](readme-fa.md) | [العربية](readme-ar.md)

## 1. Plugin Introduction

**Table of Contents** is a lightweight, extensible WordPress plugin for automatically generating a table of contents from headings in your content.

It supports independent TOC configuration for blog articles and WooCommerce products, with customization for heading levels, title, style, numbering, colors, typography, spacing, sticky behavior, and placement.

Key features:

- Automatic table of contents from H1-H6 headings.
- Independent TOC settings for blog articles and WooCommerce products.
- Support for posts, pages, and WooCommerce products.
- Custom heading levels, title, style, and numbering.
- Sticky TOC and scroll spy support.
- Responsive frontend behavior for desktop, tablet, and mobile.
- Gutenberg Block support.
- Dedicated Blog and Product shortcodes for manual placement.
- Shortcode generator with extensive customization options.
- Rank Math compatibility and duplicate TOC prevention in supported scenarios.
- Preservation of existing heading IDs and unique handling of duplicate anchors.
- Persian and English plugin interface support.
- WooCommerce HPOS-compatible architecture.

## 2. Requirements

To use version 1.0.0:

- **WordPress:** 7.0 or higher
- **PHP:** 8.2 or higher
- **WooCommerce:** 10.0 or higher for WooCommerce product features

WooCommerce is optional unless you want to use the Product TOC features.

## 3. Installation

### Install from the WordPress dashboard

1. Download the plugin ZIP file.
2. Log in to your WordPress admin dashboard.
3. Go to **Plugins > Add New Plugin**.
4. Select **Upload Plugin**.
5. Choose the plugin ZIP file and install it.
6. Activate **Table of Contents**.
7. Open **Settings > Table of Contents** to configure the plugin.

### Manual installation

1. Extract the plugin ZIP file.
2. Upload the plugin folder to:

`wp-content/plugins/table-of-contents/`

3. Open the WordPress admin dashboard.
4. Go to **Plugins**.
5. Activate **Table of Contents**.
6. Open **Settings > Table of Contents**.

## 4. Simple Overview

After activation, the plugin provides a dedicated settings page where you can configure the TOC behavior for different content types.

### Articles

The **Articles** section controls the TOC used for blog posts. You can configure heading levels, title, style, numbering, colors, typography, spacing, expand/collapse text, and sticky behavior independently from products.

### Products

The **Products** section controls the TOC used for WooCommerce products. Product TOC settings are independent from the article TOC, and the placement of the TOC can also be configured for product pages.

### Shortcodes

For manual placement, use the dedicated shortcodes:

`[hessamzm_blog_toc]`

`[hessamzm_product_toc]`

The plugin includes a shortcode generator in the settings page, so you can build a shortcode without memorizing its attributes.

Shortcodes can customize heading levels, title, style, numbering, sticky behavior, alignment or placement, colors, typography, indentation, border radius, and expand/collapse labels.

To avoid accidentally displaying two TOCs for the same content type, the shortcode generator is disabled for a content type when its automatic TOC is enabled.

### Gutenberg

The **Table of Contents** Gutenberg block can be used to insert a TOC manually in supported content.

### Widget

The **Table of Contents** widget can be added to an active WordPress widget area. On supported single content pages, it generates the TOC from the current content and prevents duplicate automatic insertion.

## Translation

The plugin interface is translation-ready and currently includes Persian and English translations.

## Compatibility

The plugin uses standard WordPress APIs and is designed for modern WordPress sites.

WooCommerce product functionality supports WooCommerce 10+ and is designed with HPOS compatibility in mind.

## License

This plugin is released under the **GPLv2 or later** license.

Copyright © hessamzm
