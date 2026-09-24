<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Tests\Unit;

use Hessamzm\TableOfContents\Settings\Settings;
use PHPUnit\Framework\TestCase;

final class SettingsTest extends TestCase
{
    public function testItSanitizesLevelsStylesColorsAndCssLengths(): void
    {
        $settings = new Settings();
        $clean = $settings->sanitize([
            'heading_levels' => [0, 2, 2, 7],
            'style' => 'invalid',
            'background_color' => '#112233',
            'font_size' => '18px',
            'indentation' => 'expression(alert(1))',
            'position' => 'left',
            'sticky_font_size' => '13px',
            'product_toc_enabled' => 1,
            'product_toc_position' => 'before_tabs',
        ]);

        self::assertSame([2], $clean['heading_levels']);
        self::assertSame('classic', $clean['style']);
        self::assertSame('#112233', $clean['background_color']);
        self::assertSame('18px', $clean['font_size']);
        self::assertSame('1.5rem', $clean['indentation']);
        self::assertSame('left', $clean['position']);
        self::assertSame('13px', $clean['sticky_font_size']);
        self::assertTrue($clean['product_toc_enabled']);
        self::assertSame('inside_description', $clean['product_toc_position']);
    }

    public function testItSupportsIndependentArticleAndProductProfiles(): void
    {
        $settings = new Settings();
        $clean = $settings->sanitize([
            'enabled' => 1,
            'post_types' => ['post', 'product'],
            'post_toc' => [
                'enabled' => 1,
                'heading_levels' => [2, 3],
                'style' => 'card',
                'position' => 'left',
            ],
            'product_toc' => [
                'enabled' => 1,
                'heading_levels' => [2, 4],
                'style' => 'minimal',
                'position' => 'right',
                'placement' => 'inside_description',
            ],
        ]);

        self::assertSame([2, 3], $clean['post_toc']['heading_levels']);
        self::assertSame('card', $clean['post_toc']['style']);
        self::assertSame('left', $clean['post_toc']['position']);
        self::assertSame([2, 4], $clean['product_toc']['heading_levels']);
        self::assertSame('minimal', $clean['product_toc']['style']);
        self::assertSame('inside_description', $clean['product_toc']['placement']);
        self::assertSame('left', $clean['post_toc']['position']);
    }

    public function testItMigratesLegacySettingsIntoProfiles(): void
    {
        $settings = new Settings();
        $clean = $settings->sanitize([
            'enabled' => 1,
            'heading_levels' => [2, 3, 4],
            'style' => 'paper',
            'product_toc_enabled' => 1,
            'product_toc_position' => 'inside_description',
        ]);

        self::assertSame([2, 3, 4], $clean['post_toc']['heading_levels']);
        self::assertTrue($clean['product_toc']['enabled']);
        self::assertSame('inside_description', $clean['product_toc']['placement']);
    }

    public function testItDefaultsProductTocToInsideDescription(): void
    {
        $settings = new Settings();

        self::assertSame('inside_description', $settings->defaults()['product_toc_position']);
    }
}
