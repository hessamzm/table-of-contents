<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\TOC;

use Hessamzm\TableOfContents\Contracts\HeadingParserInterface;

defined('ABSPATH') || exit;

final class HeadingParser implements HeadingParserInterface
{
    /**
     * @param list<int> $levels
     * @return list<Heading>
     */
    public function parse(string $content, array $levels = [2, 3, 4, 5, 6]): array
    {
        $levels = array_values(
            array_filter(
                array_map('intval', $levels),
                static fn (int $level): bool => $level >= 1 && $level <= 6
            )
        );

        if ($content === '' || $levels === []) {
            return [];
        }

        $pattern = '/<h([1-6])\b([^>]*)>(.*?)<\/h\1>/is';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $headings = [];

        foreach ($matches as $match) {
            $level = (int) $match[1];

            if (!in_array($level, $levels, true)) {
                continue;
            }

            $attributes = (string) $match[2];
            $html = (string) $match[3];
            $text = trim(wp_strip_all_tags($html));
            $existingId = $this->extractId($attributes);

            if ($text === '') {
                continue;
            }

            $headings[] = new Heading(
                $level,
                $text,
                $html,
                $existingId
            );
        }

        return $headings;
    }

    private function extractId(string $attributes): ?string
    {
        if (preg_match('/\bid\s*=\s*(["\'])(.*?)\1/i', $attributes, $match)) {
            $id = sanitize_title_with_dashes($match[2]);

            return $id !== '' ? $id : null;
        }

        return null;
    }
}
