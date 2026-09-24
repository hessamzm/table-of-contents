<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Services;

use Hessamzm\TableOfContents\Frontend\TocRenderer;
use Hessamzm\TableOfContents\Settings\Settings;
use Hessamzm\TableOfContents\Frontend\TocAssets;
use Hessamzm\TableOfContents\TOC\ContentProcessor;

defined('ABSPATH') || exit;

final class ManualTocRenderer
{
    public function __construct(
        private readonly ContentProcessor $processor,
        private readonly TocRenderer $tocRenderer,
        private readonly Settings $settings,
        private readonly TocAssets $assets,
    ) {
    }

    /**
     * @param array<string,mixed> $attributes
     */
    public function render(array $attributes = []): string
    {
        $this->assets->enqueue();

        $postId = get_the_ID();

        if (!$postId) {
            return '';
        }

        $content = (string) get_post_field('post_content', $postId, 'raw');

        if ($content === '') {
            return '';
        }

        $levels = $this->resolveLevels($attributes);
        $processed = $this->processor->process($content, $levels);

        if ($processed['tree']->isEmpty()) {
            return '';
        }

        $overrides = $this->resolveRendererOverrides($attributes);

        return '<div class="hessamzm-toc-manual">' . $this->tocRenderer->render(
            $processed['tree'],
            $overrides
        ) . '</div>';
    }

    /**
     * @param array<string,mixed> $attributes
     * @return list<int>
     */
    private function resolveLevels(array $attributes): array
    {
        $levels = isset($attributes['headingLevels']) && is_array($attributes['headingLevels'])
            ? array_map('absint', $attributes['headingLevels'])
            : [];

        if ($levels === []) {
            $levels = (array) $this->settings->get('heading_levels');
        }

        $levels = array_values(
            array_unique(
                array_filter(
                    $levels,
                    static fn (int $level): bool => $level >= 1 && $level <= 6
                )
            )
        );

        sort($levels);

        return $levels;
    }

    /**
     * @param array<string,mixed> $attributes
     * @return array<string,mixed>
     */
    private function resolveRendererOverrides(array $attributes): array
    {
        $overrides = [];

        if (array_key_exists('title', $attributes)) {
            $overrides['title'] = sanitize_text_field((string) $attributes['title']);
        }

        if (isset($attributes['style'])) {
            $style = sanitize_key((string) $attributes['style']);

            if (in_array($style, ['classic', 'minimal', 'card'], true)) {
                $overrides['style'] = $style;
            }
        }

        if (array_key_exists('showNumbers', $attributes)) {
            $overrides['show_numbers'] = (bool) $attributes['showNumbers'];
        }

        return $overrides;
    }
}
