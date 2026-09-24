<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Admin;

use Hessamzm\TableOfContents\Settings\Settings;

defined('ABSPATH') || exit;

final class SettingsPage
{
    private const PAGE_SLUG = 'hessamzm-toc';

    public function __construct(private readonly Settings $settings)
    {
    }

    public function boot(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function registerMenu(): void
    {
        add_options_page(
            __('Table of Contents', 'table-of-contents'),
            __('Table of Contents', 'table-of-contents'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render']
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            'hessamzm_toc',
            Settings::OPTION_KEY,
            [
                'type' => 'array',
                'sanitize_callback' => [$this->settings, 'sanitize'],
                'default' => $this->settings->defaults(),
            ]
        );

        add_settings_section(
            'hessamzm_toc_general',
            __('General', 'table-of-contents'),
            [$this, 'renderGeneralDescription'],
            self::PAGE_SLUG
        );

        $this->addCheckbox(
            'enabled',
            __('Enable automatic TOC', 'table-of-contents'),
            __('Automatically add the table of contents to eligible content.', 'table-of-contents')
        );

        $this->addCheckboxGroup(
            'post_types',
            __('Post types', 'table-of-contents'),
            [
                'post' => __('Posts', 'table-of-contents'),
                'page' => __('Pages', 'table-of-contents'),
                'product' => __('Products', 'table-of-contents'),
            ]
        );

        $this->addCheckboxGroup(
            'heading_levels',
            __('Heading levels', 'table-of-contents'),
            array_combine(
                range(1, 6),
                array_map(
                    static fn (int $level): string => sprintf(
                        /* translators: %d: heading level */
                        __('Heading %d', 'table-of-contents'),
                        $level
                    ),
                    range(1, 6)
                )
            )
        );

        add_settings_section(
            'hessamzm_toc_product',
            __('Product TOC', 'table-of-contents'),
            [$this, 'renderProductDescription'],
            self::PAGE_SLUG
        );

        $this->addCheckbox(
            'product_toc_enabled',
            __('Enable Product TOC', 'table-of-contents'),
            __('Generate a dedicated table of contents for WooCommerce product descriptions.', 'table-of-contents'),
            'hessamzm_toc_product'
        );

        add_settings_field(
            'product_toc_position',
            __('Product TOC position', 'table-of-contents'),
            [$this, 'renderProductPositionField'],
            self::PAGE_SLUG,
            'hessamzm_toc_product'
        );

        add_settings_section(
            'hessamzm_toc_style',
            __('Appearance', 'table-of-contents'),
            '__return_false',
            self::PAGE_SLUG
        );

        add_settings_field(
            'style',
            __('Style', 'table-of-contents'),
            [$this, 'renderStyleField'],
            self::PAGE_SLUG,
            'hessamzm_toc_style'
        );

        $this->addTextField('title', __('Title', 'table-of-contents'));
        $this->addCheckbox('show_numbers', __('Show numbers', 'table-of-contents'), __('Prefix TOC items with hierarchical numbers.', 'table-of-contents'), 'hessamzm_toc_style');
        $this->addCheckbox('sticky_toc', __('Sticky TOC', 'table-of-contents'), __('Keep the TOC visible while scrolling on larger screens.', 'table-of-contents'), 'hessamzm_toc_style');

        add_settings_field(
            'position',
            __('TOC position', 'table-of-contents'),
            [$this, 'renderPositionField'],
            self::PAGE_SLUG,
            'hessamzm_toc_style'
        );

        $this->addTextField('more_text', __('View more text', 'table-of-contents'));
        $this->addTextField('less_text', __('View less text', 'table-of-contents'));
        $this->addCheckbox('delete_data_on_uninstall', __('Delete data on uninstall', 'table-of-contents'), __('Delete plugin settings when the plugin is permanently uninstalled. This does not affect post content.', 'table-of-contents'), 'hessamzm_toc_style');

        foreach ([
            'background_color' => __('Background color', 'table-of-contents'),
            'text_color' => __('Text color', 'table-of-contents'),
            'link_color' => __('Link color', 'table-of-contents'),
            'border_color' => __('Border color', 'table-of-contents'),
        ] as $key => $label) {
            add_settings_field(
                $key,
                $label,
                [$this, 'renderColorField'],
                self::PAGE_SLUG,
                'hessamzm_toc_style',
                ['key' => $key]
            );
        }

        $this->addTextField('font_size', __('Font size', 'table-of-contents'), 'css-length');
        $this->addTextField('sticky_font_size', __('Sticky TOC font size', 'table-of-contents'), 'css-length');
        $this->addTextField('indentation', __('Indentation', 'table-of-contents'), 'css-length');
        $this->addTextField('border_radius', __('Border radius', 'table-of-contents'), 'css-length');

        add_settings_section(
            'hessamzm_toc_shortcode',
            __('Shortcode', 'table-of-contents'),
            [$this, 'renderShortcodeDescription'],
            self::PAGE_SLUG
        );

        add_settings_field(
            'shortcode',
            __('Basic shortcode', 'table-of-contents'),
            [$this, 'renderShortcodeField'],
            self::PAGE_SLUG,
            'hessamzm_toc_shortcode'
        );

        add_settings_field(
            'shortcode_advanced',
            __('Advanced shortcode', 'table-of-contents'),
            [$this, 'renderAdvancedShortcodeField'],
            self::PAGE_SLUG,
            'hessamzm_toc_shortcode'
        );
    }

    public function renderShortcodeDescription(): void
    {
        echo '<p>' . esc_html__('Copy one of these shortcodes and paste it into a post, page, widget, or shortcode-capable editor.', 'table-of-contents') . '</p>';
    }

    public function renderShortcodeField(): void
    {
        echo '<input type="text" class="large-text code" readonly value="' . esc_attr('[hessamzm_toc]') . '">';
        echo '<p class="description">' . esc_html__('Uses the global TOC settings and the current content.', 'table-of-contents') . '</p>';
    }

    public function renderAdvancedShortcodeField(): void
    {
        echo '<input type="text" class="large-text code" readonly value="' . esc_attr('[hessamzm_toc levels="2,3,4" title="Contents" style="card" numbers="true"]') . '">';
        echo '<p class="description">' . esc_html__('Optional attributes: levels, title, style, and numbers.', 'table-of-contents') . '</p>';
    }

    public function renderGeneralDescription(): void
    {
        echo '<p>' . esc_html__('Configure automatic TOC rendering and its global appearance.', 'table-of-contents') . '</p>';
    }

    public function renderProductDescription(): void
    {
        echo '<p>' . esc_html__('Configure the WooCommerce product TOC independently from the general automatic renderer.', 'table-of-contents') . '</p>';
    }

    public function renderProductPositionField(): void
    {
        $value = (string) $this->settings->get('product_toc_position');
        $options = [
            'before_summary' => __('Before product summary', 'table-of-contents'),
            'inside_description' => __('Inside product description', 'table-of-contents'),
            'after_tabs' => __('After product tabs', 'table-of-contents'),
        ];

        echo '<select name="' . esc_attr(Settings::OPTION_KEY . '[product_toc_position]') . '">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('The TOC uses the product description headings and the global appearance settings.', 'table-of-contents') . '</p>';
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== 'settings_page_' . self::PAGE_SLUG) {
            return;
        }

        wp_enqueue_style(
            'hessamzm-toc',
            HESSAMZM_TOC_URL . 'assets/css/frontend.css',
            [],
            HESSAMZM_TOC_VERSION
        );

        wp_enqueue_style(
            'hessamzm-toc-admin',
            HESSAMZM_TOC_URL . 'assets/css/admin.css',
            [],
            HESSAMZM_TOC_VERSION
        );

        wp_enqueue_script(
            'hessamzm-toc-admin',
            HESSAMZM_TOC_URL . 'assets/js/admin-settings.js',
            [],
            HESSAMZM_TOC_VERSION,
            true
        );

        wp_set_script_translations(
            'hessamzm-toc-admin',
            'table-of-contents',
            HESSAMZM_TOC_DIR . 'languages'
        );
    }

    public function renderLivePreview(): void
    {
        $previewItems = [
            __('Introduction', 'table-of-contents'),
            __('Getting Started', 'table-of-contents'),
            __('Configuration', 'table-of-contents'),
            __('Advanced Settings', 'table-of-contents'),
            __('Conclusion', 'table-of-contents'),
        ];

        echo '<div class="hessamzm-toc-live-preview">';
        echo '<h2>' . esc_html__('Live preview', 'table-of-contents') . '</h2>';
        echo '<p class="description">' . esc_html__('Preview the TOC while you customize its appearance. Changes are shown instantly and are saved when you click Save Changes.', 'table-of-contents') . '</p>';
        echo '<div class="hessamzm-toc-preview-stage">';
        echo '<nav class="hessamzm-toc hessamzm-toc--paper hessamzm-toc--position-right" aria-label="' . esc_attr__('Live preview', 'table-of-contents') . '">';
        echo '<div class="hessamzm-toc__header"><p class="hessamzm-toc__title" data-default-title="' . esc_attr__('Table of Contents', 'table-of-contents') . '">' . esc_html__('Table of Contents', 'table-of-contents') . '</p></div>';
        echo '<div class="hessamzm-toc__body">';
        echo '<ol class="hessamzm-toc__list">';
        foreach ($previewItems as $index => $item) {
            echo '<li class="hessamzm-toc__item"><a class="hessamzm-toc__link" href="#">' . esc_html($item) . '</a>';
            if ($index === 1) {
                echo '<ol class="hessamzm-toc__children">';
                echo '<li class="hessamzm-toc__item"><a class="hessamzm-toc__link" href="#">' . esc_html__('Subsection example', 'table-of-contents') . '</a></li>';
                echo '</ol>';
            }
            echo '</li>';
        }
        echo '</ol></div>';
        echo '<div class="hessamzm-toc__footer">';
        echo '<button type="button" class="hessamzm-toc__toggle" data-expand-label="' . esc_attr__('View more', 'table-of-contents') . '" data-collapse-label="' . esc_attr__('View less', 'table-of-contents') . '">' . esc_html__('View more', 'table-of-contents') . '</button>';
        echo '</div></nav>';
        echo '</div></div>';
    }


    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'table-of-contents'));
        }

        echo '<div class="wrap hessamzm-toc-settings-page">';
        echo '<h1>' . esc_html__('Table of Contents', 'table-of-contents') . '</h1>';
        echo '<div class="hessamzm-toc-settings-layout">';
        echo '<aside class="hessamzm-toc-settings-preview">';
        $this->renderLivePreview();
        echo '</aside>';
        echo '<main class="hessamzm-toc-settings-form">';
        echo '<form method="post" action="options.php">';
        settings_fields('hessamzm_toc');
        do_settings_sections(self::PAGE_SLUG);
        submit_button();
        echo '</form>';
        echo '</main>';
        echo '</div>';
        echo '</div>';
    }

    private function addCheckbox(string $key, string $label, string $description, string $section = 'hessamzm_toc_general'): void
    {
        add_settings_field(
            $key,
            $label,
            [$this, 'renderCheckboxField'],
            self::PAGE_SLUG,
            $section,
            ['key' => $key, 'description' => $description]
        );
    }

    /**
     * @param array<string,string> $options
     */
    private function addCheckboxGroup(string $key, string $label, array $options): void
    {
        add_settings_field(
            $key,
            $label,
            [$this, 'renderCheckboxGroupField'],
            self::PAGE_SLUG,
            'hessamzm_toc_general',
            ['key' => $key, 'options' => $options]
        );
    }

    private function addTextField(string $key, string $label, string $class = ''): void
    {
        add_settings_field(
            $key,
            $label,
            [$this, 'renderTextField'],
            self::PAGE_SLUG,
            'hessamzm_toc_style',
            ['key' => $key, 'class' => $class]
        );
    }

    public function renderCheckboxField(array $args): void
    {
        $key = (string) $args['key'];
        $checked = (bool) $this->settings->get($key);

        echo '<label><input type="checkbox" name="' . esc_attr(Settings::OPTION_KEY . '[' . $key . ']') . '" value="1" ' . checked($checked, true, false) . '> ';
        echo esc_html((string) $args['description']);
        echo '</label>';
    }

    public function renderCheckboxGroupField(array $args): void
    {
        $key = (string) $args['key'];
        $selected = (array) $this->settings->get($key);

        foreach ((array) $args['options'] as $value => $label) {
            echo '<label style="display:block;margin-bottom:6px">';
            echo '<input type="checkbox" name="' . esc_attr(Settings::OPTION_KEY . '[' . $key . '][]') . '" value="' . esc_attr((string) $value) . '" ' . checked(in_array($value, $selected, true), true, false) . '> ';
            echo esc_html((string) $label);
            echo '</label>';
        }
    }

    public function renderStyleField(): void
    {
        $value = (string) $this->settings->get('style');
        $options = [
            'classic' => __('Classic', 'table-of-contents'),
            'minimal' => __('Minimal', 'table-of-contents'),
            'card' => __('Card', 'table-of-contents'),
            'paper' => __('Paper Menu', 'table-of-contents'),
        ];

        echo '<select name="' . esc_attr(Settings::OPTION_KEY . '[style]') . '">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function renderPositionField(): void
    {
        $value = (string) $this->settings->get('position');
        $options = [
            'right' => __('Right', 'table-of-contents'),
            'left' => __('Left', 'table-of-contents'),
        ];

        echo '<select name="' . esc_attr(Settings::OPTION_KEY . '[position]') . '">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Choose which side of the content the TOC is aligned to.', 'table-of-contents') . '</p>';
    }

    public function renderColorField(array $args): void
    {
        $key = (string) $args['key'];
        $value = (string) $this->settings->get($key);

        echo '<input type="color" name="' . esc_attr(Settings::OPTION_KEY . '[' . $key . ']') . '" value="' . esc_attr($value) . '">';
    }

    public function renderTextField(array $args): void
    {
        $key = (string) $args['key'];
        $class = (string) ($args['class'] ?? '');
        $value = (string) $this->settings->get($key);

        echo '<input type="text" class="regular-text" name="' . esc_attr(Settings::OPTION_KEY . '[' . $key . ']') . '" value="' . esc_attr($value) . '">';
        if ($class === 'css-length') {
            echo '<p class="description">' . esc_html__('Use a CSS length such as 16px, 1rem, or 1.5em.', 'table-of-contents') . '</p>';
        }
    }
}
