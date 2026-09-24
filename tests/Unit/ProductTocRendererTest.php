<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Tests\Unit;

use Hessamzm\TableOfContents\Integrations\WooCommerce\ProductTocRenderer;
use PHPUnit\Framework\TestCase;

final class ProductTocRendererTest extends TestCase
{
    public function testProductIntegrationClassesAreDefined(): void
    {
        self::assertTrue(class_exists(ProductTocRenderer::class));
    }
}
