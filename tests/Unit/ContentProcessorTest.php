<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Tests\Unit;

use Hessamzm\TableOfContents\TOC\AnchorGenerator;
use Hessamzm\TableOfContents\TOC\ContentProcessor;
use Hessamzm\TableOfContents\TOC\HeadingParser;
use Hessamzm\TableOfContents\TOC\TocBuilder;
use PHPUnit\Framework\TestCase;

final class ContentProcessorTest extends TestCase
{
    public function testItKeepsUnselectedHeadingLevelsOutOfTheAnchorMapping(): void
    {
        $processor = new ContentProcessor(
            new HeadingParser(),
            new AnchorGenerator(),
            new TocBuilder()
        );

        $result = $processor->process(
            '<h2>First</h2><h4>Ignored</h4><h2>Second</h2>',
            [2]
        );

        self::assertStringContainsString('<h2 id="first">First</h2>', $result['content']);
        self::assertStringContainsString('<h4>Ignored</h4>', $result['content']);
        self::assertStringContainsString('<h2 id="second">Second</h2>', $result['content']);
        self::assertCount(2, $result['tree']->headings());
    }
}
