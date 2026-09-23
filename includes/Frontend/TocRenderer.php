<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Frontend;

use Hessamzm\TableOfContents\TOC\HeadingNode;
use Hessamzm\TableOfContents\TOC\HeadingTree;

defined('ABSPATH') || exit;

final class TocRenderer
{
    public function render(HeadingTree $tree): string
    {
        if ($tree->isEmpty()) {
            return '';
        }

        $title = (string) apply_filters(
            'hessamzm_toc/title',
            __('Table of Contents', 'table-of-contents')
        );

        $attributes = (string) apply_filters(
            'hessamzm_toc/container_attributes',
            'class="hessamzm-toc"'
        );

        $html = '<nav ' . wp_kses_post($attributes) . ' aria-label="' . esc_attr($title) . '">';
        $html .= '<p class="hessamzm-toc__title">' . esc_html($title) . '</p>';
        $html .= '<ol class="hessamzm-toc__list">';

        foreach ($tree->roots() as $node) {
            $html .= $this->renderNode($node);
        }

        $html .= '</ol>';
        $html .= '</nav>';

        return $html;
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
