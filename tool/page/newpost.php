<?php

/**
 * Template name: 2333
 * Description:   用户前台发布文章的页面模板
 */

//引入核心文件
require_once get_theme_file_path('/inc/code/require.php');
require_once get_theme_file_path('/inc/code/file.php');

//前置判断，判断是否有编辑权限
$edit_id   = !empty($_REQUEST['edit']) ? $_REQUEST['edit'] : 0;
$edit_post = '';
if ($edit_id) {
    $edit_post = get_post($edit_id);
    if ((empty($edit_post->ID) || !zib_current_user_can('new_post_edit', $edit_post))) {
        wp_safe_redirect(home_url(remove_query_arg('edit')));
        return;
    }
    $edit_id = $edit_post->ID;
}

get_header();
$cuid = get_current_user_id();

//不显示悬浮按钮
remove_action('wp_footer', 'zib_float_right');

//不显示底部按钮
remove_action('wp_footer', 'zib_footer_tabbar');

if (!$cuid) {
    $btn_txet = '审核';
} else {
    $btn_txet = '发布';
}

if (!_pz('post_article_s') || zib_is_close_sign()) {
    get_template_part('template/content-404');
    get_footer();
    exit();
}

//编辑器按钮|上传图片
if (zib_current_user_can('new_post_upload_img')) {
    add_filter('tinymce_upload_img', '__return_true');
}

//编辑器按钮，上传视频
if (zib_current_user_can('new_post_upload_video')) {
    add_filter('tinymce_upload_video', '__return_true');
}

//编辑器按钮，嵌入视频
if (zib_current_user_can('new_post_iframe_video')) {
    add_filter('tinymce_iframe_video', '__return_true');
}

//编辑器按钮，隐藏内容
if (zib_current_user_can('new_post_hide')) {
    add_filter('tinymce_hide', '__return_true');
}

//编辑器按钮，付费功能
$can_new_post_pay = zib_current_user_can('new_post_pay');
if ($can_new_post_pay) {
    add_filter('tinymce_hide_pay', '__return_true');
}

//最近保存的草稿
//$draft_id = get_user_meta($cuid, 'posts_draft', true);

//准备参数
$is_can_new = zib_current_user_can('new_post_add');
$in         = array(
    'ID'           => '',
    'post_title'   => '',
    'post_content' => '',
    'view_btn'     => '',
    'uptime_badge' => '',
    'cat_id'       => '',
    'text_tags'    => '',
    'post_status'  => '',
);
if (!empty($edit_post->ID)) {
    $is_can_new         = true; //拥有编辑权限，则拥有此权限
    $in                 = array_merge($in, (array) $edit_post);
    $is_edit            = true;
    $in['view_btn']     = '<a class="but c-blue" href="' . get_permalink($edit_post) . '"><i class="fa fa-file-text-o"></i> 预览文章</a>';
    $in['uptime_badge'] = '<span class="badg">最后保存：' . $in['post_modified'] . '</span>';

    if (is_super_admin()) {
        $in['view_btn'] .= '<a class="but c-yellow ml6" href="' . get_edit_post_link($edit_post) . '">后台编辑</a>';
    }

    $the_category = get_the_category($edit_post->ID);
    $in['cat_id'] = !empty($the_category[0]->term_id) ? $the_category[0]->term_id : 0;

    $the_tags = get_the_tags($edit_post->ID);
    if ($the_tags) {
        $the_tags        = array_column((array) $the_tags, 'name');
        $in['text_tags'] = implode(', ', $the_tags);
    }
}

//文章封面
$featured_edit = '';
if (zib_current_user_can('new_post_image_cover')) {
    add_filter('featured_image_edit', '__return_true');

    if (zib_current_user_can('new_post_slide_cover')) {
        add_filter('featured_slide_edit', '__return_true');
    }
    if (zib_current_user_can('new_post_video_cover')) {
        add_filter('featured_video_edit', '__return_true');
    }

    $options   = array(
        'video_ratio' => 50, //比例
        'slide_ratio' => 50, //比例
        'image_ratio' => 50, //比例
    );

    $featured_edit = zib_get_post_featured_edit_box($edit_id, 'mb20', $options);
}

