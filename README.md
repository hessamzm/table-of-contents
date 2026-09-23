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

Implemented:

- Automatic rendering through the `the_content` filter.
- Main-loop and main-query safeguards.
- Posts, Pages and WooCommerce Product post type support without a WooCommerce hard dependency.
- TOC insertion above the processed content.
- Automatic anchor injection for selected headings.
- Existing IDs are preserved unless uniqueness requires a generated suffix.
- Frontend stylesheet loaded only on eligible singular content.
- Extensible render decision and title/container filters.

## Requirements

- WordPress 7+
- PHP 8.2+

## Planned phases

1. Architecture + Core Engine — Completed
2. Automatic Rendering — Completed
3. Admin settings and styling system
4. Gutenberg block and shortcode
5. Rank Math and compatibility integrations
6. Frontend assets, sticky TOC, responsive behavior and scroll spy
7. Security, translation, performance, tests and documentation
