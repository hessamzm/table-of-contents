<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Frontend;

use Hessamzm\TableOfContents\Settings\Settings;
use Hessamzm\TableOfContents\Widgets\TableOfContentsWidget;
use Hessamzm\TableOfContents\TOC\ContentProcessor;
use Hessamzm\TableOfContents\Integrations\SEO\SeoTocCompatibility;

defined('ABSPATH') || exit;

final class AutomaticRenderer
{
    private bool $rendering = false;

    public function __construct(
        private readonly ContentProcessor $processor,
        private readonly TocRenderer $tocRenderer,
        private readonly Settings $settings,
        private readonly TocAssets $assets,
        private readonly SeoTocCompatibility $seoTocCompatibility,
    ) {
    }

    public function boot(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_filter('the_content', [$this, 'filterContent'], 20);
    }

    public function enqueueAssets(): void
    {
        if (!$this->isEnabled() || !$this->shouldRender()) {
            return;
        }

        $this->assets->enqueue();
    }

    public function filterContent(string $content): string
    {
        if (!$this->isEnabled() || !$this->shouldRender()) {
            return $content;
        }

        if ($this->rendering) {
            return $content;
        }

        $this->assets->enqueue();
        $this->rendering = true;

        try {
            $levels = $this->getLevels();
            $processed = $this->processor->process($content, $levels);

            if ($processed['tree']->isEmpty()) {
                return $content;
            }

            if ($this->hasManualToc($content) || $this->hasActiveTocWidget()) {
                return $processed['content'];
            }

            $toc = $this->tocRenderer->render($processed['tree']);

            if ($toc === '') {
                return $content;
            }

            return $toc . $processed['content'];
        } finally {
            $this->rendering = false;
        }
    }

    private function hasManualToc(string $content): bool
    {
        return str_contains($content, 'hessamzm-toc-manual')
            || has_block('hessamzm/table-of-contents', $content)
            || has_shortcode($content, 'hessamzm_toc')
            || $this->seoTocCompatibility->hasToc($content);
    }

    private function hasActiveTocWidget(): bool
    {
        if (!in_array(get_post_type(), ['post', 'product'], true)) {
            return false;
        }

        return TableOfContentsWidget::isActive();
    }

    private function isEnabled(): bool
    {
        return (bool) $this->settings->get('enabled');
    }

    private function getLevels(): array
    {
        $levels = (array) apply_filters(
            'hessamzm_toc/heading_levels',
            (array) $this->settings->get('heading_levels')
        );

        return array_values(
            array_filter(
                array_map('intval', $levels),
                static fn (int $level): bool => $level >= 1 && $level <= 6
            )
        );
    }

    private function shouldRender(): bool
    {
        if (is_admin() || is_feed() || !is_singular() || !in_the_loop() || !is_main_query()) {
            return false;
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            return false;
        }

        if (post_password_required()) {
            return false;
        }

        $postType = get_post_type();

        $postTypes = (array) apply_filters(
            'hessamzm_toc/post_types',
            (array) $this->settings->get('post_types')
        );

        if (!in_array($postType, $postTypes, true)) {
            return false;
        }

        return (bool) apply_filters(
            'hessamzm_toc/should_render',
            true,
            get_the_ID(),
            $postType
        );
    }
}
