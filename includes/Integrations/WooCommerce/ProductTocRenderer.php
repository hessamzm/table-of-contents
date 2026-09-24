<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Integrations\WooCommerce;

use Hessamzm\TableOfContents\Frontend\TocAssets;
use Hessamzm\TableOfContents\Frontend\TocRenderer;
use Hessamzm\TableOfContents\Settings\Settings;
use Hessamzm\TableOfContents\TOC\ContentProcessor;

defined('ABSPATH') || exit;

final class ProductTocRenderer
{
    /** @var array<int,array{content:string,tree:\Hessamzm\TableOfContents\TOC\HeadingTree}> */
    private array $processed = [];

    public function __construct(
        private readonly ContentProcessor $processor,
        private readonly TocRenderer $tocRenderer,
        private readonly Settings $settings,
        private readonly TocAssets $assets,
    ) {
    }

    public function boot(): void
    {
        if (!$this->isAvailable() || !$this->isEnabled()) {
            return;
        }

        add_filter('the_content', [$this, 'filterProductDescription'], 20);
        add_filter('body_class', [$this, 'filterBodyClass']);

        $position = $this->getPosition();

        if ($position === 'before_summary') {
            add_action('woocommerce_before_single_product_summary', [$this, 'render'], 30);
        } elseif ($position === 'after_tabs') {
            add_action('woocommerce_after_single_product_summary', [$this, 'render'], 12);
        }
    }

    public function render(): void
    {
        if (!$this->shouldRender()) {
            return;
        }

        $product = $this->getProduct();
        if ($product === null) {
            return;
        }

        $description = (string) $product->get_description();
        if ($this->hasManualToc($description)) {
            return;
        }

        $processed = $this->getProcessedDescription($product);

        if ($processed === null || $processed['tree']->isEmpty()) {
            return;
        }

        $profile = $this->getProfile();
        $this->assets->enqueue($profile);

        $toc = $this->tocRenderer->render($processed['tree'], array_merge($profile, ['profile' => 'product']));

        if ($toc === '') {
            return;
        }

        echo '<div class="hessamzm-toc-product">' . $toc . '</div>';
    }

    /**
     * Adds a scoped body class so WooCommerce tab overflow can be relaxed
     * only when Product TOC sticky mode is active.
     *
     * @param list<string> $classes
     * @return list<string>
     */
    public function filterBodyClass(array $classes): array
    {
        if ($this->shouldRender() && (bool) $this->getProfile()['sticky_toc']) {
            $classes[] = 'hessamzm-toc-product-sticky';
        }

        return $classes;
    }

    public function filterProductDescription(string $content): string
    {
        if (!$this->shouldRender()) {
            return $content;
        }

        $product = $this->getProduct();

        if ($product === null) {
            return $content;
        }

        $processed = $this->getProcessedDescription($product);

        if ($processed === null) {
            return $content;
        }

        $processedContent = $processed['content'];

        if ($this->hasManualToc($content)) {
            return $processedContent;
        }

        if ($this->getPosition() !== 'inside_description' || $processed['tree']->isEmpty()) {
            return $processedContent;
        }

        $profile = $this->getProfile();
        $this->assets->enqueue($profile);

        $toc = $this->tocRenderer->render($processed['tree'], array_merge($profile, ['profile' => 'product']));

        if ($toc === '') {
            return $processedContent;
        }

        return '<div class="hessamzm-toc-product hessamzm-toc-product--inside-description">' . $toc . '</div>' . $processedContent;
    }

    private function hasManualToc(string $content): bool
    {
        return str_contains($content, 'hessamzm-toc-manual')
            || has_shortcode($content, 'hessamzm_product_toc')
            || has_shortcode($content, 'hessamzm_toc');
    }

    private function getProcessedDescription(?object $product = null): ?array
    {
        $product ??= $this->getProduct();

        if ($product === null || !method_exists($product, 'get_id') || !method_exists($product, 'get_description')) {
            return null;
        }

        $productId = absint($product->get_id());

        if ($productId < 1) {
            return null;
        }

        if (isset($this->processed[$productId])) {
            return $this->processed[$productId];
        }

        $description = (string) $product->get_description();

        if ($description === '') {
            return null;
        }

        $levels = $this->getLevels();
        $processed = $this->processor->process($description, $levels);

        $this->processed[$productId] = $processed;

        return $processed;
    }

    private function getProduct(): ?object
    {
        if (!$this->isAvailable() || !is_singular('product') || !function_exists('wc_get_product')) {
            return null;
        }

        $product = wc_get_product(get_queried_object_id());

        return is_object($product) && is_a($product, 'WC_Product') ? $product : null;
    }

    private function shouldRender(): bool
    {
        if (!$this->isAvailable() || !$this->isEnabled() || is_admin() || !is_singular('product')) {
            return false;
        }

        if (is_feed() || post_password_required()) {
            return false;
        }

        if (!$this->isProductSelected()) {
            return false;
        }

        return (bool) apply_filters(
            'hessamzm_toc/product_should_render',
            true,
            get_queried_object_id()
        );
    }

    private function isProductSelected(): bool
    {
        $postTypes = (array) $this->settings->get('post_types');

        return in_array('product', $postTypes, true);
    }

    private function isEnabled(): bool
    {
        return (bool) $this->getProfile()['enabled'];
    }

    private function getPosition(): string
    {
        $position = sanitize_key((string) $this->getProfile()['placement']);

        return in_array($position, ['before_summary', 'inside_description', 'after_tabs'], true)
            ? $position
            : 'inside_description';
    }

    /** @return list<int> */
    private function getLevels(): array
    {
        $levels = (array) apply_filters(
            'hessamzm_toc/product_heading_levels',
            (array) $this->getProfile()['heading_levels']
        );

        return array_values(
            array_filter(
                array_map('intval', $levels),
                static fn (int $level): bool => $level >= 1 && $level <= 6
            )
        );
    }

    /** @return array<string,mixed> */
    private function getProfile(): array
    {
        return $this->settings->getProfile('product');
    }

    private function isAvailable(): bool
    {
        return class_exists('WooCommerce') && function_exists('wc_get_product');
    }
}
