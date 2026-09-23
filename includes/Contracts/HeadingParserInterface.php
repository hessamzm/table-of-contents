<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Contracts;

use Hessamzm\TableOfContents\TOC\Heading;

defined('ABSPATH') || exit;

interface HeadingParserInterface
{
    /**
     * @return list<Heading>
     */
    public function parse(string $content, array $levels = [2, 3, 4, 5, 6]): array;
}
