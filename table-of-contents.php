<?php
/**
 * Plugin Name: Table of Contents
 * Plugin URI: https://github.com/hessamzm/table-of-contents
 * Description: Automatic, extensible table of contents engine for WordPress content.
 * Version: 1.0.2
 * Requires at least: 7.0
 * Requires PHP: 8.2
 * WC requires at least: 10.0
 * Author: hessamzm
 * Author URI: https://github.com/hessamzm
 * Text Domain: table-of-contents
 * Domain Path: /languages
 *
 * @package Hessamzm\\TableOfContents
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('HESSAMZM_TOC_VERSION', '1.0.0');
define('HESSAMZM_TOC_FILE', __FILE__);
define('HESSAMZM_TOC_DIR', plugin_dir_path(__FILE__));
define('HESSAMZM_TOC_URL', plugin_dir_url(__FILE__));

require_once HESSAMZM_TOC_DIR . 'includes/Contracts/HeadingParserInterface.php';
require_once HESSAMZM_TOC_DIR . 'includes/Contracts/AnchorGeneratorInterface.php';
require_once HESSAMZM_TOC_DIR . 'includes/Contracts/TocBuilderInterface.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/Heading.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/HeadingNode.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/HeadingParser.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/AnchorGenerator.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/HeadingTree.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/TocBuilder.php';
require_once HESSAMZM_TOC_DIR . 'includes/TOC/ContentProcessor.php';
require_once HESSAMZM_TOC_DIR . 'includes/Settings/Settings.php';
require_once HESSAMZM_TOC_DIR . 'includes/Core/Lifecycle.php';
require_once HESSAMZM_TOC_DIR . 'includes/Services/ManualTocRenderer.php';
require_once HESSAMZM_TOC_DIR . 'includes/Blocks/TableOfContentsBlock.php';
require_once HESSAMZM_TOC_DIR . 'includes/Shortcodes/TableOfContentsShortcode.php';
require_once HESSAMZM_TOC_DIR . 'includes/Widgets/TableOfContentsWidget.php';
require_once HESSAMZM_TOC_DIR . 'includes/Admin/SettingsPage.php';
require_once HESSAMZM_TOC_DIR . 'includes/Frontend/TocRenderer.php';
require_once HESSAMZM_TOC_DIR . 'includes/Frontend/TocAssets.php';
require_once HESSAMZM_TOC_DIR . 'includes/Frontend/AutomaticRenderer.php';
require_once HESSAMZM_TOC_DIR . 'includes/Integrations/RankMath/RankMathIntegration.php';
require_once HESSAMZM_TOC_DIR . 'includes/Integrations/SEO/SeoTocCompatibility.php';
require_once HESSAMZM_TOC_DIR . 'includes/Integrations/WooCommerce/ProductTocRenderer.php';
require_once HESSAMZM_TOC_DIR . 'includes/Integrations/WooCommerce/WooCommerceIntegration.php';
require_once HESSAMZM_TOC_DIR . 'includes/Core/Plugin.php';

register_activation_hook(
    HESSAMZM_TOC_FILE,
    ['\\Hessamzm\\TableOfContents\\Core\\Lifecycle', 'activate']
);

register_deactivation_hook(
    HESSAMZM_TOC_FILE,
    ['\\Hessamzm\\TableOfContents\\Core\\Lifecycle', 'deactivate']
);

add_action(
    'init',
    static function (): void {
        load_plugin_textdomain(
            'table-of-contents',
            false,
            dirname(plugin_basename(HESSAMZM_TOC_FILE)) . '/languages'
        );
    },
    1
);

add_action(
    'before_woocommerce_init',
    static function (): void {
        if (!class_exists('Automattic\\WooCommerce\\Utilities\\FeaturesUtil')) {
            return;
        }

        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            HESSAMZM_TOC_FILE,
            true
        );

        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'product_block_editor',
            HESSAMZM_TOC_FILE,
            true
        );
    }
);

add_action(
    'plugins_loaded',
    static function (): void {
        (new \Hessamzm\TableOfContents\Core\Plugin())->boot();
    },
    20
);
