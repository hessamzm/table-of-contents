<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\Core;

use Hessamzm\TableOfContents\TOC\AnchorGenerator;
use Hessamzm\TableOfContents\TOC\HeadingParser;
use Hessamzm\TableOfContents\TOC\HeadingTree;
use Hessamzm\TableOfContents\TOC\TocBuilder;

defined('ABSPATH') || exit;

final class Plugin
{
    private HeadingParser $parser;
    private AnchorGenerator $anchorGenerator;
    private TocBuilder $tocBuilder;

    public function __construct()
    {
        $this->parser = new HeadingParser();
        $this->anchorGenerator = new AnchorGenerator();
        $this->tocBuilder = new TocBuilder();
    }

    public function boot(): void
    {
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
