<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Contracts;

use Hessamzm\TableOfContents\TOC\HeadingTree;

defined('ABSPATH') || exit;

interface TocBuilderInterface
{
    /**
     * @param list<\Hessamzm\TableOfContents\TOC\Heading> $headings
     */
    public function build(array $headings): HeadingTree;
}
