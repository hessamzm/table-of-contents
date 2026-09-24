<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Shortcodes;

use Hessamzm\TableOfContents\Services\ManualTocRenderer;

defined('ABSPATH') || exit;

final class TableOfContentsShortcode
{
    public const BLOG_TAG = 'hessamzm_blog_toc';
    public const PRODUCT_TAG = 'hessamzm_product_toc';
    public const LEGACY_TAG = 'hessamzm_toc';

    public function __construct(private readonly ManualTocRenderer $renderer)
    {
    }

    public function boot(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        add_shortcode(self::BLOG_TAG, [$this, 'renderBlog']);
        add_shortcode(self::PRODUCT_TAG, [$this, 'renderProduct']);
        add_shortcode(self::LEGACY_TAG, [$this, 'renderLegacy']);
    }

    /** @param array<string,mixed> $atts */
    public function renderBlog(array $atts = []): string
    {
        if (!is_singular('post')) {
            return '';
        }

        return $this->renderer->render($this->normalizeAttributes($atts, self::BLOG_TAG), 'post');
    }

    /** @param array<string,mixed> $atts */
    public function renderProduct(array $atts = []): string
    {
        if (!is_singular('product') || !class_exists('WooCommerce')) {
            return '';
        }

        return $this->renderer->render($this->normalizeAttributes($atts, self::PRODUCT_TAG), 'product');
    }

    /** @param array<string,mixed> $atts */
    public function renderLegacy(array $atts = []): string
    {
        if (!is_singular()) {
            return '';
        }

        $profile = get_post_type() === 'product' ? 'product' : 'post';

        return $this->renderer->render($this->normalizeAttributes($atts, self::LEGACY_TAG), $profile);
    }

    /** @param array<string,mixed> $atts @return array<string,mixed> */
    private function normalizeAttributes(array $atts, string $tag): array
    {
        $atts = shortcode_atts([
            'levels' => '',
            'title' => '',
            'style' => '',
            'numbers' => '',
        ], $atts, $tag);

        $attributes = [];

        if ($atts['levels'] !== '') {
            $levels = array_map(
                'absint',
                preg_split('/\s*,\s*/', (string) $atts['levels']) ?: []
            );
            $attributes['headingLevels'] = $levels;
        }

        if ($atts['title'] !== '') {
            $attributes['title'] = sanitize_text_field((string) $atts['title']);
        }

        if ($atts['style'] !== '') {
            $attributes['style'] = sanitize_key((string) $atts['style']);
        }

        if ($atts['numbers'] !== '') {
            $attributes['showNumbers'] = in_array(
                strtolower((string) $atts['numbers']),
                ['1', 'true', 'yes'],
                true
            );
        }

        /**
         * Filters attributes passed to the manual TOC renderer.
         *
         * @param array<string,mixed> $attributes
         * @param string $tag Registered shortcode tag.
         */
        return (array) apply_filters('hessamzm_toc/shortcode_attributes', $attributes, $tag);
    }
}
