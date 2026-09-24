<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Integrations\WooCommerce;

use Hessamzm\TableOfContents\Frontend\TocAssets;
use Hessamzm\TableOfContents\Frontend\TocRenderer;
use Hessamzm\TableOfContents\Settings\Settings;
use Hessamzm\TableOfContents\TOC\ContentProcessor;

defined('ABSPATH') || exit;

final class WooCommerceIntegration
{
    private ProductTocRenderer $productTocRenderer;

    public function __construct(
        ContentProcessor $processor,
        TocRenderer $tocRenderer,
        private readonly Settings $settings,
        TocAssets $assets,
    ) {
        $this->productTocRenderer = new ProductTocRenderer(
            $processor,
            $tocRenderer,
            $settings,
            $assets
        );
    }

    public function boot(): void
    {
        if (!class_exists('WooCommerce')) {
            return;
        }

        $this->productTocRenderer->boot();

        add_filter('hessamzm_toc/should_render', [$this, 'disableAutomaticProductToc'], 10, 3);
    }

    public function disableAutomaticProductToc(bool $shouldRender, int $postId, string $postType): bool
    {
        if ($postType !== 'product' || !(bool) $this->settings->get('product_toc_enabled')) {
            return $shouldRender;
        }

        return false;
    }
}
