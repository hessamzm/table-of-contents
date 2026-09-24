<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Core;

use Hessamzm\TableOfContents\Settings\Settings;

defined('ABSPATH') || exit;

final class Lifecycle
{
    public static function activate(): void
    {
        if (get_option(Settings::OPTION_KEY, null) === null) {
            add_option(Settings::OPTION_KEY, (new Settings())->defaults());
        }

        do_action('hessamzm_toc/activated');
    }

    public static function deactivate(): void
    {
        do_action('hessamzm_toc/deactivated');
    }
}
