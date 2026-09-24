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
    public function render(array $attributes = [], string $profileType = 'post'): string
    {
        $profileType = in_array($profileType, ['post', 'product'], true) ? $profileType : 'post';
        $postId = get_the_ID();

        if (!$postId || !is_singular()) {
            return '';
        }

        if ($profileType === 'product' && get_post_type($postId) !== 'product') {
            return '';
        }

        if ($profileType === 'post' && get_post_type($postId) !== 'post') {
            return '';
        }

        $profile = $this->settings->getProfile($profileType);

        if (empty($profile['enabled'])) {
            return '';
        }

        $content = (string) get_post_field('post_content', $postId, 'raw');

        if ($content === '') {
            return '';
        }

        $this->assets->enqueue($profile);

        $levels = $this->resolveLevels($attributes, $profile);
        $processed = $this->processor->process($content, $levels);

        if ($processed['tree']->isEmpty()) {
            return '';
        }

        $overrides = array_merge($profile, $this->resolveRendererOverrides($attributes));
        $overrides['profile'] = $profileType;

        return '<div class="hessamzm-toc-manual hessamzm-toc-manual--' . esc_attr($profileType) . '">' .
            $this->tocRenderer->render($processed['tree'], $overrides) .
            '</div>';
    }

    /**
     * @param array<string,mixed> $attributes
     * @param array<string,mixed> $profile
     * @return list<int>
     */
    private function resolveLevels(array $attributes, array $profile): array
    {
        $levels = isset($attributes['headingLevels']) && is_array($attributes['headingLevels'])
            ? array_map('absint', $attributes['headingLevels'])
            : [];

        if ($levels === []) {
            $levels = (array) ($profile['heading_levels'] ?? []);
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

            if (in_array($style, ['classic', 'minimal', 'card', 'paper'], true)) {
                $overrides['style'] = $style;
            }
        }

        if (array_key_exists('showNumbers', $attributes)) {
            $overrides['show_numbers'] = (bool) $attributes['showNumbers'];
        }

        return $overrides;
    }
}
