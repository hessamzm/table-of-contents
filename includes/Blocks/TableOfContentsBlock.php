<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Blocks;

use Hessamzm\TableOfContents\Services\ManualTocRenderer;

defined('ABSPATH') || exit;

final class TableOfContentsBlock
{
    public const NAME = 'hessamzm/table-of-contents';

    public function __construct(private readonly ManualTocRenderer $renderer)
    {
    }

    public function boot(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        $blockPath = HESSAMZM_TOC_DIR . 'blocks/table-of-contents';

        if (!file_exists($blockPath . '/block.json')) {
            return;
        }

        wp_register_script(
            'hessamzm-toc-block-editor',
            HESSAMZM_TOC_URL . 'blocks/table-of-contents/index.js',
            ['wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n'],
            HESSAMZM_TOC_VERSION,
            true
        );

        register_block_type(
            $blockPath,
            [
                'editor_script' => 'hessamzm-toc-block-editor',
                'render_callback' => [$this, 'render'],
            ]
        );
    }

    /**
     * @param array<string,mixed> $attributes
     */
    public function render(array $attributes, string $content = ''): string
    {
        return $this->renderer->render($attributes);
    }
}
