<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\TOC;

defined('ABSPATH') || exit;

final class HeadingTree
{
    /**
     * @param list<Heading> $headings
     */
    public function __construct(private readonly array $headings)
    {
    }

    /**
     * @return list<Heading>
     */
    public function headings(): array
    {
        return $this->headings;
    }

    public function isEmpty(): bool
    {
        return $this->headings === [];
    }
}
