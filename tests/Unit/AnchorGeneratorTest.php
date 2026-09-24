<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Tests\Unit;

use Hessamzm\TableOfContents\TOC\AnchorGenerator;
use Hessamzm\TableOfContents\TOC\Heading;
use PHPUnit\Framework\TestCase;

final class AnchorGeneratorTest extends TestCase
{
    public function testItPreservesExistingIdsAndMakesDuplicatesUnique(): void
    {
        $generator = new AnchorGenerator();
        $headings = [
            new Heading(2, 'Intro', 'Intro', 'intro'),
            new Heading(2, 'Intro', 'Intro', 'intro'),
            new Heading(2, 'Other', 'Other', null),
        ];

        $result = $generator->assign($headings);

        self::assertSame('intro', $result[0]->id());
        self::assertSame('intro-2', $result[1]->id());
        self::assertSame('other', $result[2]->id());
    }
}
