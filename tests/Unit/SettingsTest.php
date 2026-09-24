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
        ]);

        self::assertSame([2], $clean['heading_levels']);
        self::assertSame('classic', $clean['style']);
        self::assertSame('#112233', $clean['background_color']);
        self::assertSame('18px', $clean['font_size']);
        self::assertSame('1.5rem', $clean['indentation']);
    }
}
