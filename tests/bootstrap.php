<?php
declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

function wp_strip_all_tags(string $text): string
{
    return trim(strip_tags($text));
}

function sanitize_title_with_dashes(string $title): string
{
    $title = strtolower(trim($title));
    $title = preg_replace('/[^a-z0-9\\x80-\\xff]+/i', '-', $title) ?? '';
    return trim($title, '-');
}

function sanitize_key(string $key): string
{
    return strtolower(preg_replace('/[^a-z0-9_\\-]/', '', $key) ?? '');
}

function absint($value): int
{
    return abs((int) $value);
}

function sanitize_text_field($value): string
{
    return trim(strip_tags((string) $value));
}

function sanitize_hex_color($color): string|false
{
    $color = (string) $color;
    return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : false;
}

function wp_parse_args($args, $defaults = []): array
{
    return array_merge($defaults, is_array($args) ? $args : []);
}

require_once dirname(__DIR__) . '/includes/Contracts/HeadingParserInterface.php';
require_once dirname(__DIR__) . '/includes/Contracts/AnchorGeneratorInterface.php';
require_once dirname(__DIR__) . '/includes/Contracts/TocBuilderInterface.php';
require_once dirname(__DIR__) . '/includes/TOC/Heading.php';
require_once dirname(__DIR__) . '/includes/TOC/HeadingNode.php';
require_once dirname(__DIR__) . '/includes/TOC/HeadingParser.php';
require_once dirname(__DIR__) . '/includes/TOC/AnchorGenerator.php';
require_once dirname(__DIR__) . '/includes/TOC/HeadingTree.php';
require_once dirname(__DIR__) . '/includes/TOC/TocBuilder.php';
require_once dirname(__DIR__) . '/includes/Settings/Settings.php';
