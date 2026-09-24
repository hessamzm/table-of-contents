<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Integrations\SEO;

defined('ABSPATH') || exit;

final class SeoTocCompatibility
{
    /**
     * Detect TOC blocks supplied by supported SEO plugins.
     *
     * @param string $content Post content.
     */
    public function hasToc(string $content): bool
    {
        foreach ($this->getSupportedBlocks() as $blockName) {
            if (has_block($blockName, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int,string>
     */
    private function getSupportedBlocks(): array
    {
        $blocks = [
            'aioseo/table-of-contents',
            'rank-math/toc-block',
            'seopress/table-of-contents',
            'yoast/table-of-contents',
        ];

        $blocks = (array) apply_filters(
            'hessamzm_toc/seo_toc_blocks',
            $blocks
        );

        return array_values(
            array_filter(
                array_map('strval', $blocks),
                static fn (string $blockName): bool => (bool) preg_match('/^[a-z0-9_-]+\/[a-z0-9_-]+$/', $blockName)
            )
        );
    }
}
