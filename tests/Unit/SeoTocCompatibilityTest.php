<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Tests\Unit;

use Hessamzm\TableOfContents\Integrations\SEO\SeoTocCompatibility;
use PHPUnit\Framework\TestCase;

final class SeoTocCompatibilityTest extends TestCase
{
    public function testSupportedSeoBlocksAreDetected(): void
    {
        $compatibility = new SeoTocCompatibility();

        self::assertTrue(
            $compatibility->hasToc('<!-- wp:aioseo/table-of-contents -->')
        );
        self::assertTrue(
            $compatibility->hasToc('<!-- wp:rank-math/toc-block -->')
        );
        self::assertTrue(
            $compatibility->hasToc('<!-- wp:seopress/table-of-contents -->')
        );
        self::assertTrue(
            $compatibility->hasToc('<!-- wp:yoast/table-of-contents -->')
        );
    }

    public function testUnrelatedBlockIsNotDetected(): void
    {
        $compatibility = new SeoTocCompatibility();

        self::assertFalse(
            $compatibility->hasToc('<!-- wp:paragraph --><p>Content</p><!-- /wp:paragraph -->')
        );
    }
}
