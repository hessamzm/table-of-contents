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
        self::assertArrayNotHasKey('enabled', $clean);
        self::assertSame('inside_description', $clean['product_toc_position']);
    }

    public function testArticlesTabPreservesProductProfileAndIgnoresGlobalToggle(): void
    {
        $settings = new Settings();

        $GLOBALS['hessamzm_toc_test_options'][Settings::OPTION_KEY] = [
            'enabled' => false,
            'post_toc' => [
                'enabled' => false,
                'heading_levels' => [2, 3],
                'title' => 'Posts',
                'style' => 'paper',
                'show_numbers' => false,
                'sticky_toc' => false,
                'position' => 'right',
                'more_text' => 'More',
                'less_text' => 'Less',
            ],
            'product_toc' => [
                'enabled' => true,
                'heading_levels' => [2, 4],
                'title' => 'Products',
                'style' => 'card',
                'show_numbers' => true,
                'sticky_toc' => true,
                'position' => 'left',
                'placement' => 'inside_description',
                'more_text' => 'More',
                'less_text' => 'Less',
            ],
        ];

        $clean = $settings->sanitize([
            '_active_tab' => 'articles',
            'post_toc' => [
                'enabled' => true,
                'heading_levels' => [2, 5],
                'title' => 'Updated posts',
            ],
        ]);

        self::assertTrue($clean['post_toc']['enabled']);
        self::assertTrue($clean['product_toc']['enabled']);
        self::assertSame('card', $clean['product_toc']['style']);
        self::assertArrayNotHasKey('enabled', $clean);
    }

    public function testProductsTabPreservesArticleProfileAndIgnoresGlobalToggle(): void
    {
        $settings = new Settings();

        $GLOBALS['hessamzm_toc_test_options'][Settings::OPTION_KEY] = [
            'enabled' => false,
            'post_toc' => [
                'enabled' => true,
                'heading_levels' => [2, 3],
                'title' => 'Posts',
                'style' => 'paper',
                'show_numbers' => false,
                'sticky_toc' => false,
                'position' => 'right',
                'more_text' => 'More',
                'less_text' => 'Less',
            ],
            'product_toc' => [
                'enabled' => false,
                'heading_levels' => [2, 4],
                'title' => 'Products',
                'style' => 'card',
                'show_numbers' => true,
                'sticky_toc' => true,
                'position' => 'left',
                'placement' => 'inside_description',
                'more_text' => 'More',
                'less_text' => 'Less',
            ],
        ];

        $clean = $settings->sanitize([
            '_active_tab' => 'products',
            'product_toc' => [
                'enabled' => true,
                'heading_levels' => [2, 6],
                'placement' => 'after_tabs',
            ],
        ]);

        self::assertTrue($clean['product_toc']['enabled']);
        self::assertTrue($clean['post_toc']['enabled']);
        self::assertSame('paper', $clean['post_toc']['style']);
        self::assertArrayNotHasKey('enabled', $clean);
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
