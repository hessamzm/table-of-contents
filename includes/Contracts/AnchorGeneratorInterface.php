<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Contracts;

use Hessamzm\TableOfContents\TOC\Heading;

defined('ABSPATH') || exit;

interface AnchorGeneratorInterface
{
    /**
     * @param list<Heading> $headings
     * @return list<Heading>
     */
    public function assign(array $headings): array;
}
