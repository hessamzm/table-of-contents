<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Frontend;

use Hessamzm\TableOfContents\Settings\Settings;
use Hessamzm\TableOfContents\TOC\HeadingNode;
use Hessamzm\TableOfContents\TOC\HeadingTree;

defined('ABSPATH') || exit;

final class TocRenderer
{
    public function __construct(private readonly Settings $settings)
    {
    }

    public function render(HeadingTree $tree, array $overrides = []): string
    {
        if ($tree->isEmpty()) {
            return '';
        }

        $title = array_key_exists('title', $overrides)
            ? sanitize_text_field((string) $overrides['title'])
            : (string) $this->settings->get('title');

        if ($title === '') {
            $title = __('Table of Contents', 'table-of-contents');
        }

        $title = (string) apply_filters('hessamzm_toc/title', $title);

        $style = sanitize_html_class((string) ($overrides['style'] ?? $this->settings->get('style')));
        $numbered = array_key_exists('show_numbers', $overrides)
            ? (bool) $overrides['show_numbers']
            : (bool) $this->settings->get('show_numbers');
        $classes = [
            'hessamzm-toc',
            'hessamzm-toc--' . ($style ?: 'classic'),
        ];

        if ($numbered) {
            $classes[] = 'hessamzm-toc--numbered';
        }

        $attributes = (string) apply_filters(
            'hessamzm_toc/container_attributes',
            ''
        );

        $html = '<nav class="' . esc_attr(implode(' ', $classes)) . '"' . ($attributes !== '' ? ' ' . wp_kses_post($attributes) : '') . ' aria-label="' . esc_attr($title) . '">';
        $html .= '<p class="hessamzm-toc__title">' . esc_html($title) . '</p>';
        $html .= '<ol class="hessamzm-toc__list">';

        foreach ($tree->roots() as $node) {
            $html .= $this->renderNode($node);
        }

        $html .= '</ol>';
        $html .= '</nav>';

        return (string) apply_filters('hessamzm_toc/html', $html, $tree);
    }

    private function renderNode(HeadingNode $node): string
    {
        $heading = $node->heading();
        $id = $heading->id();

        if ($id === null) {
            return '';
        }

        $html = '<li class="hessamzm-toc__item hessamzm-toc__item--level-' . absint($heading->level()) . '">';
        $html .= '<a class="hessamzm-toc__link" href="#' . esc_attr($id) . '">';
        $html .= esc_html($heading->text());
        $html .= '</a>';

        $children = $node->children();

        if ($children !== []) {
            $html .= '<ol class="hessamzm-toc__children">';

            foreach ($children as $child) {
                $html .= $this->renderNode($child);
            }

            $html .= '</ol>';
        }

        $html .= '</li>';

        return $html;
    }
}
