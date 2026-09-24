# Table of Contents

Production-oriented WordPress Table of Contents plugin by hessamzm.

## Phase 1 — Architecture + Core Engine

Completed:

- Heading parsing for H1–H6.
- Configurable heading-level selection.
- Existing heading ID preservation.
- Deterministic unique anchor generation.
- Heading collection/tree boundary for future renderers.
- Contract-based services for extensibility.
- WordPress-safe bootstrap and prefixed hooks.

## Phase 2 — Automatic Rendering

Completed:

- Automatic rendering through the `the_content` filter.
- Main-loop and main-query safeguards.
- Posts, Pages and WooCommerce Product post type support without a WooCommerce hard dependency.
- TOC insertion above the processed content.
- Automatic anchor injection for selected headings.
- Existing IDs are preserved unless uniqueness requires a generated suffix.
- Frontend stylesheet loaded only on eligible singular content.
- Extensible render decision and title/container filters.

## Phase 3 — Admin Settings & Styling System

Implemented:

- WordPress Settings API based settings page under Settings > Table of Contents.
- Enable/disable automatic rendering.
- Select eligible Post, Page and Product post types.
- Select H1–H6 heading levels.
- Global TOC title.
- Classic, Minimal and Card styles.
- Hierarchical numbering option.
- Background, text, link and border colors.
- Font size, indentation and border radius.
- Sanitization for colors and CSS lengths.
- Frontend CSS variables generated from sanitized settings.
- Settings-aware automatic renderer and TOC renderer.

## Phase 4 — Gutenberg Block + Shortcode

Implemented:

- Dynamic Gutenberg block registered from `block.json` metadata.
- Block Editor controls for title, style, numbering and H1–H6 selection.
- Server-side block rendering using the shared TOC processing pipeline.
- `[hessamzm_toc]` shortcode with optional `levels`, `title`, `style` and `numbers` attributes.
- Manual TOC marker prevents duplicate automatic TOC insertion while still allowing automatic anchor injection.
- Shared manual rendering service keeps block and shortcode behavior consistent.
- Frontend CSS variables scoped to the TOC component instead of `:root`.

## Requirements

- WordPress 7+
- PHP 8.2+

## Planned phases

1. Architecture + Core Engine — Completed
2. Automatic Rendering — Completed
3. Admin Settings and Styling System — In Progress
4. Gutenberg block and shortcode
5. Rank Math and compatibility integrations
6. Frontend assets, sticky TOC, responsive behavior and scroll spy
7. Security, translation, performance, tests and documentation
