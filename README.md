# Table of Contents

Production-oriented WordPress Table of Contents plugin by hessamzm.

## Phase 1 — Architecture + Core Engine

This phase establishes the core domain model and services:

- Heading parsing for H1–H6.
- Configurable heading-level selection.
- Existing heading ID preservation.
- Deterministic unique anchor generation.
- Heading collection/tree boundary for future renderers.
- Contract-based services for extensibility.
- WordPress-safe bootstrap and prefixed hooks.

## Requirements

- WordPress 7+
- PHP 8.2+

## Planned phases

1. Architecture + Core Engine
2. Automatic rendering for Posts, Pages and WooCommerce Products
3. Admin settings and styling system
4. Gutenberg block and shortcode
5. Rank Math and compatibility integrations
6. Frontend assets, sticky TOC, responsive behavior and scroll spy
7. Security, translation, performance, tests and documentation
