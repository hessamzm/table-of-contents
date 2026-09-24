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
        'delete_data_on_uninstall' => false,
        'heading_levels' => [2, 3, 4, 5, 6],
        'title' => '',
        'style' => 'paper',
        'show_numbers' => false,
        'background_color' => '#ffffff',
        'text_color' => '#1d2327',
        'link_color' => '#2271b1',
        'border_color' => '#dcdcde',
        'font_size' => '16px',
        'sticky_font_size' => '14px',
        'indentation' => '1.5rem',
        'border_radius' => '0px',
        'sticky_toc' => false,
        'position' => 'right',
        'more_text' => 'View more',
        'less_text' => 'View less',
        'product_toc_enabled' => true,
        'product_toc_position' => 'inside_description',
        'post_toc' => [],
        'product_toc' => [],
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
        $settings = is_array($settings) ? wp_parse_args($settings, $this->defaults) : $this->defaults;

        $legacyProfile = $this->profileFromLegacy($settings);
        $settings['post_toc'] = wp_parse_args(
            is_array($settings['post_toc']) ? $settings['post_toc'] : [],
            $legacyProfile
        );
        $settings['product_toc'] = wp_parse_args(
            is_array($settings['product_toc']) ? $settings['product_toc'] : [],
            array_merge($legacyProfile, [
                'enabled' => !empty($settings['product_toc_enabled']),
                'position' => (string) ($settings['product_toc_position'] ?? 'inside_description'),
            ])
        );

        return $this->cached = $settings;
    }

    public function get(string $key): mixed
    {
        return $this->all()[$key] ?? null;
    }

    /** @return array<string,mixed> */
    public function getProfile(string $type): array
    {
        $key = $type === 'product' ? 'product_toc' : 'post_toc';
        $profile = $this->get($key);

        return is_array($profile) ? $profile : $this->profileFromLegacy($this->all());
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
            : $this->defaults['post_types'];
        $clean['post_types'] = array_values(array_intersect($postTypes, ['post', 'page', 'product']));

        $legacy = $this->sanitizeProfile($settings, $this->profileFromLegacy($this->defaults), false);
        $clean = array_merge($clean, $legacy);

        $postInput = isset($settings['post_toc']) && is_array($settings['post_toc'])
            ? $settings['post_toc']
            : [];
        $productInput = isset($settings['product_toc']) && is_array($settings['product_toc'])
            ? $settings['product_toc']
            : [];

        $clean['post_toc'] = $this->sanitizeProfile(
            $postInput,
            $this->profileFromLegacy($settings),
            true
        );
        $clean['product_toc'] = $this->sanitizeProfile(
            $productInput,
            array_merge($this->profileFromLegacy($settings), [
                'enabled' => !empty($settings['product_toc_enabled']),
                'position' => (string) ($settings['product_toc_position'] ?? 'inside_description'),
            ]),
            true
        );

        $clean['product_toc_enabled'] = $clean['product_toc']['enabled'];
        $clean['product_toc_position'] = $clean['product_toc']['position'];
        $clean['heading_levels'] = $clean['post_toc']['heading_levels'];
        $clean['title'] = $clean['post_toc']['title'];
        $clean['style'] = $clean['post_toc']['style'];
        $clean['show_numbers'] = $clean['post_toc']['show_numbers'];
        $clean['sticky_toc'] = $clean['post_toc']['sticky_toc'];
        $clean['position'] = $clean['post_toc']['position'];

        return $clean;
    }

    /** @param array<string,mixed> $input @param array<string,mixed> $fallback @return array<string,mixed> */
    private function sanitizeProfile(array $input, array $fallback, bool $allowPosition): array
    {
        if ($input === []) {
            return $fallback;
        }

        $levels = isset($input['heading_levels']) && is_array($input['heading_levels'])
            ? array_map('absint', $input['heading_levels'])
            : $fallback['heading_levels'];

        $style = isset($input['style']) ? sanitize_key((string) $input['style']) : $fallback['style'];
        $position = isset($input['position']) ? sanitize_key((string) $input['position']) : $fallback['position'];

        if ($position === 'before_tabs') {
            $position = 'inside_description';
        }

        $clean = [
            'enabled' => !empty($input['enabled']),
            'heading_levels' => array_values(array_unique(array_filter($levels, static fn (int $level): bool => $level >= 1 && $level <= 6))),
            'title' => isset($input['title']) ? sanitize_text_field((string) $input['title']) : $fallback['title'],
            'style' => in_array($style, ['classic', 'minimal', 'card', 'paper'], true) ? $style : $fallback['style'],
            'show_numbers' => !empty($input['show_numbers']),
            'sticky_toc' => !empty($input['sticky_toc']),
            'position' => $allowPosition
                ? (in_array($position, ['before_summary', 'inside_description', 'after_tabs'], true) ? $position : $fallback['position'])
                : (in_array($position, ['left', 'right'], true) ? $position : $fallback['position']),
            'more_text' => isset($input['more_text']) ? sanitize_text_field((string) $input['more_text']) : $fallback['more_text'],
            'less_text' => isset($input['less_text']) ? sanitize_text_field((string) $input['less_text']) : $fallback['less_text'],
        ];

        foreach (['background_color', 'text_color', 'link_color', 'border_color'] as $key) {
            $value = isset($input[$key]) ? sanitize_hex_color($input[$key]) : false;
            $clean[$key] = $value ?: $fallback[$key];
        }

        foreach (['font_size', 'sticky_font_size', 'indentation', 'border_radius'] as $key) {
            $value = isset($input[$key]) ? sanitize_text_field((string) $input[$key]) : '';
            $clean[$key] = $this->sanitize_css_length($value, (string) $fallback[$key]);
        }

        return $clean;
    }

    /** @return array<string,mixed> */
    private function profileFromLegacy(array $settings): array
    {
        return [
            'enabled' => !empty($settings['enabled']),
            'heading_levels' => $settings['heading_levels'] ?? $this->defaults['heading_levels'],
            'title' => (string) ($settings['title'] ?? ''),
            'style' => (string) ($settings['style'] ?? 'paper'),
            'show_numbers' => !empty($settings['show_numbers']),
            'background_color' => (string) ($settings['background_color'] ?? '#ffffff'),
            'text_color' => (string) ($settings['text_color'] ?? '#1d2327'),
            'link_color' => (string) ($settings['link_color'] ?? '#2271b1'),
            'border_color' => (string) ($settings['border_color'] ?? '#dcdcde'),
            'font_size' => (string) ($settings['font_size'] ?? '16px'),
            'sticky_font_size' => (string) ($settings['sticky_font_size'] ?? '14px'),
            'indentation' => (string) ($settings['indentation'] ?? '1.5rem'),
            'border_radius' => (string) ($settings['border_radius'] ?? '0px'),
            'sticky_toc' => !empty($settings['sticky_toc']),
            'position' => (string) ($settings['position'] ?? 'right'),
            'more_text' => (string) ($settings['more_text'] ?? 'View more'),
            'less_text' => (string) ($settings['less_text'] ?? 'View less'),
        ];
    }

    private function sanitize_css_length(string $value, string $fallback): string
    {
        return preg_match('/^(?:0|[1-9]\d*)(?:\.\d+)?(?:px|rem|em|%)$/', $value) ? $value : $fallback;
    }
}
