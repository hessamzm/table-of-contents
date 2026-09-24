<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\TOC;

defined('ABSPATH') || exit;

final class ContentProcessor
{
    public function __construct(
        private readonly HeadingParser $parser,
        private readonly AnchorGenerator $anchorGenerator,
        private readonly TocBuilder $tocBuilder,
    ) {
    }

    /**
     * @param list<int> $levels
     * @return array{content:string,tree:HeadingTree}
     */
    public function process(string $content, array $levels = [2, 3, 4, 5, 6]): array
    {
        $headings = $this->parser->parse($content, $levels);

        if ($headings === []) {
            return [
                'content' => $content,
                'tree' => new HeadingTree([]),
            ];
        }

        $headings = $this->anchorGenerator->assign($headings);
        $tree = $this->tocBuilder->build($headings);

        return [
            'content' => $this->injectAnchors($content, $headings, $levels),
            'tree' => $tree,
        ];
    }

    /**
     * @param list<Heading> $headings
     */
    private function injectAnchors(string $content, array $headings, array $levels): string
    {
        $headingIndex = 0;

        $pattern = '/<h([1-6])\b([^>]*)>(.*?)<\/h\1>/is';

        $processed = preg_replace_callback(
            $pattern,
            function (array $match) use ($headings, $levels, &$headingIndex): string {
                $level = (int) $match[1];

                if (!in_array($level, $levels, true)) {
                    return $match[0];
                }

                if (!isset($headings[$headingIndex])) {
                    return $match[0];
                }

                $heading = $headings[$headingIndex];
                ++$headingIndex;

                if ($heading->level() !== $level) {
                    return $match[0];
                }
                $text = trim(wp_strip_all_tags((string) $match[3]));

                if ($text !== $heading->text()) {
                    return $match[0];
                }

                $id = $heading->id();

                if ($id === null) {
                    return $match[0];
                }

                $attributes = (string) $match[2];

                if (preg_match('/\bid\s*=\s*(["\'])(.*?)\1/i', $attributes, $idMatch)) {
                    if ($idMatch[2] === $id) {
                        return $match[0];
                    }

                    $attributes = preg_replace(
                        '/\bid\s*=\s*(["\']).*?\1/i',
                        'id="' . esc_attr($id) . '"',
                        $attributes,
                        1
                    ) ?? $attributes;
                } else {
                    $attributes = rtrim($attributes);
                    $attributes .= ' id="' . esc_attr($id) . '"';
                }

                return '<h' . $match[1] . $attributes . '>' . $match[3] . '</h' . $match[1] . '>';
            },
            $content
        );

        return is_string($processed) ? $processed : $content;
    }
}
