<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\TOC;

defined('ABSPATH') || exit;

final class HeadingTree
{
    /**
     * @param list<HeadingNode> $roots
     */
    public function __construct(private readonly array $roots)
    {
    }

    /**
     * @return list<HeadingNode>
     */
    public function roots(): array
    {
        return $this->roots;
    }

    public function isEmpty(): bool
    {
        return $this->roots === [];
    }

    /**
     * @return list<Heading>
     */
    public function headings(): array
    {
        $headings = [];

        $walk = static function (array $nodes) use (&$headings, &$walk): void {
            foreach ($nodes as $node) {
                $headings[] = $node->heading();
                $walk($node->children());
            }
        };

        $walk($this->roots);

        return $headings;
    }
}
