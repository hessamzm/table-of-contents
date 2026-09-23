<?php
/**
 * Plugin Name: Table of Contents
 * Plugin URI: https://github.com/hessamzm/table-of-contents
 * Description: Automatic, extensible table of contents engine for WordPress content.
 * Version: 0.1.0
 * Requires at least: 7.0
 * Requires PHP: 8.2
 * Author: hessamzm
 * Author URI: https://github.com/hessamzm
 * Text Domain: table-of-contents
 * Domain Path: /languages
 *
 * @package Hessamzm\TableOfContents
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('HESSAMZM_TOC_VERSION', '0.1.0');
define('HESSAMZM_TOC_FILE', __FILE__);
define('HESSAMZM_TOC_DIR', plugin_dir_path(__FILE__));
define('HESSAMZM_TOC_URL', plugin_dir_url(__FILE__));

require_once HESSAMZM_TOC_DIR . 'includes/Contracts/HeadingParserInterface.php';
require_once HESSAMZM_TOC_DIR . 'includes/Contracts/AnchorGeneratorInterface.php';
require_once HESSAMZM_TOC_DIR . 'includes/Contracts/TocBuilderInterface.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/Heading.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/HeadingParser.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/AnchorGenerator.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/HeadingTree.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/TocBuilder.php';
require_once HESSAMZM_TOC_DIR . 'includes/Core/Plugin.php';

add_action(
    'plugins_loaded',
    static function (): void {
        (new \Hessamzm\TableOfContents\Core\Plugin())->boot();
    }
);
