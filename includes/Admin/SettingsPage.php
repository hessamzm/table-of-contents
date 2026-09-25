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
        $this->addCheckboxGroup('post_types', __('Content types', 'table-of-contents'), [
            'post' => __('Blog posts', 'table-of-contents'),
            'page' => __('Pages', 'table-of-contents'),
            'product' => __('WooCommerce products', 'table-of-contents'),
        ], 'hessamzm_toc_general');
        $this->addCheckbox('delete_data_on_uninstall', __('Delete data on uninstall', 'table-of-contents'), __('Delete plugin settings when the plugin is permanently uninstalled.', 'table-of-contents'), 'hessamzm_toc_general');

        add_settings_section(
            'hessamzm_toc_articles',
            __('Articles', 'table-of-contents'),
            [$this, 'renderArticlesDescription'],
            self::PAGE_SLUG
        );
        $this->registerProfileFields('post', 'hessamzm_toc_articles', false);

        add_settings_section(
            'hessamzm_toc_products',
            __('Products', 'table-of-contents'),
            [$this, 'renderProductsDescription'],
            self::PAGE_SLUG
        );
        $this->registerProfileFields('product', 'hessamzm_toc_products', true);
    }

    private function registerProfileFields(string $profile, string $section, bool $product): void
    {
        $prefix = $profile . '_toc';

        $this->addCheckbox($prefix . '_enabled', __('Enable TOC', 'table-of-contents'), $product
            ? __('Generate a dedicated TOC for WooCommerce product descriptions.', 'table-of-contents')
            : __('Generate a dedicated TOC for blog posts.', 'table-of-contents'), $section, $profile, 'enabled');

        $this->addHeadingLevelsField($section, $profile);

        $this->addSelectField($section, $profile, 'style', __('Style', 'table-of-contents'), [
            'classic' => __('Classic', 'table-of-contents'),
            'minimal' => __('Minimal', 'table-of-contents'),
            'card' => __('Card', 'table-of-contents'),
            'paper' => __('Paper Menu', 'table-of-contents'),
        ]);

        $this->addTextField($section, $profile, 'title', __('Title', 'table-of-contents'));
        $this->addCheckbox($prefix . '_show_numbers', __('Show numbers', 'table-of-contents'), __('Prefix TOC items with hierarchical numbers.', 'table-of-contents'), $section, $profile, 'show_numbers');
        $this->addCheckbox($prefix . '_sticky_toc', __('Sticky TOC', 'table-of-contents'), __('Keep the TOC visible while scrolling on larger screens.', 'table-of-contents'), $section, $profile, 'sticky_toc');

        $this->addSelectField($section, $profile, 'position', __('Alignment', 'table-of-contents'), [
            'right' => __('Right', 'table-of-contents'),
            'left' => __('Left', 'table-of-contents'),
        ]);

        if ($product) {
            $this->addSelectField($section, $profile, 'placement', __('TOC placement', 'table-of-contents'), [
                'inside_description' => __('Inside product description', 'table-of-contents'),
                'before_summary' => __('Before product summary', 'table-of-contents'),
                'after_tabs' => __('After product tabs', 'table-of-contents'),
            ]);
        }

        foreach ([
            'background_color' => __('Background color', 'table-of-contents'),
            'text_color' => __('Text color', 'table-of-contents'),
            'link_color' => __('Link color', 'table-of-contents'),
            'border_color' => __('Border color', 'table-of-contents'),
        ] as $key => $label) {
            add_settings_field(
                $prefix . '_' . $key,
                $label,
                [$this, 'renderProfileColorField'],
                self::PAGE_SLUG,
                $section,
                ['profile' => $profile, 'key' => $key]
            );
        }

        foreach ([
            'font_size' => __('Font size', 'table-of-contents'),
            'sticky_font_size' => __('Sticky TOC font size', 'table-of-contents'),
            'indentation' => __('Indentation', 'table-of-contents'),
            'border_radius' => __('Border radius', 'table-of-contents'),
        ] as $key => $label) {
            $this->addTextField($section, $profile, $key, $label, 'css-length');
        }

        $this->addTextField($section, $profile, 'more_text', __('View more text', 'table-of-contents'));
        $this->addTextField($section, $profile, 'less_text', __('View less text', 'table-of-contents'));
    }

    private function addCheckbox(string $key, string $label, string $description, string $section, ?string $profile = null, ?string $profileKey = null): void
    {
        add_settings_field(
            $key,
            $label,
            [$this, 'renderCheckboxField'],
            self::PAGE_SLUG,
            $section,
            ['key' => $profileKey ?? $key, 'description' => $description, 'profile' => $profile]
        );
    }

    /** @param array<string,string> $options */
    private function addCheckboxGroup(string $key, string $label, array $options, string $section): void
    {
        add_settings_field(
            $key,
            $label,
            [$this, 'renderCheckboxGroupField'],
            self::PAGE_SLUG,
            $section,
            ['key' => $key, 'options' => $options]
        );
    }

    private function addHeadingLevelsField(string $section, string $profile): void
    {
        add_settings_field(
            $profile . '_heading_levels',
            __('Heading levels', 'table-of-contents'),
            [$this, 'renderProfileHeadingLevelsField'],
            self::PAGE_SLUG,
            $section,
            ['profile' => $profile]
        );
    }

    private function addTextField(string $section, string $profile, string $key, string $label, string $class = ''): void
    {
        add_settings_field(
            $profile . '_toc_' . $key,
            $label,
            [$this, 'renderProfileTextField'],
            self::PAGE_SLUG,
            $section,
            ['profile' => $profile, 'key' => $key, 'class' => $class]
        );
    }

    private function addSelectField(string $section, string $profile, string $key, string $label, array $options): void
    {
        add_settings_field(
            $profile . '_toc_' . $key,
            $label,
            [$this, 'renderProfileSelectField'],
            self::PAGE_SLUG,
            $section,
            ['profile' => $profile, 'key' => $key, 'options' => $options]
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'table-of-contents'));
        }

        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';
        if (!in_array($tab, ['general', 'articles', 'products'], true)) {
            $tab = 'general';
        }

        echo '<div class="wrap hessamzm-toc-settings-page">';
        echo '<h1>' . esc_html__('Table of Contents', 'table-of-contents') . '</h1>';
        echo '<nav class="nav-tab-wrapper" aria-label="' . esc_attr__('Table of Contents settings', 'table-of-contents') . '">';
        $tabs = [
            'general' => __('General', 'table-of-contents'),
            'articles' => __('Articles', 'table-of-contents'),
            'products' => __('Products', 'table-of-contents'),
        ];
        foreach ($tabs as $key => $label) {
            $url = add_query_arg(['page' => self::PAGE_SLUG, 'tab' => $key], admin_url('options-general.php'));
            $class = $tab === $key ? ' nav-tab-active' : '';
            echo '<a class="nav-tab' . esc_attr($class) . '" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
        }
        echo '</nav>';

        echo '<div class="hessamzm-toc-settings-layout">';
        echo '<aside class="hessamzm-toc-settings-preview">';
        $this->renderLivePreview($tab === 'products' ? 'product' : 'post');
        echo '</aside>';
        echo '<main class="hessamzm-toc-settings-form">';
        echo '<form method="post" action="options.php">';
        settings_fields('hessamzm_toc');
        echo '<input type="hidden" name="' . esc_attr(Settings::OPTION_KEY . '[_active_tab]') . '" value="' . esc_attr($tab) . '">';
        $section = 'hessamzm_toc_' . $tab;
        echo '<div class="hessamzm-toc-settings-section">';
        if ($tab === 'general') {
            $this->renderGeneralDescription();
        } elseif ($tab === 'articles') {
            $this->renderArticlesDescription();
        } else {
            $this->renderProductsDescription();
        }
        echo '<table class="form-table" role="presentation">';
        do_settings_fields(self::PAGE_SLUG, $section);
        echo '</table>';
        if ($tab === 'general') {
            $this->renderShortcodeGenerators();
        }
        echo '</div>';
        submit_button();
        echo '</form>';
        echo '</main>';
        echo '</div>';
        echo '</div>';
    }

    private function renderLivePreview(string $profile): void
    {
        $previewItems = [
            __('Introduction', 'table-of-contents'),
            __('Getting Started', 'table-of-contents'),
            __('Configuration', 'table-of-contents'),
            __('Advanced Settings', 'table-of-contents'),
            __('Conclusion', 'table-of-contents'),
        ];

        echo '<div class="hessamzm-toc-live-preview" data-preview-profile="' . esc_attr($profile) . '">';
        echo '<h2>' . esc_html__('Live preview', 'table-of-contents') . '</h2>';
        echo '<p class="description">' . esc_html__('Preview the TOC while you customize its appearance. Changes are shown instantly and are saved when you click Save Changes.', 'table-of-contents') . '</p>';
        echo '<div class="hessamzm-toc-preview-stage">';
        echo '<nav class="hessamzm-toc hessamzm-toc--paper hessamzm-toc--position-right" aria-label="' . esc_attr__('Live preview', 'table-of-contents') . '">';
        echo '<div class="hessamzm-toc__header"><p class="hessamzm-toc__title" data-default-title="' . esc_attr__('Table of Contents', 'table-of-contents') . '">' . esc_html__('Table of Contents', 'table-of-contents') . '</p></div>';
        echo '<div class="hessamzm-toc__body"><ol class="hessamzm-toc__list">';
        foreach ($previewItems as $index => $item) {
            echo '<li class="hessamzm-toc__item"><a class="hessamzm-toc__link" href="#">' . esc_html($item) . '</a>';
            if ($index === 1) {
                echo '<ol class="hessamzm-toc__children"><li class="hessamzm-toc__item"><a class="hessamzm-toc__link" href="#">' . esc_html__('Subsection example', 'table-of-contents') . '</a></li></ol>';
            }
            echo '</li>';
        }
        echo '</ol></div><div class="hessamzm-toc__footer">';
        echo '<button type="button" class="hessamzm-toc__toggle" data-expand-label="' . esc_attr__('View more', 'table-of-contents') . '" data-collapse-label="' . esc_attr__('View less', 'table-of-contents') . '">' . esc_html__('View more', 'table-of-contents') . '</button>'; 
        echo '</div></nav></div></div>';
    }

    private function renderShortcodeGenerators(): void
    {
        $postProfile = $this->settings->getProfile('post');
        $productProfile = $this->settings->getProfile('product');
        $postEnabled = !empty($postProfile['enabled']);
        $productEnabled = !empty($productProfile['enabled']);
        $bothEnabled = $postEnabled && $productEnabled;

        $sectionClass = 'hessamzm-toc-shortcode-generators' . ($bothEnabled ? ' is-disabled' : '');

        echo '<section class="' . esc_attr($sectionClass) . '" aria-labelledby="hessamzm-toc-shortcode-generators-title">';
        echo '<h2 id="hessamzm-toc-shortcode-generators-title">' . esc_html__('Shortcode generators', 'table-of-contents') . '</h2>';
        echo '<p class="description">' . esc_html__('Build a fully customizable shortcode. The generator exposes the same visual and behavioral settings available in the selected TOC profile.', 'table-of-contents') . '</p>';

        if ($bothEnabled) {
            echo '<div class="notice notice-warning inline hessamzm-toc-shortcode-warning">';
            echo '<p><strong>' . esc_html__('Shortcodes are disabled while both automatic TOCs are enabled.', 'table-of-contents') . '</strong><br>';
            echo esc_html__('Both Articles and Products currently generate their TOC automatically. Adding a manual shortcode would create a duplicate TOC, so disable the automatic TOC for the content type you want to place manually, then return here to generate its shortcode.', 'table-of-contents') . '</p>';
            echo '</div>';
        } else {
            echo '<p class="description">' . esc_html__('Use a shortcode only for a content type whose automatic TOC is disabled. This prevents the same post or product from receiving two TOCs.', 'table-of-contents') . '</p>';
        }

        $this->renderShortcodeGeneratorCard(
            'post',
            __('Blog TOC shortcode', 'table-of-contents'),
            __('Use this shortcode inside a blog post to insert the Blog TOC manually.', 'table-of-contents'),
            'hessamzm_blog_toc',
            $postEnabled || $bothEnabled,
            $postEnabled
                ? __('The Articles automatic TOC is enabled. Disable it before using the Blog shortcode, otherwise the post may contain two TOCs.', 'table-of-contents')
                : ''
        );

        $this->renderShortcodeGeneratorCard(
            'product',
            __('Product TOC shortcode', 'table-of-contents'),
            __('Use this shortcode inside a WooCommerce product to insert the Product TOC manually.', 'table-of-contents'),
            'hessamzm_product_toc',
            $productEnabled || $bothEnabled,
            $productEnabled
                ? __('The Products automatic TOC is enabled. Disable it before using the Product shortcode, otherwise the product may contain two TOCs.', 'table-of-contents')
                : ''
        );

        echo '</section>';
    }

    private function renderShortcodeGeneratorCard(
        string $profile,
        string $title,
        string $description,
        string $tagConstant,
        bool $disabled = false,
        string $disabledReason = ''
    ): void {
        $values = $this->settings->getProfile($profile);
        $levels = array_map('absint', (array) ($values['heading_levels'] ?? [2, 3, 4, 5, 6]));
        $style = (string) ($values['style'] ?? 'paper');
        $profileId = 'hessamzm-toc-shortcode-generator-' . $profile;

        echo '<fieldset class="hessamzm-toc-shortcode-generator" data-shortcode-profile="' . esc_attr($profile) . '"';
        echo ' data-shortcode-tag="' . esc_attr($tagConstant) . '"';
        echo ' data-default-levels="' . esc_attr(wp_json_encode(array_values($levels))) . '"';
        echo ' data-default-title="' . esc_attr((string) ($values['title'] ?? '')) . '"';
        echo ' data-default-style="' . esc_attr($style) . '"';
        echo ' data-default-numbers="' . esc_attr(!empty($values['show_numbers']) ? '1' : '0') . '"';
        echo ' data-default-sticky="' . esc_attr(!empty($values['sticky_toc']) ? '1' : '0') . '"';
        echo ' data-default-position="' . esc_attr((string) ($values['position'] ?? 'right')) . '"';
        echo ' data-default-placement="' . esc_attr((string) ($values['placement'] ?? 'inside_description')) . '"';
        echo ' data-default-background="' . esc_attr((string) ($values['background_color'] ?? '#ffffff')) . '"';
        echo ' data-default-text-color="' . esc_attr((string) ($values['text_color'] ?? '#1d2327')) . '"';
        echo ' data-default-link-color="' . esc_attr((string) ($values['link_color'] ?? '#2271b1')) . '"';
        echo ' data-default-border-color="' . esc_attr((string) ($values['border_color'] ?? '#dcdcde')) . '"';
        echo ' data-default-font-size="' . esc_attr((string) ($values['font_size'] ?? '16px')) . '"';
        echo ' data-default-sticky-font-size="' . esc_attr((string) ($values['sticky_font_size'] ?? '14px')) . '"';
        echo ' data-default-indentation="' . esc_attr((string) ($values['indentation'] ?? '1.5rem')) . '"';
        echo ' data-default-border-radius="' . esc_attr((string) ($values['border_radius'] ?? '0px')) . '"';
        echo ' data-default-more-text="' . esc_attr((string) ($values['more_text'] ?? __('View more', 'table-of-contents'))) . '"';
        echo ' data-default-less-text="' . esc_attr((string) ($values['less_text'] ?? __('View less', 'table-of-contents'))) . '"';
        echo ' ' . disabled($disabled, true, false) . '>';

        echo '<legend><span class="h3">' . esc_html($title) . '</span></legend>';
        echo '<p>' . esc_html($description) . '</p>';

        if ($disabled) {
            echo '<div class="notice notice-warning inline hessamzm-toc-shortcode-card-warning">';
            echo '<p>' . esc_html($disabledReason !== '' ? $disabledReason : __('Disable the automatic TOC for this content type before using its shortcode.', 'table-of-contents')) . '</p>';
            echo '</div>';
        }

        echo '<div class="hessamzm-toc-shortcode-generator__fields">';
        echo '<div class="hessamzm-toc-shortcode-field-group">';
        echo '<h4>' . esc_html__('Content and layout', 'table-of-contents') . '</h4>';

        echo '<fieldset>';
        echo '<legend>' . esc_html__('Heading levels', 'table-of-contents') . '</legend>';
        for ($level = 1; $level <= 6; $level++) {
            echo '<label>';
            echo '<input type="checkbox" class="hessamzm-toc-shortcode-level" value="' . esc_attr((string) $level) . '" ' . checked(in_array($level, $levels, true), true, false) . ' ' . disabled($disabled, true, false) . '>';
            echo esc_html(sprintf(/* translators: %d: heading level */ __('Heading %d', 'table-of-contents'), $level));
            echo '</label>';
        }
        echo '</fieldset>';

        echo '<p><label for="' . esc_attr($profileId . '-title') . '">' . esc_html__('Title', 'table-of-contents') . '</label><br>';
        echo '<input id="' . esc_attr($profileId . '-title') . '" type="text" class="regular-text hessamzm-toc-shortcode-title" value="' . esc_attr((string) ($values['title'] ?? '')) . '" ' . disabled($disabled, true, false) . '></p>';

        echo '<p><label for="' . esc_attr($profileId . '-style') . '">' . esc_html__('Style', 'table-of-contents') . '</label><br>';
        echo '<select id="' . esc_attr($profileId . '-style') . '" class="hessamzm-toc-shortcode-style" ' . disabled($disabled, true, false) . '>';
        foreach ([
            'classic' => __('Classic', 'table-of-contents'),
            'minimal' => __('Minimal', 'table-of-contents'),
            'card' => __('Card', 'table-of-contents'),
            'paper' => __('Paper Menu', 'table-of-contents'),
        ] as $option => $label) {
            echo '<option value="' . esc_attr($option) . '" ' . selected($style, $option, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select></p>';

        echo '<p><label><input type="checkbox" class="hessamzm-toc-shortcode-numbers" ' . checked(!empty($values['show_numbers']), true, false) . ' ' . disabled($disabled, true, false) . '> ' . esc_html__('Show numbers', 'table-of-contents') . '</label></p>';
        echo '<p><label><input type="checkbox" class="hessamzm-toc-shortcode-sticky" ' . checked(!empty($values['sticky_toc']), true, false) . ' ' . disabled($disabled, true, false) . '> ' . esc_html__('Sticky TOC', 'table-of-contents') . '</label></p>';

        echo '<p><label for="' . esc_attr($profileId . '-position') . '">' . esc_html__('Alignment', 'table-of-contents') . '</label><br>';
        echo '<select id="' . esc_attr($profileId . '-position') . '" class="hessamzm-toc-shortcode-position" ' . disabled($disabled, true, false) . '>';
        foreach (['right' => __('Right', 'table-of-contents'), 'left' => __('Left', 'table-of-contents')] as $option => $label) {
            echo '<option value="' . esc_attr($option) . '" ' . selected((string) ($values['position'] ?? 'right'), $option, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select></p>';

        if ($profile === 'product') {
            echo '<p><label for="' . esc_attr($profileId . '-placement') . '">' . esc_html__('TOC placement', 'table-of-contents') . '</label><br>';
            echo '<select id="' . esc_attr($profileId . '-placement') . '" class="hessamzm-toc-shortcode-placement" ' . disabled($disabled, true, false) . '>';
            foreach ([
                'inside_description' => __('Inside product description', 'table-of-contents'),
                'before_summary' => __('Before product summary', 'table-of-contents'),
                'after_tabs' => __('After product tabs', 'table-of-contents'),
            ] as $option => $label) {
                echo '<option value="' . esc_attr($option) . '" ' . selected((string) ($values['placement'] ?? 'inside_description'), $option, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select></p>';
        }
        echo '</div>';

        echo '<div class="hessamzm-toc-shortcode-field-group">';
        echo '<h4>' . esc_html__('Appearance', 'table-of-contents') . '</h4>';
        foreach ([
            'background_color' => __('Background color', 'table-of-contents'),
            'text_color' => __('Text color', 'table-of-contents'),
            'link_color' => __('Link color', 'table-of-contents'),
            'border_color' => __('Border color', 'table-of-contents'),
        ] as $key => $label) {
            echo '<p><label for="' . esc_attr($profileId . '-' . $key) . '">' . esc_html($label) . '</label><br>';
            echo '<input id="' . esc_attr($profileId . '-' . $key) . '" type="color" class="hessamzm-toc-shortcode-' . esc_attr($key) . '" value="' . esc_attr((string) ($values[$key] ?? '#ffffff')) . '" ' . disabled($disabled, true, false) . '></p>';
        }

        foreach ([
            'font_size' => __('Font size', 'table-of-contents'),
            'sticky_font_size' => __('Sticky TOC font size', 'table-of-contents'),
            'indentation' => __('Indentation', 'table-of-contents'),
            'border_radius' => __('Border radius', 'table-of-contents'),
        ] as $key => $label) {
            echo '<p><label for="' . esc_attr($profileId . '-' . $key) . '">' . esc_html($label) . '</label><br>';
            echo '<input id="' . esc_attr($profileId . '-' . $key) . '" type="text" class="regular-text hessamzm-toc-shortcode-' . esc_attr($key) . '" value="' . esc_attr((string) ($values[$key] ?? '')) . '" placeholder="' . esc_attr__('Example: 16px, 1rem, or 1.5em', 'table-of-contents') . '" ' . disabled($disabled, true, false) . '></p>';
        }

        echo '<p><label for="' . esc_attr($profileId . '-more-text') . '">' . esc_html__('View more text', 'table-of-contents') . '</label><br>';
        echo '<input id="' . esc_attr($profileId . '-more-text') . '" type="text" class="regular-text hessamzm-toc-shortcode-more-text" value="' . esc_attr((string) ($values['more_text'] ?? '')) . '" ' . disabled($disabled, true, false) . '></p>';
        echo '<p><label for="' . esc_attr($profileId . '-less-text') . '">' . esc_html__('View less text', 'table-of-contents') . '</label><br>';
        echo '<input id="' . esc_attr($profileId . '-less-text') . '" type="text" class="regular-text hessamzm-toc-shortcode-less-text" value="' . esc_attr((string) ($values['less_text'] ?? '')) . '" ' . disabled($disabled, true, false) . '></p>';
        echo '</div>';
        echo '</div>';

        echo '<p><label for="' . esc_attr($profileId . '-output') . '">' . esc_html__('Generated shortcode', 'table-of-contents') . '</label><br>';
        echo '<textarea id="' . esc_attr($profileId . '-output') . '" class="large-text code hessamzm-toc-shortcode-output" rows="4" readonly></textarea></p>';
        echo '<button type="button" class="button hessamzm-toc-shortcode-copy" data-copy-label="' . esc_attr__('Copy shortcode', 'table-of-contents') . '" data-copied-label="' . esc_attr__('Copied', 'table-of-contents') . '" ' . disabled($disabled, true, false) . '>' . esc_html__('Copy shortcode', 'table-of-contents') . '</button>';
        echo '</fieldset>';
    }

    public function renderGeneralDescription(): void
    {
        echo '<p>' . esc_html__('General plugin settings. Blog posts and products are enabled and configured independently in their respective tabs.', 'table-of-contents') . '</p>';
    }

    public function renderArticlesDescription(): void
    {
        echo '<p>' . esc_html__('Create and customize the TOC used specifically for blog posts. These settings are independent from the product TOC.', 'table-of-contents') . '</p>';
    }

    public function renderProductsDescription(): void
    {
        echo '<p>' . esc_html__('Create and customize the TOC used specifically for WooCommerce products. These settings are independent from the article TOC.', 'table-of-contents') . '</p>';
    }

    public function renderCheckboxField(array $args): void
    {
        $profile = isset($args['profile']) ? (string) $args['profile'] : '';
        $key = (string) $args['key'];
        $checked = $profile !== '' ? !empty($this->settings->getProfile($profile)[$key]) : !empty($this->settings->get($key));
        $name = $profile !== ''
            ? Settings::OPTION_KEY . '[' . $profile . '_toc][' . $key . ']'
            : Settings::OPTION_KEY . '[' . $key . ']';

        echo '<label><input type="checkbox" name="' . esc_attr($name) . '" value="1" ' . checked($checked, true, false) . '> ';
        echo esc_html((string) $args['description']) . '</label>';
    }

    /** @param array<string,mixed> $args */
    public function renderCheckboxGroupField(array $args): void
    {
        $key = (string) $args['key'];
        $selected = (array) $this->settings->get($key);
        foreach ((array) $args['options'] as $value => $label) {
            echo '<label style="display:block;margin-bottom:6px">';
            echo '<input type="checkbox" name="' . esc_attr(Settings::OPTION_KEY . '[' . $key . '][]') . '" value="' . esc_attr((string) $value) . '" ' . checked(in_array($value, $selected, true), true, false) . '> ';
            echo esc_html((string) $label) . '</label>';
        }
    }

    public function renderProfileHeadingLevelsField(array $args): void
    {
        $profile = (string) $args['profile'];
        $selected = (array) ($this->settings->getProfile($profile)['heading_levels'] ?? []);
        for ($level = 1; $level <= 6; $level++) {
            echo '<label style="display:inline-block;margin-right:14px">';
            echo '<input type="checkbox" name="' . esc_attr(Settings::OPTION_KEY . '[' . $profile . '_toc][heading_levels][]') . '" value="' . esc_attr((string) $level) . '" ' . checked(in_array($level, $selected, true), true, false) . '> ';
            echo esc_html(sprintf(/* translators: %d: heading level */ __('Heading %d', 'table-of-contents'), $level)) . '</label>';
        }
    }

    public function renderProfileTextField(array $args): void
    {
        $profile = (string) $args['profile'];
        $key = (string) $args['key'];
        $value = (string) ($this->settings->getProfile($profile)[$key] ?? '');
        echo '<input type="text" class="regular-text" name="' . esc_attr(Settings::OPTION_KEY . '[' . $profile . '_toc][' . $key . ']') . '" value="' . esc_attr($value) . '">';
        if (($args['class'] ?? '') === 'css-length') {
            echo '<p class="description">' . esc_html__('Use a CSS length such as 16px, 1rem, or 1.5em.', 'table-of-contents') . '</p>';
        }
    }

    public function renderProfileColorField(array $args): void
    {
        $profile = (string) $args['profile'];
        $key = (string) $args['key'];
        $value = (string) ($this->settings->getProfile($profile)[$key] ?? '');
        echo '<input type="color" name="' . esc_attr(Settings::OPTION_KEY . '[' . $profile . '_toc][' . $key . ']') . '" value="' . esc_attr($value) . '">';
    }

    public function renderProfileSelectField(array $args): void
    {
        $profile = (string) $args['profile'];
        $key = (string) $args['key'];
        $value = (string) ($this->settings->getProfile($profile)[$key] ?? '');
        echo '<select name="' . esc_attr(Settings::OPTION_KEY . '[' . $profile . '_toc][' . $key . ']') . '">';
        foreach ((array) $args['options'] as $option => $label) {
            echo '<option value="' . esc_attr((string) $option) . '" ' . selected($value, $option, false) . '>' . esc_html((string) $label) . '</option>';
        }
        echo '</select>';
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== 'settings_page_' . self::PAGE_SLUG) {
            return;
        }

        wp_enqueue_style('hessamzm-toc', HESSAMZM_TOC_URL . 'assets/css/frontend.css', [], HESSAMZM_TOC_VERSION);
        wp_enqueue_style('hessamzm-toc-admin', HESSAMZM_TOC_URL . 'assets/css/admin.css', [], HESSAMZM_TOC_VERSION);
        wp_enqueue_script('hessamzm-toc-admin', HESSAMZM_TOC_URL . 'assets/js/admin-settings.js', [], HESSAMZM_TOC_VERSION, true);
        wp_set_script_translations('hessamzm-toc-admin', 'table-of-contents', HESSAMZM_TOC_DIR . 'languages');
    }
}
