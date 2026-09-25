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

    /** @var array<string,string> */
    private const COMMON_ATTRIBUTES = [
        'levels' => '',
        'title' => '',
        'style' => '',
        'numbers' => '',
        'sticky' => '',
        'position' => '',
        'background' => '',
        'text_color' => '',
        'link_color' => '',
        'border_color' => '',
        'font_size' => '',
        'sticky_font_size' => '',
        'indentation' => '',
        'border_radius' => '',
        'more_text' => '',
        'less_text' => '',
    ];

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
        $defaults = self::COMMON_ATTRIBUTES;

        if ($tag === self::PRODUCT_TAG || $tag === self::LEGACY_TAG) {
            $defaults['placement'] = '';
        }

        $atts = shortcode_atts($defaults, $atts, $tag);
        $attributes = [];

        if ($atts['levels'] !== '') {
            $levels = array_map(
                'absint',
                preg_split('/\s*,\s*/', (string) $atts['levels']) ?: []
            );
            $attributes['headingLevels'] = array_values(
                array_unique(
                    array_filter(
                        $levels,
                        static fn (int $level): bool => $level >= 1 && $level <= 6
                    )
                )
            );
            sort($attributes['headingLevels']);
        }

        if ($atts['title'] !== '') {
            $attributes['title'] = sanitize_text_field((string) $atts['title']);
        }

        if ($atts['style'] !== '') {
            $style = sanitize_key((string) $atts['style']);
            if (in_array($style, ['classic', 'minimal', 'card', 'paper'], true)) {
                $attributes['style'] = $style;
            }
        }

        foreach ([
            'numbers' => 'showNumbers',
            'sticky' => 'stickyToc',
        ] as $attribute => $override) {
            if ($atts[$attribute] !== '') {
                $attributes[$override] = in_array(
                    strtolower((string) $atts[$attribute]),
                    ['1', 'true', 'yes', 'on'],
                    true
                );
            }
        }

        if ($atts['position'] !== '') {
            $position = sanitize_key((string) $atts['position']);
            if (in_array($position, ['left', 'right'], true)) {
                $attributes['position'] = $position;
            }
        }

        if (isset($atts['placement']) && $atts['placement'] !== '') {
            $placement = sanitize_key((string) $atts['placement']);
            if (in_array($placement, ['before_summary', 'inside_description', 'after_tabs'], true)) {
                $attributes['placement'] = $placement;
            }
        }

        foreach ([
            'background' => 'background_color',
            'text_color' => 'text_color',
            'link_color' => 'link_color',
            'border_color' => 'border_color',
        ] as $attribute => $override) {
            if ($atts[$attribute] !== '') {
                $color = sanitize_hex_color((string) $atts[$attribute]);
                if ($color) {
                    $attributes[$override] = $color;
                }
            }
        }

        foreach ([
            'font_size' => 'font_size',
            'sticky_font_size' => 'sticky_font_size',
            'indentation' => 'indentation',
            'border_radius' => 'border_radius',
        ] as $attribute => $override) {
            if ($atts[$attribute] !== '') {
                $value = sanitize_text_field((string) $atts[$attribute]);
                if (preg_match('/^(?:0|[1-9]\d*)(?:\.\d+)?(?:px|rem|em|%)$/', $value)) {
                    $attributes[$override] = $value;
                }
            }
        }

        foreach ([
            'more_text' => 'more_text',
            'less_text' => 'less_text',
        ] as $attribute => $override) {
            if ($atts[$attribute] !== '') {
                $attributes[$override] = sanitize_text_field((string) $atts[$attribute]);
            }
        }

        /**
         * Filters normalized attributes passed to the manual TOC renderer.
         *
         * @param array<string,mixed> $attributes
         * @param string $tag Registered shortcode tag.
         */
        return (array) apply_filters('hessamzm_toc/shortcode_attributes', $attributes, $tag);
    }
}
