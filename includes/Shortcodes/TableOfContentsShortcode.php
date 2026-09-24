<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Shortcodes;

use Hessamzm\TableOfContents\Services\ManualTocRenderer;
use Hessamzm\TableOfContents\Settings\Settings;

defined('ABSPATH') || exit;

final class TableOfContentsShortcode
{
    public const TAG = 'hessamzm_toc';

    public function __construct(
        private readonly ManualTocRenderer $renderer,
        private readonly Settings $settings,
    ) {
    }

    public function boot(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        add_shortcode(self::TAG, [$this, 'render']);
    }

    /**
     * @param array<string,mixed> $atts
     */
    public function render(array $atts = []): string
    {
        $defaults = [
            'levels' => '',
            'title' => '',
            'style' => '',
            'numbers' => '',
        ];

        $atts = shortcode_atts($defaults, $atts, self::TAG);

        $attributes = [];

        if ($atts['levels'] !== '') {
            $levels = array_map('absint', preg_split('/\s*,\s*/', (string) $atts['levels']) ?: []);
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
         */
        $attributes = (array) apply_filters('hessamzm_toc/shortcode_attributes', $attributes);

        return $this->renderer->render($attributes);
    }
}
