# Table of Contents

Production-oriented WordPress Table of Contents plugin by hessamzm.

## Requirements

- WordPress 7+
- PHP 8.2+
- WooCommerce 10+ is supported for Product post types, but WooCommerce is not a hard dependency.

## Features

- Automatic TOC for selected H1-H6 headings.
- Automatic insertion above eligible post, page, or product content.
- Existing heading IDs are preserved and duplicate anchors are made unique.
- Gutenberg dynamic block.
- `[hessamzm_toc]` shortcode.
- Rank Math TOC detection compatibility.
- Classic, Minimal, and Card styles.
- Hierarchical numbering.
- Sticky TOC on larger screens.
- IntersectionObserver scroll spy with `aria-current="location"`.
- Responsive frontend behavior and reduced-motion support.
- Settings API based administration.
- Sanitized color, CSS length, heading-level, post-type, and style settings.

## Configuration

Open **Settings > Table of Contents** to configure:

- Automatic rendering.
- Eligible post types.
- Heading levels.
- TOC title and style.
- Numbering and sticky behavior.
- Colors, font size, indentation, and border radius.
- Optional deletion of plugin settings on uninstall.

Deactivation does not delete settings or content. Permanent deletion removes the settings option only when **Delete data on uninstall** is enabled.

## Manual placement

### Gutenberg

Insert the **Table of Contents** block. The block is rendered server-side from the current post content.

### Shortcode

`[hessamzm_toc]`

Optional:

`[hessamzm_toc levels="2,3,4" title="Contents" style="card" numbers="true"]`

## Extensibility

Key filters include:

- `hessamzm_toc/should_render`
- `hessamzm_toc/post_types`
- `hessamzm_toc/heading_levels`
- `hessamzm_toc/title`
- `hessamzm_toc/container_attributes`
- `hessamzm_toc/html`
- `hessamzm_toc/shortcode_attributes`
- `hessamzm_toc/rank_math_integration_enabled`

## Development

Install development dependencies:

```bash
composer install
vendor/bin/phpunit
```

The unit suite covers heading parsing, anchor generation, tree building, and settings sanitization. Full WordPress/browser integration testing requires a WordPress test environment and is tracked separately.

## Internationalization

Text domain: `table-of-contents`

- PHP strings use WordPress gettext functions.
- Gutenberg strings use `wp.i18n`.
- Script translation hooks are registered for frontend and editor scripts.
- Translation template: `languages/table-of-contents.pot`.

## Security

The plugin uses WordPress Settings API validation/sanitization, escaped output, capability checks on the settings page, controlled CSS values, and sanitized shortcode/block inputs. No custom database tables are used.

## Project phases

1. Architecture + Core Engine — Completed
2. Automatic Rendering — Completed
3. Admin Settings and Styling System — Completed
4. Gutenberg block and shortcode — Implemented
5. Rank Math compatibility — Implemented; runtime validation pending
6. Frontend assets, sticky TOC, responsive behavior and scroll spy — Implemented; runtime validation pending
7. Security, lifecycle, translation, performance, tests and documentation — Implemented; runtime WordPress/browser validation pending

## Production validation

The remaining release gate is runtime validation in a real WordPress environment, including:

- Plugin activation/deactivation/uninstall behavior.
- Automatic rendering on posts/pages/products.
- Gutenberg block and shortcode rendering.
- Rank Math detection.
- Frontend sticky and scroll spy behavior.
- Accessibility and responsive checks.
- PHPUnit execution in the project environment.
