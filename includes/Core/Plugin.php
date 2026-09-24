<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Core;

use Hessamzm\TableOfContents\Frontend\AutomaticRenderer;
use Hessamzm\TableOfContents\Integrations\RankMath\RankMathIntegration;
use Hessamzm\TableOfContents\Admin\SettingsPage;
use Hessamzm\TableOfContents\Frontend\TocRenderer;
use Hessamzm\TableOfContents\Frontend\TocAssets;
use Hessamzm\TableOfContents\Blocks\TableOfContentsBlock;
use Hessamzm\TableOfContents\Shortcodes\TableOfContentsShortcode;
use Hessamzm\TableOfContents\Services\ManualTocRenderer;
use Hessamzm\TableOfContents\Settings\Settings;
use Hessamzm\TableOfContents\TOC\AnchorGenerator;
use Hessamzm\TableOfContents\TOC\ContentProcessor;
use Hessamzm\TableOfContents\TOC\HeadingParser;
use Hessamzm\TableOfContents\TOC\HeadingTree;
use Hessamzm\TableOfContents\TOC\TocBuilder;

defined('ABSPATH') || exit;

final class Plugin
{
    private HeadingParser $parser;
    private AnchorGenerator $anchorGenerator;
    private TocBuilder $tocBuilder;
    private ContentProcessor $contentProcessor;
    private TocRenderer $tocRenderer;
    private AutomaticRenderer $automaticRenderer;
    private Settings $settings;
    private SettingsPage $settingsPage;
    private TableOfContentsBlock $block;
    private TableOfContentsShortcode $shortcode;
    private RankMathIntegration $rankMathIntegration;

    public function __construct()
    {
        $this->settings = new Settings();
        $this->settingsPage = new SettingsPage($this->settings);
        $this->parser = new HeadingParser();
        $this->anchorGenerator = new AnchorGenerator();
        $this->tocBuilder = new TocBuilder();
        $this->contentProcessor = new ContentProcessor(
            $this->parser,
            $this->anchorGenerator,
            $this->tocBuilder
        );
        $this->tocRenderer = new TocRenderer($this->settings);
        $assets = new TocAssets($this->settings);
        $manualRenderer = new ManualTocRenderer(
            $this->contentProcessor,
            $this->tocRenderer,
            $this->settings,
            $assets
        );
        $this->block = new TableOfContentsBlock($manualRenderer);
        $this->shortcode = new TableOfContentsShortcode($manualRenderer);
        $this->rankMathIntegration = new RankMathIntegration();
        $this->automaticRenderer = new AutomaticRenderer(
            $this->contentProcessor,
            $this->tocRenderer,
            $this->settings,
            $assets
        );
    }

    public function boot(): void
    {
        $this->settingsPage->boot();
        $this->automaticRenderer->boot();
        $this->block->boot();
        $this->shortcode->boot();
        $this->rankMathIntegration->boot();

        /**
         * Fires after the Table of Contents core services are initialized.
         *
         * @param Plugin $plugin
         */
        do_action('hessamzm_toc/booted', $this);
    }

    public function build(string $content, array $levels = [2, 3, 4, 5, 6]): HeadingTree
    {
        $headings = $this->parser->parse($content, $levels);
        $headings = $this->anchorGenerator->assign($headings);

        return $this->tocBuilder->build($headings);
    }
}
