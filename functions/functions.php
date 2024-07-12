<?php if (!defined('ABSPATH')) {
    die;
}
function zibll_plugin_activate()
{
    add_option('zibll_plugin_do_activation_redirect', true);
}

function zibll_plugin_redirect()
{
    if (get_option('zibll_plugin_do_activation_redirect', false)) {
        delete_option('zibll_plugin_do_activation_redirect');
        wp_redirect(admin_url('admin.php?page=zibll_plugin_seeting'));
    }
}


if (_pluginpz('enhanced_post_editor')) {
    add_filter('page_template', 'zibll_plugin_new_edit_post');
}

function zibll_plugin_new_edit_post($page_template)
{
    if (get_page_template_slug() == 'pages/newposts.php') {
        $page_template = ZIBLL_PLUGIN_DIRNAME . '/tool/page/newpost.php';
    }
    return $page_template;
}
function plugin_zib_get_data_avatar($user_id = '', $size = '', $alt = '')
{
    $args = array(
        'size'   => $size,
        'height' => $size,
        'width'  => $size,
        'alt'    => $alt,
    );
    $cache = wp_cache_get($user_id, 'user_avatar', true);
    if (false === $cache) {
        $avatar = zib_get_avatar(null, $user_id, $args);
        wp_cache_set($user_id, $avatar, 'user_avatar');
    } else {
        $avatar = $cache;
    }
    if (zib_is_lazy('lazy_avatar')) {
        $avatar = str_replace('">', ' photo"> ', $avatar);
        $avatar = str_replace('class="avatar', 'class="', $avatar);
        $avatar = str_replace('>', 'height="40" width="40">', $avatar);
    }
    return $avatar;
}

