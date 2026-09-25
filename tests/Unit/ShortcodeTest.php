<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Tests\Unit;

use Hessamzm\TableOfContents\Shortcodes\TableOfContentsShortcode;
use PHPUnit\Framework\TestCase;

final class ShortcodeTest extends TestCase
{
    public function testItNormalizesCompleteShortcodeCustomization(): void
    {
        $shortcode = (new \ReflectionClass(TableOfContentsShortcode::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(TableOfContentsShortcode::class, 'normalizeAttributes');
        $method->setAccessible(true);

        $attributes = $method->invoke($shortcode, [
            'levels' => '2, 4, 6',
            'title' => 'Custom TOC',
            'style' => 'card',
            'numbers' => 'true',
            'sticky' => 'yes',
            'position' => 'left',
            'placement' => 'after_tabs',
            'background' => '#112233',
            'text_color' => '#223344',
            'link_color' => '#334455',
            'border_color' => '#445566',
            'font_size' => '18px',
            'sticky_font_size' => '14px',
            'indentation' => '2rem',
            'border_radius' => '8px',
            'more_text' => 'More',
            'less_text' => 'Less',
        ], TableOfContentsShortcode::PRODUCT_TAG);

        self::assertSame([2, 4, 6], $attributes['headingLevels']);
        self::assertSame('Custom TOC', $attributes['title']);
        self::assertSame('card', $attributes['style']);
        self::assertTrue($attributes['showNumbers']);
        self::assertTrue($attributes['stickyToc']);
        self::assertSame('left', $attributes['position']);
        self::assertSame('after_tabs', $attributes['placement']);
        self::assertSame('#112233', $attributes['background_color']);
        self::assertSame('#223344', $attributes['text_color']);
        self::assertSame('#334455', $attributes['link_color']);
        self::assertSame('#445566', $attributes['border_color']);
        self::assertSame('18px', $attributes['font_size']);
        self::assertSame('14px', $attributes['sticky_font_size']);
        self::assertSame('2rem', $attributes['indentation']);
        self::assertSame('8px', $attributes['border_radius']);
        self::assertSame('More', $attributes['more_text']);
        self::assertSame('Less', $attributes['less_text']);
    }

    public function testItRejectsInvalidShortcodeCustomizationValues(): void
    {
        $shortcode = (new \ReflectionClass(TableOfContentsShortcode::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(TableOfContentsShortcode::class, 'normalizeAttributes');
        $method->setAccessible(true);

        $attributes = $method->invoke($shortcode, [
            'levels' => '0,2,7',
            'style' => 'invalid',
            'numbers' => 'false',
            'sticky' => 'off',
            'position' => 'center',
            'placement' => 'invalid',
            'background' => 'red',
            'font_size' => 'expression(alert(1))',
            'border_radius' => '12vh',
        ], TableOfContentsShortcode::BLOG_TAG);

        self::assertSame([0, 2, 7], $attributes['headingLevels']);
        self::assertFalse($attributes['showNumbers']);
        self::assertFalse($attributes['stickyToc']);
        self::assertArrayNotHasKey('style', $attributes);
        self::assertArrayNotHasKey('position', $attributes);
        self::assertArrayNotHasKey('placement', $attributes);
        self::assertArrayNotHasKey('background_color', $attributes);
        self::assertArrayNotHasKey('font_size', $attributes);
        self::assertArrayNotHasKey('border_radius', $attributes);
    }
}
