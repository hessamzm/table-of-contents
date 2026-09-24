<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Frontend;

use Hessamzm\TableOfContents\Settings\Settings;

defined('ABSPATH') || exit;

final class TocAssets
{
    private bool $enqueued = false;

    public function __construct(private readonly Settings $settings)
    {
    }

    public function enqueue(): void
    {
        if ($this->enqueued) {
            return;
        }

        wp_enqueue_style(
            'hessamzm-toc',
            HESSAMZM_TOC_URL . 'assets/css/frontend.css',
            [],
            HESSAMZM_TOC_VERSION
        );

        wp_add_inline_style('hessamzm-toc', $this->getCssVariables());

        wp_enqueue_script(
            'hessamzm-toc-frontend',
            HESSAMZM_TOC_URL . 'assets/js/frontend.js',
            [],
            HESSAMZM_TOC_VERSION,
            true
        );

        wp_set_script_translations(
            'hessamzm-toc-frontend',
            'table-of-contents',
            HESSAMZM_TOC_DIR . 'languages'
        );

        $this->enqueued = true;
    }

    private function getCssVariables(): string
    {
        $map = [
            '--hessamzm-toc-background-color' => 'background_color',
            '--hessamzm-toc-text-color' => 'text_color',
            '--hessamzm-toc-link-color' => 'link_color',
            '--hessamzm-toc-border-color' => 'border_color',
            '--hessamzm-toc-font-size' => 'font_size',
            '--hessamzm-toc-sticky-font-size' => 'sticky_font_size',
            '--hessamzm-toc-indentation' => 'indentation',
            '--hessamzm-toc-border-radius' => 'border_radius',
        ];

        $variables = [];

        foreach ($map as $property => $key) {
            $value = (string) $this->settings->get($key);
            $variables[] = $property . ':' . $value;
        }

        return '.hessamzm-toc{' . implode(';', $variables) . ';}';
    }
}