?>
<main role="main" class="container">
    <form>
        <div class="content-wrap newposts-wrap">
            <div class="content-layout">
                <div class="zib-widget full-widget-sm editor-main-box" style="min-height:60vh;">
                    <?php echo $featured_edit; ?>
                    <div class="relative newposts-title">
                        <textarea type="text" class="line-form-input input-lg new-title" name="post_title" tabindex="1" rows="1" autoHeight="true" maxHeight="<?php echo (wp_is_mobile() ? 110 : 78); ?>" placeholder="<?php echo _pz('post_t_placeholder', '请输入标题'); ?>"><?php echo esc_attr($in['post_title']); ?></textarea>
                        <i class="line-form-line"></i>
                    </div>
                    <?php

                    if (!$is_can_new) {
                        echo '<div class="flex jc" style="min-height:50vh;">';
                        echo zib_get_nocan_info($cuid, 'new_post_add', '无法发布');
                        echo '</div>';
                    } else {
                        $editor_id = 'post_content';
                        $settings  = array(
                            'textarea_rows'  => 20,
                            'editor_height'  => (wp_is_mobile() ? 460 : 470),
                            'media_buttons'  => false,
                            'default_editor' => 'tinymce',
                            'quicktags'      => false,
                            'editor_css'     => '<link rel="stylesheet" href="' . ZIB_TEMPLATE_DIRECTORY_URI . '/css/new-posts.min.css?ver=' . THEME_VERSION . '" type="text/css">',
                            'teeny'          => false,
                            'tinymce'        => array(
                                'placeholder' => _pz('post_c_placeholder', '请输入内容'),
                            ),
                        );
                        wp_editor($in[$editor_id], $editor_id, $settings);
                    }

                    ?>
                    <?php echo '<div class="em09 flex ac hh"><span class="view-btn mr6 mt6">' . $in['view_btn'] . '</span><span class="modified-time mt6">' . $in['uptime_badge'] . '</span></div>'; ?>
                </div>
            </div>
        </div>

        <div class="sidebar show-sidebar">
            <?php dynamic_sidebar('newposts_sidebar_top'); ?>
            <?php if (!$cuid) {
            ?>
                <div class="main-bg theme-box radius8 main-shadow relative">
                    <div class="box-header">
                        <div class="title-theme">用户信息</div>
                    </div>
                    <div class="box-body">
                        <p class="muted-3-color em09">请输入昵称</p>
                        <div class="mb20">
                            <input class="form-control" name="user_name" placeholder="请输入昵称">
                        </div>
                        <p class="muted-3-color em09">请输入您的联系方式</p>
                        <input class="form-control" name="contact_details" placeholder="输入联系方式">
                    </div>
                </div>
            <?php } ?>
            <div class="theme-box">
                <div class="main-bg theme-box radius8 main-shadow relative">
                    <div class="box-header">
                        <div class="title-theme">文章分类</div>
                    </div>
                    <div class="box-body">
                        <p class="muted-3-color em09">请选择文章分类</p>
                        <div class="form-select">
                            <select class="form-control" name="category" tabindex="5">
                                <?php
                                $cat_ids = _pz('post_article_cat', array());

                                $cats = get_categories(array(
                                    'orderby'    => 'include',
                                    'include'    => $cat_ids,
                                    'hide_empty' => false,
                                ));

                                if ($cats) {
                                    foreach ($cats as $cat) {
                                        echo '<option value="' . $cat->term_id . '" ' . selected($cat->term_id, $in['cat_id'], false) . '>' . $cat->name . '</option>';
                                    }
                                } else {
                                    echo '<option value="1" selected="selected">' . get_category(1)->name . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                    </div>
                    <div class="box-header">
                        <div class="title-theme">文章标签</div>
                    </div>
                    <div class="box-body">
                        <p class="muted-3-color em09">填写文章的标签，每个标签用逗号隔开</p>
                        <textarea class="form-control" rows="3" name="tags" placeholder="输入文章标签" tabindex="6"><?php echo $in['text_tags']; ?></textarea>
                    </div>
                </div>
            </div>
            <?php
            if ($can_new_post_pay) {
                echo zib_newpost_get_paybox($in['ID']);
                echo errrrr($in['ID']);
            } ?>
            <div class="zib-widget">
                <div class="text-center">
                    <p class="separator muted-3-color theme-box">Are you ready</p>
                    <?php

                    echo '<input type="hidden" name="posts_id" value="' . (int) $in['ID'] . '">';
                    $btns = '';
                    if (!$is_can_new) {
                        echo '<p class="em09 muted-3-color theme-box">暂无发布权限</p>';
                    } else {
                        if ($cuid) {
                            if ('publish' !== $in['post_status'] && 'pending' !== $in['post_status']) {
                                $btns .= '<botton type="button" action="plugin_posts_draft" name="submit" class="but jb-green new-posts-submit padding-lg"><i class="fa fa-fw fa-dot-circle-o"></i>保存草稿</botton>';
                            } elseif ($in['post_status']) {
                                $btn_txet = '保存';
                            }
                        } else {
                            echo '<p class="em09 muted-3-color theme-box">您当前未登录，不能保存草稿，文章提交' . $btn_txet . '之后不可再修改！</p>';
                        }

                        //人机验证
                        if (_pz('verification_newposts_s')) {
                            $verification_input = zib_get_machine_verification_input('newposts_submit');
                            if ($verification_input) {
                                echo '<div >' . $verification_input . '</div>';
                            }
                        }

                        $btns .= '<botton type="button" action="plugin_posts_save" name="submit" class="ml10 but jb-blue new-posts-submit padding-lg"><i class="fa fa-fw fa-check-square-o"></i>提交' . $btn_txet . '</botton>';
                    }

                    echo $btns ? '<div class="but-average  ">' . $btns . '</div>' : '';
                    ?>
                </div>
            </div>
            <?php dynamic_sidebar('newposts_sidebar_bottom'); ?>
        </div>
    </form>
</main>
<?php get_footer();

//付费模块
function zib_newpost_get_paybox($in_id = 0)
{
    //默认参数
    $default = array(
        'pay_type'     => 'no',
        'pay_modo'     => '0',
        'points_price' => '',
        'vip_1_points' => '',
        'vip_2_points' => '',
        'pay_price'    => '',
        'vip_1_price'  => '',
        'vip_2_price'  => '',
    );
    $points_s = _pz('points_s');
    $user_id  = get_current_user_id();

    $pay_mate    = (array) get_post_meta($in_id, 'posts_zibpay', true);
    $in          = array_merge($default, $pay_mate);
    $in_s        = $in['pay_type'] && $in['pay_type'] !== 'no';
    $vip_input_s = _pz('post_article_pay_vip_price_s');
    $vip_1_s     = _pz('pay_user_vip_1_s');
    $vip_2_s     = _pz('pay_user_vip_2_s');
    $money_icon  = zib_get_svg('money-color-2', null, 'mr6 em12');

    //付费类型
    $pay_type_args = array(
        'no' => '关闭',
        '1'  => '付费阅读',
        '2'  => '付费资源',
    );
    $pay_type_input = '';
    foreach ($pay_type_args as $k => $v) {
        $pay_type_input .= '<label class="badg p2-10 mr10 pointer"><input type="radio"' . (checked($in['pay_type'], $k, false)) . ' name="pay_type" value="' . $k . '"> ' . $v . '</label>';
    }
    $pay_type_input = '<div><p class="muted-3-color em09">设置付费内容</p><div>' . $pay_type_input . '</div></div>';

    $pay_type_input = '<div class="flex ac jsb padding-h10 border-bottom">
    <div class="flex ac">内容付费</div>
    <label style="margin: 0;"><input class="hide" name="zibpay_s" type="checkbox"' . ($in_s ? ' checked="checked"' : '') . '><div class="form-switch flex0"></div></label>
</div>';

    //支付类型
    $pay_modo_input = '<input type="hidden" name="posts_zibpay[pay_modo]" value="' . $in['pay_modo'] . '">';
    if ($points_s) {
        $pay_modo_input .= '<div class="flex ac jsb padding-h10">
            <div class="flex ac">支付类型</div>
            <div class="but-average radius em09">
                <span data-for="posts_zibpay[pay_modo]" data-value="0" class="but p2-10 pointer' . ($in['pay_modo'] !== 'points' ? ' active' : '') . '">现金支付</span>
                <span data-for="posts_zibpay[pay_modo]" data-value="points" class="but p2-10 pointer' . ($in['pay_modo'] === 'points' ? ' active' : '') . '">积分支付</span>
            </div>
        </div>';
    }

    $vip_pay_price_input = '';
    if ($vip_input_s && $vip_1_s) {
        $vip_pay_price_input .= '<div class="relative mt6">
        <div class="flex ab">
            <div class="muted-color mb6 flex0">' . zibpay_get_vip_icon(1, 'em12 mr6', false) . _pz('pay_user_vip_1_name') . '价格</div><input type="number" name="posts_zibpay[vip_1_price]" value="' . $in['vip_1_price'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
            <i class="line-form-line"></i>
        </div>
    </div>';
    }
    if ($vip_input_s && $vip_2_s) {
        $vip_pay_price_input .= '<div class="relative mt6">
        <div class="flex ab">
            <div class="muted-color mb6 flex0">' . zibpay_get_vip_icon(2, 'em12 mr6', false) . _pz('pay_user_vip_2_name') . '价格</div><input type="number" name="posts_zibpay[vip_2_price]" value="' . $in['vip_2_price'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
            <i class="line-form-line"></i>
        </div>
    </div>';
    }
    if ($vip_pay_price_input) {
        $vip_pay_price_input .= '<div class="px12 mt6 muted-color">会员价不能高于普通价，为0则为会员免费</div>';
    }
    //设置金额
    $pay_price_input = '<div class="mt10" data-controller="posts_zibpay[pay_modo]" data-condition="!=" data-value="points"' . ($in['pay_modo'] === 'points' ? ' style="display: none;"' : '') . '>
    <div class="relative">
        <div class="flex ab">
            <div class="muted-color mb6 flex0">' . $money_icon . '设置价格</div><input type="number" name="posts_zibpay[pay_price]" value="' . $in['pay_price'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
            <i class="line-form-line"></i>
        </div>
    </div>' . $vip_pay_price_input . '</div>';

    //设置积分
    $vip_pay_price_input = '';
    if ($vip_input_s && $vip_1_s) {
        $vip_pay_price_input .= '<div class="relative mt6">
        <div class="flex ab">
            <div class="muted-color mb6 flex0">' . zibpay_get_vip_icon(1, 'em12 mr6', false) . _pz('pay_user_vip_1_name') . '积分</div><input type="number" name="posts_zibpay[vip_1_points]" value="' . $in['vip_1_points'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
            <i class="line-form-line"></i>
        </div>
    </div>';
    }
    if ($vip_input_s && $vip_2_s) {
        $vip_pay_price_input .= '<div class="relative mt6">
        <div class="flex ab">
            <div class="muted-color mb6 flex0">' . zibpay_get_vip_icon(2, 'em12 mr6', false) . _pz('pay_user_vip_2_name') . '积分</div><input type="number" name="posts_zibpay[vip_2_points]" value="' . $in['vip_2_points'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
            <i class="line-form-line"></i>
        </div>
    </div>';
    }
    if ($vip_pay_price_input) {
        $vip_pay_price_input .= '<div class="px12 mt6 muted-color">会员价不能高于普通价，为0则为会员免费</div>';
    }
    if ($points_s) {
        $pay_price_input .= '<div class="mt10" data-controller="posts_zibpay[pay_modo]" data-condition="==" data-value="points"' . ($in['pay_modo'] !== 'points' ? ' style="display: none;"' : '') . '><div class="relative">
    <div class="flex ab">
        <div class="muted-color mb6 flex0">' . zib_get_svg('points-color', null, 'mr6 em12') . '设置积分</div><input type="number" name="posts_zibpay[points_price]" value="' . $in['points_price'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
        <i class="line-form-line"></i>
    </div>
</div>' . $vip_pay_price_input . '</div>';
    }

    $desc = '如果您在文章中添加了付费可见的隐藏内容，请在此设置付费功能';
    if (_pz('pay_income_s')) {
        $income_ratio = zibpay_get_user_income_ratio($user_id);
        if ($income_ratio) {
            $desc .= '<div class="c-blue px12 mt6">您已参与创作分成，本文获得的收益将与您分成，您可以进入<a target="_blank" class="c-blue-2" href="' . zib_get_user_center_url('income') . '">用户中心-创作分成</a>查看您的分成比例及分成详情</div>';
        }
    }

    $html = '<div class="main-bg theme-box radius8 main-shadow relative dependency-box">
            <div class="box-header">
                <div class="title-theme">付费内容</div>
            </div>
            <div class="box-body">' . $pay_type_input . '
                <div data-controller="zibpay_s" data-condition="!=" data-value=""' . (!$in_s ? ' style="display: none;"' : '') . '>' . $pay_modo_input . $pay_price_input . '</div>
                <div class="em09 mt10 muted-2-color">' . $desc . '</div>
            </div>
        </div>';
    return $html;
}
function errrrr($id = 0, $class = 'zib-widget mb20')
{
    if (!get_current_user_id()) {
        //未登录
        return '<div class="vote-set signin-loader">
                        <div class="flex ac jsb drop-btn ' . $class . '">
                            <div class="flex ac"><i class="fa fa-bar-chart mr6"></i>投票</div>
                            <i class="ml6 fa fa-angle-right em12"></i>
                        </div>
                    </div>';
    }

    //权限判断
    if (($id && !zib_bbs_current_user_can('posts_vote_edit', $id)) || (!$id && !zib_bbs_current_user_can('posts_vote_add'))) {
        return '';
    }

    $vote_opt_null = [
        array(
            "link" => '',
            "more" => '',
            "icon" => "fa fa-download",
            "name" => ''
        )
    ];
    $vote_opt = array();
    $is_vote  = false;

    $default = array(
        'pay_type'     => 'no',
        'pay_modo'     => '0',
        'points_price' => '',
        'vip_1_points' => '',
        'vip_2_points' => '',
        'pay_price'    => '',
        'vip_1_price'  => '',
        'vip_2_price'  => '',
    );
    $pay_mate    = (array) get_post_meta($id, 'posts_zibpay', true);
    $in          = array_merge($default, $pay_mate);
    $is_vote        = $in['pay_type'] && $in['pay_type'] !== 'no';
    $selected = $in['pay_limit'] ?? '';
    $vote_opt = $in['pay_download'] ?? array();
    $opt = !empty($vote_opt)?$vote_opt:$vote_opt_null;

    //投票选项
    $option_html = '';
    foreach ($opt as $k) {
        $option_html .= '<div class="cloneable-item vote-opt-item form-right-icon mb6 ">
            <div>
            <input type="input" class="form-control" name="pay_download[name]" value="' . esc_attr($k['name']) . '" placeholder="按钮名称">
            <input type="input" class="form-control" name="pay_download[link]" value="' . esc_attr($k['link']) . '" placeholder="下载链接">
            <input type="input" class="form-control" name="pay_download[more]" value="' . esc_attr($k['more']) . '" placeholder="提取密码、解压密码等">
            </div>
            <a href="javascript:;" class="flex0 abs-right muted-color cloneable-remove">' . zib_get_svg('close') . '</a></div>
            ';
    }
    $option_html = '<div class="cloneable vote-options" data-max="' . (_pz('bbs_vote_max') ?: 8) . '" data-min="1">' . $option_html . '</div>';

    $option_html .= '<botton type="button" class="cloneable-add but block c-blue">' . zib_get_svg('add') . '添加选项</botton>';

    $option_html = '<div class="mb10"><div class="em09 muted-2-color mb6">下载列表</div>' . $option_html . '</div>';

    //多链接下载
    $type_html  = '';
    $type_html .= '<select class="form-control" name="posts_zibpay[pay_limit]">
        <option value="0"' . selected('0', $selected, false) . '>所有人可购买</option>
        <option value="1"' . selected('1', $selected, false) . '>黄金会员及以上会员可购买</option>
        <option value="2"' . selected('2', $selected, false) . '>仅钻石会员可购买</option>
    </select>';
    $type_html = '<div class="mb10"><div class="em09 muted-2-color mb6">购买权限</div>' . $type_html . '</div>';



    $html = '<div class="dependency-box vote-set ' . $class . '">';
    $html .= '<div class="flex ac jsb">
                    <div class="flex ac"><i class="fa fa-bar-chart mr6"></i>多链接下载模块</div>
                    <label style="margin: 0;"><input class="hide"' . ($is_vote ? ' checked="checked"' : '') . 'name="vote_s" type="checkbox"><div class="form-switch flex0"></div></label>
                </div>';

    $html .= '<div class="vote-set" data-controller="vote_s" data-condition="!=" data-value="" ' . (!$is_vote ? ' style="display: none;"' : '') . '>';
    // $html .= $title_html;
    $html .= $type_html;
    $html .= $option_html;
    // $html .= $time_limit_html;
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
