<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Frontend;

use Hessamzm\TableOfContents\TOC\ContentProcessor;

defined('ABSPATH') || exit;

final class AutomaticRenderer
{
    private bool $rendering = false;

    public function __construct(
        private readonly ContentProcessor $processor,
        private readonly TocRenderer $tocRenderer,
    ) {
    }

    public function boot(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_filter('the_content', [$this, 'filterContent'], 20);
    }

    public function enqueueAssets(): void
    {
        if (!$this->shouldRender()) {
            return;
        }

        wp_enqueue_style(
            'hessamzm-toc',
            HESSAMZM_TOC_URL . 'assets/css/frontend.css',
            [],
            HESSAMZM_TOC_VERSION
        );
    }

    public function filterContent(string $content): string
    {
        if (!$this->shouldRender()) {
            return $content;
        }

        if ($this->rendering) {
            return $content;
        }

        $this->rendering = true;

        try {
            $processed = $this->processor->process($content);

            if ($processed['tree']->isEmpty()) {
                return $content;
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

        if (!in_array($postType, ['post', 'page', 'product'], true)) {
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
