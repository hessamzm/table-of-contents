<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Widgets;

use Hessamzm\TableOfContents\Services\ManualTocRenderer;

defined('ABSPATH') || exit;

final class TableOfContentsWidget extends \WP_Widget
{
    public const ID = 'hessamzm_toc_widget';

    public function __construct(private readonly ManualTocRenderer $renderer)
    {
        parent::__construct(
            self::ID,
            __('Table of Contents', 'table-of-contents'),
            [
                'classname' => self::ID,
                'description' => __('Displays a table of contents generated from the current post or product content.', 'table-of-contents'),
                'show_instance_in_rest' => true,
            ]
        );
    }

    public function boot(): void
    {
        add_action('widgets_init', [$this, 'register']);
    }

    public function register(): void
    {
        register_widget($this);
    }

    public function widget($args, $instance): void
    {
        if (!$this->isSupportedContext()) {
            return;
        }

        $toc = $this->renderer->render();

        if ($toc === '') {
            return;
        }

        echo $args['before_widget'];
        echo $toc;
        echo $args['after_widget'];
    }

    public function form($instance): void
    {
        echo '<p>' . esc_html__('This widget uses the current post or product content and the global TOC settings.', 'table-of-contents') . '</p>';
    }

    /** @param array<string,mixed> $new_instance @param array<string,mixed> $old_instance @return array<string,mixed> */
    public function update($new_instance, $old_instance): array
    {
        return [];
    }

    public function isSupportedContext(): bool
    {
        if (is_admin() || !is_singular()) {
            return false;
        }

        $postType = get_post_type();

        return in_array($postType, ['post', 'product'], true);
    }

    public static function isActive(): bool
    {
        return function_exists('is_active_widget')
            && (bool) is_active_widget(false, false, self::ID, true);
    }
}
