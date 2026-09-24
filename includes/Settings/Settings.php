<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Settings;

defined('ABSPATH') || exit;

final class Settings
{
    public const OPTION_KEY = 'hessamzm_toc_settings';

    /** @var array<string,mixed> */
    private array $defaults = [
        'enabled' => true,
        'post_types' => ['post', 'page', 'product'],
        'heading_levels' => [2, 3, 4, 5, 6],
        'title' => '',
        'style' => 'classic',
        'show_numbers' => false,
        'background_color' => '#ffffff',
        'text_color' => '#1d2327',
        'link_color' => '#2271b1',
        'border_color' => '#dcdcde',
        'font_size' => '16px',
        'indentation' => '1.5rem',
        'border_radius' => '0px',
        'sticky_toc' => false,
        'delete_data_on_uninstall' => false,
    ];

    /** @var array<string,mixed>|null */
    private ?array $cached = null;

    /** @return array<string,mixed> */
    public function all(): array
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        $settings = get_option(self::OPTION_KEY, []);

        if (!is_array($settings)) {
            $settings = [];
        }

        return $this->cached = wp_parse_args($settings, $this->defaults);
    }

    public function get(string $key): mixed
    {
        return $this->all()[$key] ?? null;
    }

    /** @return array<string,mixed> */
    public function defaults(): array
    {
        return $this->defaults;
    }

    /** @param array<string,mixed> $settings @return array<string,mixed> */
    public function sanitize(array $settings): array
    {
        $clean = $this->defaults;
        $clean['enabled'] = !empty($settings['enabled']);
        $clean['delete_data_on_uninstall'] = !empty($settings['delete_data_on_uninstall']);

        $postTypes = isset($settings['post_types']) && is_array($settings['post_types'])
            ? array_map('sanitize_key', $settings['post_types'])
            : [];
        $clean['post_types'] = array_values(array_intersect($postTypes, ['post', 'page', 'product']));

        $levels = isset($settings['heading_levels']) && is_array($settings['heading_levels'])
            ? array_map('absint', $settings['heading_levels'])
            : [];
        $clean['heading_levels'] = array_values(array_unique(array_filter(
            $levels,
            static fn (int $level): bool => $level >= 1 && $level <= 6
        )));

        $clean['title'] = isset($settings['title'])
            ? sanitize_text_field($settings['title'])
            : $this->defaults['title'];

        $styles = ['classic', 'minimal', 'card'];
        $style = isset($settings['style']) ? sanitize_key($settings['style']) : 'classic';
        $clean['style'] = in_array($style, $styles, true) ? $style : 'classic';

        $clean['show_numbers'] = !empty($settings['show_numbers']);
        $clean['sticky_toc'] = !empty($settings['sticky_toc']);

        foreach (['background_color', 'text_color', 'link_color', 'border_color'] as $key) {
            $value = isset($settings[$key]) ? sanitize_hex_color($settings[$key]) : false;
            $clean[$key] = $value ?: $this->defaults[$key];
        }

        foreach (['font_size', 'indentation', 'border_radius'] as $key) {
            $value = isset($settings[$key]) ? sanitize_text_field($settings[$key]) : '';
            $clean[$key] = $this->sanitize_css_length($value, (string) $this->defaults[$key]);
        }

        return $clean;
    }

    private function sanitize_css_length(string $value, string $fallback): string
    {
        if (preg_match('/^(?:0|[1-9]\d*)(?:\.\d+)?(?:px|rem|em|%)$/', $value)) {
            return $value;
        }

        return $fallback;
    }
}
