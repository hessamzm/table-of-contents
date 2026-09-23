<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\TOC;

use Hessamzm\TableOfContents\Contracts\TocBuilderInterface;

defined('ABSPATH') || exit;

final class TocBuilder implements TocBuilderInterface
{
    /**
     * @param list<Heading> $headings
     */
    public function build(array $headings): HeadingTree
    {
        return new HeadingTree($headings);
    }
}
