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
        /**
         * @var list<array{heading:Heading,children:list<array{heading:Heading,children:list<mixed>}>}> $roots
         */
        $roots = [];

        /**
         * @var list<array{level:int,children:array}> $stack
         */
        $stack = [];

        foreach ($headings as $heading) {
            $entry = [
                'heading' => $heading,
                'children' => [],
            ];

            while ($stack !== [] && $stack[array_key_last($stack)]['level'] >= $heading->level()) {
                array_pop($stack);
            }

            if ($stack === []) {
                $roots[] = $entry;
                $rootIndex = array_key_last($roots);
                $stack[] = [
                    'level' => $heading->level(),
                    'children' => &$roots[$rootIndex]['children'],
                ];
                continue;
            }

            $parentChildren = &$stack[array_key_last($stack)]['children'];
            $parentChildren[] = $entry;
            $childIndex = array_key_last($parentChildren);

            $stack[] = [
                'level' => $heading->level(),
                'children' => &$parentChildren[$childIndex]['children'],
            ];
        }

        return new HeadingTree($this->toNodes($roots));
    }

    /**
     * @param list<array{heading:Heading,children:array}> $entries
     * @return list<HeadingNode>
     */
    private function toNodes(array $entries): array
    {
        $nodes = [];

        foreach ($entries as $entry) {
            $nodes[] = new HeadingNode(
                $entry['heading'],
                $this->toNodes($entry['children'])
            );
        }

        return $nodes;
    }
}
