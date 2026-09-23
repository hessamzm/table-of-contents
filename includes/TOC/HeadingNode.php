<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\TOC;

defined('ABSPATH') || exit;

final class HeadingNode
{
    /**
     * @param list<HeadingNode> $children
     */
    public function __construct(
        private readonly Heading $heading,
        private readonly array $children = [],
    ) {
    }

    public function heading(): Heading
    {
        return $this->heading;
    }

    /**
     * @return list<HeadingNode>
     */
    public function children(): array
    {
        return $this->children;
    }

    public function withChild(self $child): self
    {
        $children = $this->children;
        $children[] = $child;

        return new self($this->heading, $children);
    }
}
