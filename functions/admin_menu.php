<?php if (!defined('ABSPATH')) {
    die;
}


$prefix = 'zibll_plugin_seeting';
if (!function_exists('get_plugin_data')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
$pluginVersion = get_plugin_data(ZIBLL_PLUGIN_FILE);
CSF::createOptions($prefix, array(
    'menu_title' => '子比插件设置',
    'menu_slug'  => 'zibll_plugin_seeting',
    'framework_title'    => '子比插件《非官方》<small>V' . $pluginVersion['Version'] . '</small>',
    'theme'  => 'light'
));
CSF::createSection(
    $prefix,
    array(
        'title'  => '采集',
        'id' => 'gather_setting',
        'icon'   => 'fa fa-fw fa-puzzle-piece',
        'fields'      => array(
            array(
                'id'    => 'plush_switch',
                'title'    => '是否开启帖子发布接口',
                'desc'    => '
                    控制帖子发布接口是否开启
                    <p>使用时请先保存一遍，此功能依赖RestAPI，请勿禁用</p>
                    <li>查看发布接口是否生效<a target="_blank" href="/wp-json/zibll_plug/v1">点击查看</a></li>
                    ',
                'type'     => "switcher",
                'default'  => false,
            ),
            array(
                'dependency'  => array('plush_switch', '==', true),
                'id'    => 'plush_password',
                'title'    => '发布密码',
                'desc'    => '发布帖子密码，请设置以防未经授权发布内容',
                'type'  => 'text',
            ),
            array(
                'dependency'  => array('plush_switch', '==', true),
                'id'    => 'plush_userid',
                'title'    => '发布用户id',
                'desc'    => '发布用户id，不填默认为1，只能填写一个id，请确认用户存在，以免发生错误',
                'type'  => 'text',
            ),
            array(
                'dependency'  => array('plush_switch', '==', true),
                'id'    => 'plush_post_status',
                'title'    => '发布文章状态',
                'desc'    => '接口发布文章状态',
                'default'    => 'pending',
                'type'    => "select",
                'options' => array(
                    'publish'     => '发布',
                    'draft' => '草稿',
                    'pending' => '待审核',
                    // 'future'  => '',
                    // 'private'  => '',
                ),
            ),
        )
    )
);
CSF::createSection(
    $prefix,
    array(
        'title'  => '弹幕',
        'id' => 'theme_enhance',
        'icon'   => 'fa fa-fw fa-puzzle-piece',
        'description' => '',
        'fields'      => array(
            array(
                'id'    => 'zibll_plugin_danmu',
                'title'    => '是否开启页面右下角弹幕',
                'desc'    => '在页面右下角显示网站付费弹幕，移动端不显示',
                'type'     => "switcher",
                'default'  => false,
            ),
            array(
                'id' => 'danmu_count',
                'title'    => '弹幕数量',
                'desc'    => '订单数量请根据服务器配置调整，请勿设置过大，避免影响服务器运行',
                'type'     => 'spinner',
                'default'  => 10,
                'max'      => 100,
                'min'      => 10,
                'step'     => 5,
                'unit'     => '条',
                'dependency'  => array('zibll_plugin_danmu', '==', true),
            ),
            array(
                'id'    => 'danmu_time',
                'title'    => '弹幕切换时间',
                'desc'    => '弹幕显示时间',
                'type'     => 'spinner',
                'default'  => 2500,
                'step'     => 2500,
                'unit'     => '毫秒',
                'dependency'  => array('zibll_plugin_danmu', '==', true),
            ),
            // 弹幕显示行数
            array(
                'id'    => 'danmu_row',
                'title'    => '弹幕显示行数',
                'desc'    => '弹幕显示行数',
                'type'     => 'spinner',
                'default'  => 3,
                'step'     => 1,
                'min'      => 1,
                'max'      => 10,
                'unit'     => '行',
                'dependency'  => array('zibll_plugin_danmu', '==', true),
            ),
            array(
                'id'    => 'danmu_gap',
                'title'    => '弹幕间隙',
                'desc'    => '弹幕间隙',
                'type'     => 'spinner',
                'default'  => 15,
                'step'     => 1,
                'min'      => 1,
                'max'      => 100,
                'unit'     => 'px',
                'dependency'  => array('zibll_plugin_danmu', '==', true),
            ),
            array(
                'id'    => 'danmu_ismoseoverclose',
                'title'    => '悬浮是否停止',
                'desc'    => '悬浮是否停止',
                'type'     => "switcher",
                'default'  => true,
                'dependency'  => array('zibll_plugin_danmu', '==', true),
            ),
            array(
                'id'    => 'danmu_bgcolor',
                'title'    => '弹幕背景颜色',
                'desc'    => '弹幕背景颜色',
                'type'     => 'color',
                'default'  => 'rgba(0,0,0,0.35)',
                'dependency'  => array('zibll_plugin_danmu', '==', true),
            ),
            array(
                'id'    => 'danmu_right_margin',
                'title'    => '右边距',
                'desc'    => '距离浏览器右边距',
                'type'     => 'spinner',
                'default'  => 60,
                'step'     => 5,
                'min'      => 1,
                'unit'     => 'px',
                'dependency'  => array('zibll_plugin_danmu', '==', true),
            ),
        )
    )
);
CSF::createSection(
    $prefix,
    array(
        'title'  => '关于插件',
        'id' => 'about_plugin',
        'icon'   => 'fa fa-fw fa-info-circle',
        'fields'      => array(
            array(
                'type'    => 'submessage',
                'style'   => 'warning',
                'content' => '<h3 style="color:#fd4c73;"><i class="fa fa-heart fa-fw"></i> 感谢您使用Zibll子比插件</h3>
                <p>此插件非官方发布，无加密无后门</p>
                <p>欢迎各位老板联系定制WordPress插件、二开</p> 
                <p>创作不易，请保留此说明</p>
                <div style="margin:10px 14px;"><li>作者网站：<a target="_bank" href="https://www.bxmao.net/">https://www.bxmao.net</a></li>
                <li>作者联系方式1：<a href="http://wpa.qq.com/msgrd?v=3&amp;uin=417870308&amp;site=qq&amp;menu=yes">QQ 417870308</a></li>
                <li>作者联系方式2：<a href="http://wpa.qq.com/msgrd?v=3&amp;uin=544885752&amp;site=qq&amp;menu=yes">QQ 544885752</a></li>
                </div>',
            ),
        )
    )
);
