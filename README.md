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
- `[hessamzm_toc]` shortcode with usage examples shown in the plugin settings.
- WordPress sidebar widget for Posts and Products, generated from the current content.
- Rank Math TOC detection compatibility.
- Classic, Minimal, Card, and Paper Menu styles.
- Three-line compact preview with configurable expand/collapse labels.
- Left/right TOC alignment configurable from settings.
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
- Numbering, sticky behavior, and left/right position.
- Custom text for the View More and View Less controls.
- Colors, font size, indentation, and border radius.
- Optional deletion of plugin settings on uninstall.

Deactivation does not delete settings or content. Permanent deletion removes the settings option only when **Delete data on uninstall** is enabled.

## Manual placement

### Gutenberg

Insert the **Table of Contents** block. The block is rendered server-side from the current post content.

### Shortcode

The plugin settings page includes copy-ready shortcode examples.

`[hessamzm_toc]`

Optional:

`[hessamzm_toc levels="2,3,4" title="Contents" style="card" numbers="true"]`

### Sidebar widget

Add the **Table of Contents** widget to any active WordPress widget area. On single Posts and Products it reads the current content and renders the TOC using the global plugin settings. On other content types it remains hidden. When the widget is active on a supported Post or Product, automatic inline TOC insertion is suppressed to prevent duplicate TOCs.

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

The repository uses a standard gettext translation pipeline:

- Source template: `languages/table-of-contents.pot`.
- Editable translations: `languages/table-of-contents-fa_IR.po` and `languages/table-of-contents-ar_AR.po`.
- Compiled compatibility files: `.mo`.
- WordPress 6.5+ runtime files: `.l10n.php`.
- PHP strings use WordPress gettext functions.
- Gutenberg strings use `wp.i18n`.
- Script translation hooks are registered for frontend and editor scripts.
- Translation generation uses WP-CLI `wp i18n` commands.

See [docs/i18n.md](docs/i18n.md) for the update and build workflow.

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
8. TOC UX and Paper Menu style — Implemented
9. TOC position, configurable labels, and Persian admin/frontend localization — Implemented; runtime validation pending
10. Live settings preview and sticky typography customization — Implemented; runtime validation pending
11. Settings shortcode guidance and Post/Product sidebar widget — Implemented; runtime validation pending

## Production validation

The remaining release gate is runtime validation in a real WordPress environment, including:

- Plugin activation/deactivation/uninstall behavior.
- Automatic rendering on posts/pages/products.
- Gutenberg block and shortcode rendering.
- Rank Math detection.
- Frontend sticky and scroll spy behavior.
- Accessibility and responsive checks.
- PHPUnit execution in the project environment.
