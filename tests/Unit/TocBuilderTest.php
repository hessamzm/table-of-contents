<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Tests\Unit;

use Hessamzm\TableOfContents\TOC\Heading;
use Hessamzm\TableOfContents\TOC\TocBuilder;
use PHPUnit\Framework\TestCase;

final class TocBuilderTest extends TestCase
{
    public function testItBuildsNestedHeadingTree(): void
    {
        $builder = new TocBuilder();
        $tree = $builder->build([
            new Heading(2, 'A', 'A', 'a'),
            new Heading(3, 'B', 'B', 'b'),
            new Heading(2, 'C', 'C', 'c'),
        ]);

        self::assertCount(2, $tree->roots());
        self::assertCount(1, $tree->roots()[0]->children());
        self::assertSame('B', $tree->roots()[0]->children()[0]->heading()->text());
    }
}
