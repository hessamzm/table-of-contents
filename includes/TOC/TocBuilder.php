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
        /** @var list<HeadingNode> $roots */
        $roots = [];

        /**
         * Stack entries contain a reference to the current node's children.
         *
         * @var list<array{level:int,children:list<HeadingNode>}> $stack
         */
        $stack = [];

        foreach ($headings as $heading) {
            $node = new HeadingNode($heading);

            while ($stack !== [] && $stack[array_key_last($stack)]['level'] >= $heading->level()) {
                array_pop($stack);
            }

            if ($stack === []) {
                $roots[] = $node;
                $stack[] = [
                    'level' => $heading->level(),
                    'children' => &$roots,
                ];
                continue;
            }

            $parentChildren = &$stack[array_key_last($stack)]['children'];
            $parentIndex = array_key_last($parentChildren);
            $parentNode = $parentChildren[$parentIndex];
            $parentChildren[$parentIndex] = $parentNode->withChild($node);

            $children = $parentChildren[$parentIndex]->children();
            $stack[] = [
                'level' => $heading->level(),
                'children' => &$children,
            ];
        }

        return new HeadingTree($roots);
    }
}
