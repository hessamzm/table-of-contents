<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Integrations\RankMath;

defined('ABSPATH') || exit;

final class RankMathIntegration
{
    public function boot(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        add_filter('rank_math/researches/toc_plugins', [$this, 'registerTocPlugin']);
    }

    /**
     * @param array<string,string> $tocPlugins
     * @return array<string,string>
     */
    public function registerTocPlugin(array $tocPlugins): array
    {
        $tocPlugins[plugin_basename(HESSAMZM_TOC_FILE)] = __('Table of Contents', 'table-of-contents');

        return $tocPlugins;
    }

    private function isEnabled(): bool
    {
        if (!defined('RANK_MATH_VERSION')) {
            return false;
        }

        return (bool) apply_filters(
            'hessamzm_toc/rank_math_integration_enabled',
            true
        );
    }
}
