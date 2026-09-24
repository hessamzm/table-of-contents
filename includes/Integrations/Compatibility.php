<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Integrations;

defined('ABSPATH') || exit;

final class Compatibility
{
    public function boot(): void
    {
        add_filter('hessamzm_toc/heading_levels', [$this, 'normalizeHeadingLevels']);
        add_filter('hessamzm_toc/post_types', [$this, 'normalizePostTypes']);
    }

    /**
     * @param mixed $levels
     * @return list<int>
     */
    public function normalizeHeadingLevels(mixed $levels): array
    {
        if (!is_array($levels)) {
            return [];
        }

        $levels = array_map('absint', $levels);
        $levels = array_values(array_unique(
            array_filter(
                $levels,
                static fn (int $level): bool => $level >= 1 && $level <= 6
            )
        ));

        sort($levels);

        return $levels;
    }

    /**
     * @param mixed $postTypes
     * @return list<string>
     */
    public function normalizePostTypes(mixed $postTypes): array
    {
        if (!is_array($postTypes)) {
            return [];
        }

        $postTypes = array_map('sanitize_key', $postTypes);

        return array_values(array_unique(array_filter(
            $postTypes,
            static fn (string $postType): bool => post_type_exists($postType)
        )));
    }
}
