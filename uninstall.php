<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

$option_key = 'hessamzm_toc_settings';
$settings   = get_option($option_key, []);

if (is_array($settings) && !empty($settings['delete_data_on_uninstall'])) {
    delete_option($option_key);
}
