<?php if (!defined('ABSPATH')) {
    die;
}
/*
Plugin Name: zibll插件
Plugin URI: https://www.bxmao.net
Description: 白熊猫zibll插件
Version: 1.4.0
Requires PHP: 7.0
Author: 白熊猫
Author URI: https://www.bxmao.net
Update URI: 
WordPress requires at least: 6.0
*/
define('ZIBLL_PLUGIN_FILE', __FILE__);
define('ZIBLL_PLUGIN_DIRNAME', dirname(__FILE__));
if (!file_exists(get_theme_file_path('/inc/codestar-framework/codestar-framework.php'))) {
    return;
}
require_once(ZIBLL_PLUGIN_DIRNAME . '/functions/require.php');
register_activation_hook(__FILE__, 'zibll_plugin_activate');
add_action('admin_init', 'zibll_plugin_redirect');

function _pluginpz($name, $default = false, $subname = '')
{
    static $options = null;
    if ($options === null) {
        $options = get_option('zibll_plugin_seeting');
    }

    if (isset($options[$name])) {
        if ($subname) {
            return isset($options[$name][$subname]) ? $options[$name][$subname] : $default;
        } else {
            return $options[$name];
        }
    }
    return $default;
}



