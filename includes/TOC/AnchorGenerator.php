<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\TOC;

use Hessamzm\TableOfContents\Contracts\AnchorGeneratorInterface;

defined('ABSPATH') || exit;

final class AnchorGenerator implements AnchorGeneratorInterface
{
    /**
     * @param list<Heading> $headings
     * @return list<Heading>
     */
    public function assign(array $headings): array
    {
        $used = [];

        foreach ($headings as $index => $heading) {
            $id = $heading->existingId();

            if ($id === null) {
                $id = sanitize_title_with_dashes($heading->text());

                if ($id === '') {
                    $id = 'section-' . ($index + 1);
                }
            }

            $id = $this->uniqueId($id, $used);
            $used[$id] = true;

            $headings[$index] = $heading->withId($id);
        }

        return $headings;
    }

    /**
     * @param array<string, true> $used
     */
    private function uniqueId(string $base, array $used): string
    {
        if (!isset($used[$base])) {
            return $base;
        }

        $suffix = 2;

        do {
            $candidate = $base . '-' . $suffix;
            ++$suffix;
        } while (isset($used[$candidate]));

        return $candidate;
    }
}
