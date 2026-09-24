<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Tests\Unit;

use Hessamzm\TableOfContents\TOC\HeadingParser;
use PHPUnit\Framework\TestCase;

final class HeadingParserTest extends TestCase
{
    public function testItParsesOnlySelectedHeadingLevels(): void
    {
        $parser = new HeadingParser();
        $headings = $parser->parse('<h1>Ignored</h1><h2>Intro</h2><h3 id="custom">Details</h3>', [2, 3]);

        self::assertCount(2, $headings);
        self::assertSame(2, $headings[0]->level());
        self::assertSame('Intro', $headings[0]->text());
        self::assertSame('custom', $headings[1]->existingId());
    }
}
